<?php

namespace App\Repositories;

use App\Models\DynamicListField;
use Illuminate\Http\Request;
use App\Http\Resources\DynamicListFieldResource;
use App\Http\Requests\DynamicListFieldRequest;
use Exception;
use App\Http\Response\CustomApiResponse;
use App\Models\EcdialListFieldDependency;
use App\Services\ApiCallerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DynamicListFieldsRepository
{
    private const RESPONSE_TYPES = ['array_values', 'array_objects', 'single_value'];

    private const API_FIELD_KEYS = [
        'uses_api',
        'api_config_id',
        'dependency_mandatory',
        'response_type',
        'response_data_path',
        'response_label_key',
        'response_value_key',
    ];

    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    private function isFieldApiEnabled(): bool
    {
        return (bool) config('omni.FIELD_API_ENABLED');
    }

    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $listId = $request->input('list_id', '');
            $fieldType = $request->input('field_type', '');
            $status = $request->input('status', '');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = DynamicListField::query();

            // Search Filter
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('field_name', 'like', "%$searchTerm%")
                        ->orWhere('field_label', 'like', "%$searchTerm%")
                        ->orWhere('description', 'like', "%$searchTerm%")
                        ->orWhere('status', 'like', "%$searchTerm%");
                });
            }

            if (!empty($listId)) {
                $query->where('list_id', $listId);
            }

            if (!empty($fieldType)) {
                $query->where('field_type', $fieldType);
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            if ($this->isFieldApiEnabled()) {
                $eagerLoad = ['apiConfig'];
                if (!empty($listId)) {
                    $eagerLoad['dependencyMappings'] = function ($q) use ($listId) {
                        $q->where('ecdial_list_field_dependencies.list_id', $listId);
                    };
                }
                $query->with($eagerLoad);
            }

            $fieldData = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            $resource = DynamicListFieldResource::collection($fieldData);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $resource, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating EcDial Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function create(DynamicListFieldRequest $request)
    {
        DB::beginTransaction();
        try {
            $listId = $request->input('list_id');
            $tableName = "custom_lead_{$listId}";
            $allFields = $request->input('form_items', []);
            $incomingFieldNames = $this->extractIncomingFieldNames($allFields);
            $insertedFields = [];
            $columnsToAdd = [];

            // Optionally, delete all existing fields for this list
            DynamicListField::where('list_id', $listId)->delete();

            foreach ($allFields as $column => $fields) {
                foreach (array_values($fields) as $index => $field) {
                    $dependencyMappings = $field['dependency_mappings'] ?? [];

                    $field['list_id'] = $listId;
                    $field['field_column'] = $column;
                    $field['field_order'] = $index + 1;
                    $field = $this->normalizeFieldPayload($field, $dependencyMappings);
                    unset($field['dependency_mappings'], $field['dependency_values']);

                    if (array_key_exists('order', $field)) {
                        unset($field['order']);
                    }

                    if (isset($field['id']) && preg_match('/^new-\w+$/', $field['id'])) {
                        unset($field['id']);
                    }

                    // Create the new field
                    $fieldModel = DynamicListField::create($field);
                    if ($this->isFieldApiEnabled()) {
                        $this->syncDependencyMappings((int) $listId, $fieldModel->field_name, $dependencyMappings, $incomingFieldNames);
                    }
                    $insertedFields[] = $fieldModel;

                    // Track new schema column name
                    $columnName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $field['field_name']));
                    $columnsToAdd[$columnName] = true;
                }
            }

            DB::commit();

            // Add new schema columns to the corresponding custom_lead table
            foreach (array_keys($columnsToAdd) as $columnName) {
                if (!Schema::hasColumn($tableName, $columnName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                        $table->string($columnName)->nullable()->after('updated_at');
                    });
                }
            }

            if ($this->isFieldApiEnabled()) {
                foreach ($insertedFields as $f) {
                    $f->load(['apiConfig', 'dependencyMappings' => function ($q) use ($f) {
                        $q->where('ecdial_list_field_dependencies.list_id', $f->list_id);
                    }]);
                }
            }

            $resource = DynamicListFieldResource::collection($insertedFields);
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $resource,
                trans('message.list_field_create_success')
            );
        } catch (\Exception $ex) {
            DB::rollBack();

            if ($ex instanceof \InvalidArgumentException) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    [],
                    $ex->getMessage()
                );
            }

            // Handle unique constraint violation directly
            if (
                $ex instanceof \Illuminate\Database\QueryException &&
                strpos($ex->getMessage(), 'ecdial_lists_fields_list_id_field_name_unique') !== false
            ) {
                return response()->json([
                    'message' => 'Field Name Already Exists',
                ], 400);
            }
            generateLogs('error', 'Error in generating EcDial: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update($request, $id)
    {
        $validationRules = [
            'list_id' => ['required', Rule::exists('ecdial_lists', 'list_id')],
            'field_name' => ['required', 'string', 'max:255'],
            'field_label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', Rule::in(['text', 'select', 'multi', 'checkbox', 'radio', 'textarea', 'date', 'number'])],
            'description' => ['nullable', 'string', 'max:255'],
            'options' => ['nullable', 'string', 'max:255'],
            'mandatory' => ['required', Rule::in(['Y', 'N'])],
            'calling_authority' => ['required', Rule::in(['Y', 'N'])],
            'show_authority' => ['required', Rule::in(['Y', 'N'])],
            'agent_view' => ['required', Rule::in(['Y', 'N'])],
            'field_order' => ['required', 'integer'],
            'field_help' => ['nullable', 'string'],
            'field_size' => ['nullable', 'integer'],
            'field_max_length' => ['nullable', 'integer'],
            'search_filter' => ['required', Rule::in(['Y', 'N'])],
            'editable' => ['required', Rule::in(['Y', 'N'])],
            'default_value' => ['nullable', 'string', 'max:255'],
            'value_masked' => ['required', Rule::in(['Y', 'N'])],
            'duplicate_check' => ['required', Rule::in(['Y', 'N'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'field_column' => ['nullable', 'string', 'max:1'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'send_to_crm' => ['nullable', Rule::in(['Y', 'N'])],
            'allow_field_deletion' => ['nullable', Rule::in(['Y', 'N'])],
            'allow_data_delete' => ['nullable', Rule::in(['Y', 'N'])],
        ];

        if ($this->isFieldApiEnabled()) {
            $validationRules += [
                'uses_api' => ['required', Rule::in(['Y', 'N'])],
                'api_config_id' => ['required_if:uses_api,Y', 'nullable', Rule::exists('api_configs', 'id')],
                'dependent_on_other' => ['nullable', Rule::in(['Y', 'N'])],
                'dependency_mandatory' => ['nullable', Rule::in(['Y', 'N'])],
                'response_type' => ['required_if:uses_api,Y', 'nullable', Rule::in(self::RESPONSE_TYPES)],
                'response_data_path' => ['nullable', 'regex:/^[a-zA-Z0-9_.]*$/'],
                'response_label_key' => ['required_if:response_type,array_objects', 'nullable', 'string', 'max:255'],
                'response_value_key' => ['required_if:response_type,array_objects', 'nullable', 'string', 'max:255'],
                'dependency_mappings' => ['sometimes', 'array', 'required_if:dependent_on_other,Y'],
                'dependency_mappings.*.parent_field_name' => ['nullable', 'string', 'max:255'],
                'dependency_mappings.*.param_name' => ['required_with:dependency_mappings', 'string', 'max:100'],
                'dependency_mappings.*.param_type' => ['required_with:dependency_mappings', Rule::in(['query', 'param', 'body', 'header', 'path'])],
                'dependency_mappings.*.sort_order' => ['nullable', 'integer', 'min:0'],
            ];
        }

        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                generateLogs('error', 'Validation failed: ' . json_encode($validator->errors()->all()));
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    [],
                    $validator->errors()->first()
                );
            }

            $validatedData = $validator->validated();
            $dependencyMappings = $validatedData['dependency_mappings'] ?? null;
            $validatedData = $this->normalizeFieldPayload($validatedData, $dependencyMappings ?? []);
            unset($validatedData['dependency_mappings'], $validatedData['dependency_values']);

            $field = DynamicListField::findOrFail($id);
            $oldListId = (int) $field->list_id;
            $oldFieldName = $field->field_name;

            $field->update($validatedData);

            if ($this->isFieldApiEnabled()) {
                if ($oldListId === (int) $field->list_id && $oldFieldName !== $field->field_name) {
                    $this->cascadeDependencyFieldRename((int) $field->list_id, $oldFieldName, $field->field_name);
                }

                if (!is_null($dependencyMappings)) {
                    $allowedFieldNames = $this->getExistingFieldNames((int) $field->list_id);
                    $this->syncDependencyMappings((int) $field->list_id, $field->field_name, $dependencyMappings, $allowedFieldNames);
                }
            }

            DB::commit();

            if ($this->isFieldApiEnabled()) {
                $field->load(['apiConfig', 'dependencyMappings' => function ($q) use ($field) {
                    $q->where('ecdial_list_field_dependencies.list_id', $field->list_id);
                }]);
            }

            $data = new DynamicListFieldResource($field);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.list_field_update_success'));
        } catch (Exception $ex) {
            DB::rollBack();

            if ($ex instanceof \InvalidArgumentException) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    [],
                    $ex->getMessage()
                );
            }

            generateLogs('error', 'Error in Update DynamicListField: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    /**
     * Trigger the API configured for a field and return its response.
     *
     * For multi-parameter dependency fields, pass dependency_values as a key-value map.
     * Example: dependency_values[user]=101&dependency_values[date]=2026-05-18
     */
    public function fetchOptions(int $id, Request $request)
    {
        if (!$this->isFieldApiEnabled()) {
            return $this->apiResponse->getResponseStructure(null, [], 'Field API feature is not enabled.');
        }

        try {
            $field = DynamicListField::with(['apiConfig'])->findOrFail($id);
            $field->load(['dependencyMappings' => function ($q) use ($field) {
                $q->where('ecdial_list_field_dependencies.list_id', $field->list_id);
            }]);

            if ($field->uses_api !== 'Y' || empty($field->api_config_id)) {
                return $this->apiResponse->getResponseStructure(null, [], trans('message.field_not_api_driven'));
            }

            $apiConfig = $field->apiConfig;

            if (!$apiConfig || !$apiConfig->status) {
                return $this->apiResponse->getResponseStructure(null, [], trans('message.api_config_not_found'));
            }

            $override = [];
            $params = [];

            $dependencyValues = $request->input('dependency_values', []);

            if (!is_array($dependencyValues)) {
                $dependencyValues = [];
            }

            foreach ($field->dependencyMappings as $mapping) {
                if (!array_key_exists($mapping->param_name, $dependencyValues)) {
                    continue;
                }

                $this->injectDependencyValue($override, $params, $mapping->param_type, $mapping->param_name, $dependencyValues[$mapping->param_name]);
            }

            if ($this->isDependencyMandatory($field)) {
                $missingParams = $this->getMissingDependencyParams($field->dependencyMappings->all(), $dependencyValues);

                if (!empty($missingParams)) {
                    return $this->apiResponse->getResponseStructure(
                        trans('message.error'),
                        ['missing_dependency_params' => $missingParams],
                        'Missing mandatory dependency values: ' . implode(', ', $missingParams)
                    );
                }
            }

            $apiCallerService = app(ApiCallerService::class);
            $response = $apiCallerService->call($apiConfig, $override, $params);

            $mappedOptions = $this->buildMappedOptions($field, $response['body'] ?? []);
            $response['mapped_options'] = $mappedOptions;

            return $this->apiResponse->getResponseStructure(trans('message.success'), $response, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetchOptions for DynamicList field: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $field = DynamicListField::findOrFail($id);
            $columnName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $field->field_name));
            $tableName = 'custom_lead_' . $field->list_id;

            if ($field->allow_field_deletion == 'N') {
                return $this->apiResponse->getResponseStructure(
                    false,
                    [],
                    trans('message.field_deletion_not_allowed')
                );
            }

            if ($field->allow_data_delete == 'Y') {
                if (Schema::hasColumn($tableName, $columnName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                        $table->dropColumn($columnName);
                    });
                }
            } else {
                if (Schema::hasColumn($tableName, $columnName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                        $table->renameColumn($columnName, $columnName . '_deleted');
                    });
                }
            }

            $listId = $field->list_id;
            $fieldColumn = $field->field_column;
            $fieldOrder = $field->field_order;

            $field->delete();

            DynamicListField::where('list_id', $listId)
                ->where('field_column', $fieldColumn)
                ->where('field_order', '>', $fieldOrder)
                ->decrement('field_order');

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $id,
                trans('message.list_field_delete_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Delete EcDial: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function syncDependencyMappings(int $listId, string $childFieldName, array $dependencyMappings, array $allowedFieldNames = []): void
    {
        EcdialListFieldDependency::where('list_id', $listId)
            ->where('child_field_name', $childFieldName)
            ->delete();

        if (empty($dependencyMappings)) {
            return;
        }

        $this->assertValidParentFieldNames($dependencyMappings, $allowedFieldNames);

        foreach ($dependencyMappings as $index => $mapping) {
            if (empty($mapping['param_name']) || empty($mapping['param_type'])) {
                continue;
            }

            EcdialListFieldDependency::create([
                'list_id' => $listId,
                'child_field_name' => $childFieldName,
                'parent_field_name' => $mapping['parent_field_name'] ?? null,
                'param_name' => $mapping['param_name'],
                'param_type' => $mapping['param_type'],
                'sort_order' => $mapping['sort_order'] ?? $index,
            ]);
        }
    }

    private function extractIncomingFieldNames(array $allFields): array
    {
        $fieldNames = [];

        foreach ($allFields as $fields) {
            foreach ((array) $fields as $field) {
                if (!empty($field['field_name'])) {
                    $fieldNames[] = $field['field_name'];
                }
            }
        }

        return array_values(array_unique($fieldNames));
    }

    private function getExistingFieldNames(int $listId): array
    {
        return DynamicListField::where('list_id', $listId)
            ->pluck('field_name')
            ->all();
    }

    private function assertValidParentFieldNames(array $dependencyMappings, array $allowedFieldNames): void
    {
        if (empty($allowedFieldNames)) {
            return;
        }

        $allowedLookup = array_flip($allowedFieldNames);

        foreach ($dependencyMappings as $mapping) {
            $parentFieldName = $mapping['parent_field_name'] ?? null;

            if (empty($parentFieldName)) {
                continue;
            }

            if (!isset($allowedLookup[$parentFieldName])) {
                throw new \InvalidArgumentException("Invalid parent_field_name: {$parentFieldName}");
            }
        }
    }

    private function cascadeDependencyFieldRename(int $listId, string $oldFieldName, string $newFieldName): void
    {
        EcdialListFieldDependency::where('list_id', $listId)
            ->where('child_field_name', $oldFieldName)
            ->update(['child_field_name' => $newFieldName]);

        EcdialListFieldDependency::where('list_id', $listId)
            ->where('parent_field_name', $oldFieldName)
            ->update(['parent_field_name' => $newFieldName]);
    }

    private function injectDependencyValue(array &$override, array &$params, ?string $paramType, string $paramName, $value): void
    {
        if (is_null($value)) {
            return;
        }

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

    private function normalizeFieldPayload(array $field, array $dependencyMappings): array
    {
        if (!$this->isFieldApiEnabled()) {
            // Strip all API-related keys to prevent writing to non-existent columns
            foreach (self::API_FIELD_KEYS as $key) {
                unset($field[$key]);
            }
            unset($field['dependent_on_other']);
            return $field;
        }

        if (array_key_exists('dependency_mandatory', $field)) {
            $field['dependency_mandatory'] = $this->normalizeYesNoBoolean($field['dependency_mandatory']);
        }

        $usesApi = ($field['uses_api'] ?? 'N') === 'Y';

        if (!$usesApi) {
            $field['api_config_id'] = null;
            $field['response_type'] = null;
            $field['response_data_path'] = null;
            $field['response_label_key'] = null;
            $field['response_value_key'] = null;
            $field['dependency_mandatory'] = $field['dependency_mandatory'] ?? 'N';
            return $field;
        }

        $field['response_type'] = $field['response_type'] ?? 'array_values';
        $field['response_data_path'] = $field['response_data_path'] ?? null;

        if (($field['response_type'] ?? null) !== 'array_objects') {
            $field['response_label_key'] = null;
            $field['response_value_key'] = null;
        }

        if (!array_key_exists('dependency_mandatory', $field)) {
            $field['dependency_mandatory'] = !empty($dependencyMappings) ? 'Y' : 'N';
        }

        return $field;
    }

    private function normalizeYesNoBoolean($value): string
    {
        if (is_string($value)) {
            $upper = strtoupper($value);
            if (in_array($upper, ['Y', 'N'], true)) {
                return $upper;
            }
        }

        return filter_var($value, FILTER_VALIDATE_BOOL) ? 'Y' : 'N';
    }

    private function isDependencyMandatory(DynamicListField $field): bool
    {
        return ($field->dependency_mandatory ?? 'N') === 'Y';
    }

    private function getMissingDependencyParams(array $dependencyMappings, array $dependencyValues): array
    {
        $missing = [];

        foreach ($dependencyMappings as $mapping) {
            $paramName = $mapping->param_name;

            if (!array_key_exists($paramName, $dependencyValues)) {
                $missing[] = $paramName;
                continue;
            }

            $value = $dependencyValues[$paramName];
            if (is_null($value) || (is_string($value) && trim($value) === '')) {
                $missing[] = $paramName;
            }
        }

        return array_values(array_unique($missing));
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
            'single_value' => $this->mapSingleValueOption($source),
            default => $this->mapArrayValueOptions($source),
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
                if (is_array($item) || is_object($item)) {
                    $value = json_encode($item);
                } else {
                    $value = $item;
                }

                $options[] = [
                    'label' => (string) $value,
                    'value' => $value,
                ];
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
            if (array_is_list($source)) {
                $first = $source[0] ?? null;
            } else {
                $first = reset($source);
            }
        } else {
            $first = $source;
        }

        if (is_null($first)) {
            return [];
        }

        $normalized = is_scalar($first) ? $first : json_encode($first);

        return [[
            'label' => (string) $normalized,
            'value' => $normalized,
        ]];
    }
}
