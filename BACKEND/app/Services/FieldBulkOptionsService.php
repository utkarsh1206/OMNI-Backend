<?php

namespace App\Services;

use App\Models\DynamicListField;
use App\Models\EcdialListField;
use Exception;

class FieldBulkOptionsService
{
    public function __construct(protected ApiCallerService $apiCallerService) {}

    /**
     * Fetch options for all independent dynamic list fields belonging to a list.
     * Returns an associative array keyed by field_name.
     */
    public function fetchForDynamic(int $listId): array
    {
        $fields = DynamicListField::with(['apiConfig', 'dependencyMappings' => function ($q) use ($listId) {
            $q->where('ecdial_list_field_dependencies.list_id', $listId);
        }])
            ->where('list_id', $listId)
            ->where('uses_api', 'Y')
            ->whereNotNull('api_config_id')
            ->where('status', 'active')
            ->get();

        $result = [];

        foreach ($fields as $field) {
            if ($field->dependencyMappings->isNotEmpty()) {
                continue;
            }

            $apiConfig = $field->apiConfig;

            if (!$apiConfig || !$apiConfig->status) {
                $result[$field->field_name] = ['error' => 'API config not found or inactive.'];
                continue;
            }

            try {
                $response = $this->apiCallerService->call($apiConfig);
                $mappedOptions = $this->buildMappedOptions($field, $response['body'] ?? []);
                $result[$field->field_name] = ['mapped_options' => $mappedOptions];
            } catch (Exception $e) {
                $result[$field->field_name] = ['error' => $e->getMessage()];
            }
        }

        return $result;
    }

    /**
     * Fetch options for all independent ecdial list fields belonging to a list.
     * Returns an associative array keyed by field_name.
     */
    public function fetchForEcdial(int $listId): array
    {
        $fields = EcdialListField::with(['apiConfig', 'dependencyMappings' => function ($q) use ($listId) {
            $q->where('ecdial_list_field_dependencies.list_id', $listId);
        }])
            ->where('list_id', $listId)
            ->where('uses_api', 'Y')
            ->whereNotNull('api_config_id')
            ->where('status', 'active')
            ->get();

        $result = [];

        foreach ($fields as $field) {
            if ($field->dependencyMappings->isNotEmpty()) {
                continue;
            }

            $apiConfig = $field->apiConfig;

            if (!$apiConfig || !$apiConfig->status) {
                $result[$field->field_name] = ['error' => 'API config not found or inactive.'];
                continue;
            }

            try {
                $response = $this->apiCallerService->call($apiConfig);
                $result[$field->field_name] = ['response' => $response['body'] ?? null];
            } catch (Exception $e) {
                $result[$field->field_name] = ['error' => $e->getMessage()];
            }
        }

        return $result;
    }

    private function buildMappedOptions(DynamicListField $field, $responseBody): array
    {
        $responseType = $field->response_type ?: 'array_values';
        $responseDataPath = $field->response_data_path ?: null;

        $source = is_null($responseDataPath)
            ? $responseBody
            : data_get($responseBody, $responseDataPath, []);

        return match ($responseType) {
            'array_objects' => $this->mapArrayObjectsOptions($source, (string) $field->response_label_key, (string) $field->response_value_key),
            'single_value'  => $this->mapSingleValueOption($source),
            default         => $this->mapArrayValueOptions($source),
        };
    }

    private function mapArrayValueOptions($source): array
    {
        if (!is_array($source)) {
            return [];
        }

        $options = [];

        if (array_is_list($source)) {
            foreach ($source as $item) {
                $value = (is_array($item) || is_object($item)) ? json_encode($item) : $item;
                $options[] = ['label' => (string) $value, 'value' => $value];
            }

            return $options;
        }

        foreach ($source as $key => $value) {
            $options[] = [
                'label' => is_scalar($value) ? (string) $value : json_encode($value),
                'value' => is_scalar($key) ? $key : json_encode($key),
            ];
        }

        return $options;
    }

    private function mapArrayObjectsOptions($source, string $labelKey, string $valueKey): array
    {
        if (!is_array($source)) {
            return [];
        }

        $options = [];

        foreach ($source as $item) {
            if (!is_array($item)) {
                continue;
            }

            $label = data_get($item, $labelKey);
            $value = data_get($item, $valueKey);

            if (is_null($label) || is_null($value)) {
                continue;
            }

            $options[] = [
                'label' => is_scalar($label) ? (string) $label : json_encode($label),
                'value' => $value,
            ];
        }

        return $options;
    }

    private function mapSingleValueOption($source): array
    {
        if (is_array($source)) {
            $first = array_is_list($source) ? ($source[0] ?? null) : reset($source);
        } else {
            $first = $source;
        }

        if (is_null($first)) {
            return [];
        }

        $normalized = is_scalar($first) ? $first : json_encode($first);

        return [['label' => (string) $normalized, 'value' => $normalized]];
    }
}
