<?php

namespace App\Services;

use App\Models\DynamicListField;
use App\Models\EcdialListField;
use App\Models\EcdialListFieldDependency;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use stdClass;
use Throwable;

class FieldBulkOptionsService
{
    public function __construct(protected ApiCallerService $apiCallerService) {}

    /**
     * Fetch options for all independent dynamic list fields belonging to a list.
     * Returns an associative array keyed by field_name.
     */
    public function fetchForDynamic(int $listId, ?int $leadId = null): array
    {
        $fields = DynamicListField::with(['apiConfig', 'dependencyMappings' => function ($q) use ($listId) {
            $q->where('ecdial_list_field_dependencies.list_id', $listId);
        }])
            ->where('list_id', $listId)
            ->where('uses_api', 'Y')
            ->whereNotNull('api_config_id')
            ->where('status', 'active')
            ->get();

        return $this->fetchForFields($fields, $listId, $leadId, true);
    }

    /**
     * Fetch options for all independent ecdial list fields belonging to a list.
     * Returns an associative array keyed by field_name.
     */
    public function fetchForEcdial(int $listId, ?int $leadId = null): array
    {
        $fields = EcdialListField::with(['apiConfig', 'dependencyMappings' => function ($q) use ($listId) {
            $q->where('ecdial_list_field_dependencies.list_id', $listId);
        }])
            ->where('list_id', $listId)
            ->where('uses_api', 'Y')
            ->whereNotNull('api_config_id')
            ->where('status', 'active')
            ->get();

        return $this->fetchForFields($fields, $listId, $leadId, false);
    }

    private function fetchForFields($fields, int $listId, ?int $leadId, bool $mapDynamicOptions): array
    {
        $result = [];
        $leadRow = null;
        $fieldColumnByName = [];

        if (!is_null($leadId)) {
            $leadRow = $this->loadLeadRow($listId, $leadId);
            $fieldColumnByName = EcdialListField::query()
                ->where('list_id', $listId)
                ->pluck('field_column', 'field_name')
                ->toArray();
        }

        foreach ($fields as $field) {
            $hasDependencies = $field->dependencyMappings->isNotEmpty();

            if ($hasDependencies && is_null($leadRow)) {
                continue;
            }

            $override = [];
            $params = [];

            if ($hasDependencies) {
                $missingParams = $this->prepareDependencyOverrides(
                    $field->dependencyMappings,
                    $leadRow,
                    $fieldColumnByName,
                    $override,
                    $params
                );

                if (!empty($missingParams)) {
                    $result[$field->field_name] = [
                        'error' => 'Missing parent values for dependencies: ' . implode(', ', $missingParams),
                        'missing_dependency_params' => $missingParams,
                    ];
                    continue;
                }
            }

            $apiConfig = $field->apiConfig;

            if (!$apiConfig || !$apiConfig->status) {
                $result[$field->field_name] = ['error' => 'API config not found or inactive.'];
                continue;
            }

            try {
                $response = $this->apiCallerService->call($apiConfig, $override, $params);

                if ($mapDynamicOptions) {
                    $mappedOptions = $this->buildMappedOptions($field, $response['body'] ?? []);
                    $result[$field->field_name] = ['mapped_options' => $mappedOptions];
                    continue;
                }

                $result[$field->field_name] = ['response' => $response['body'] ?? null];
            } catch (Exception $e) {
                $result[$field->field_name] = ['error' => $e->getMessage()];
            }
        }

        return $result;
    }

    private function loadLeadRow(int $listId, int $leadId): stdClass
    {
        $tableName = 'custom_lead_' . $listId;

        try {
            $leadRow = DB::table($tableName)->where('id', $leadId)->first();
        } catch (Throwable $th) {
            throw new InvalidArgumentException('Lead table not found for the given list.');
        }

        if (!$leadRow) {
            throw new InvalidArgumentException('Lead not found for the given list.');
        }

        return $leadRow;
    }

    private function prepareDependencyOverrides(
        $dependencyMappings,
        stdClass $leadRow,
        array $fieldColumnByName,
        array &$override,
        array &$params
    ): array {
        $missingParams = [];

        foreach ($dependencyMappings as $mapping) {
            $parentFieldName = $mapping->parent_field_name;
            $columnName = $fieldColumnByName[$parentFieldName] ?? $parentFieldName;
            $value = $this->readLeadValue($leadRow, $columnName, $parentFieldName);

            if (is_null($value) || (is_string($value) && trim($value) === '')) {
                $missingParams[] = $mapping->param_name;
                continue;
            }

            $this->injectDependencyValue($override, $params, $mapping->param_type, $mapping->param_name, $value);
        }

        return array_values(array_unique($missingParams));
    }

    private function readLeadValue(stdClass $leadRow, string $columnName, string $fallbackName)
    {
        $row = (array) $leadRow;

        if (array_key_exists($columnName, $row)) {
            return $row[$columnName];
        }

        if (array_key_exists($fallbackName, $row)) {
            return $row[$fallbackName];
        }

        return null;
    }

    private function injectDependencyValue(array &$override, array &$params, ?string $paramType, string $paramName, $value): void
    {
        switch ($paramType) {
            case 'query':
            case 'param':
                $override['query'][$paramName] = $value;
                break;

            case 'body':
                $override['body'][$paramName] = $value;
                break;

            case 'header':
                $override['headers'][$paramName] = $value;
                break;

            case 'path':
                $params[$paramName] = $value;
                break;
        }
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
