<?php

namespace App\Repositories;

use App\Models\EcdialListField;
use Illuminate\Http\Request;
use App\Http\Resources\EcdialListFieldResource;
use App\Http\Requests\EcdialListFieldRequest;
use Exception;
use App\Http\Response\CustomApiResponse;
use App\Models\EcdialListFieldDependency;
use App\Models\EcdialLists;
use App\Services\ApiCallerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class EcdialListFieldsRepository
{
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

            $query = EcdialListField::query();

            $authHeader = $request->header('Authorization');

            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $token = str_replace('Bearer ', '', $authHeader);
                $payload = extractPayload($token);

                if ($payload && isset($payload['campaign']) && ($payload['role_id'] == 3)) {
                    $campaignListId = EcdialLists::where('campaign_id', $payload['campaign'])
                        ->value('list_id');

                    if ($campaignListId) {
                        $listId = $campaignListId;
                    } else {
                        return $this->apiResponse->getResponseStructure(null, [], trans('message.list_not_found'));
                    }
                }
            }

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
            $resource = EcdialListFieldResource::collection($fieldData);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $resource, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating EcDial Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function create(EcdialListFieldRequest $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $dependencyMappings = $validatedData['dependency_mappings'] ?? [];
            unset($validatedData['dependency_mappings'], $validatedData['dependency_values']);

            if ($this->isFieldApiEnabled()) {
                $this->assertValidParentFieldNames($dependencyMappings, $this->getExistingFieldNames((int) $validatedData['list_id']));
            }

            $field = EcdialListField::create($validatedData);

            if ($this->isFieldApiEnabled()) {
                $this->syncDependencyMappings((int) $field->list_id, $field->field_name, $dependencyMappings);
            }
            DB::commit();

            $listId = $validatedData['list_id'];
            $fieldName = $validatedData['field_name'];

            // Sanitize and create column name dynamically
            $columnName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $fieldName));

            // Define table name
            $tableName = "custom_lead_{$listId}";

            if (!Schema::hasColumn($tableName, $columnName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                    $table->string($columnName)->nullable()->after('updated_at');
                });
            }

            if ($this->isFieldApiEnabled()) {
                $field->load(['apiConfig', 'dependencyMappings' => function ($q) use ($field) {
                    $q->where('ecdial_list_field_dependencies.list_id', $field->list_id);
                }]);
            }

            $data = new EcdialListFieldResource($field);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.list_field_create_success'));
        } catch (Exception $ex) {
            DB::rollBack();

            if ($ex instanceof \InvalidArgumentException) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    [],
                    $ex->getMessage()
                );
            }

            generateLogs('error', 'Error in generating EcDial: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    // Update field method
    public function update(EcdialListFieldRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $field = EcdialListField::findOrFail($id);

            // Validate data automatically
            $validatedData = $request->validated();
            $dependencyMappings = $validatedData['dependency_mappings'] ?? null;
            unset($validatedData['dependency_mappings'], $validatedData['dependency_values']);

            $oldListId = (int) $field->list_id;
            $oldFieldName = $field->field_name;

            $field->update($validatedData);

            if ($this->isFieldApiEnabled()) {
                if ($oldListId === (int) $field->list_id && $oldFieldName !== $field->field_name) {
                    $this->cascadeDependencyFieldRename((int) $field->list_id, $oldFieldName, $field->field_name);
                }

                if (!is_null($dependencyMappings)) {
                    $allowedFieldNames = $this->getExistingFieldNames((int) $field->list_id);
                    $this->assertValidParentFieldNames($dependencyMappings, $allowedFieldNames);
                    $this->syncDependencyMappings((int) $field->list_id, $field->field_name, $dependencyMappings);
                }
            }

            DB::commit();

            if ($this->isFieldApiEnabled()) {
                $field->load(['apiConfig', 'dependencyMappings' => function ($q) use ($field) {
                    $q->where('ecdial_list_field_dependencies.list_id', $field->list_id);
                }]);
            }

            $data = new EcdialListFieldResource($field);

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

            generateLogs('error', 'Error in Update EcDial: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $field = EcdialListField::findOrFail($id);
            $field->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $id, trans('message.list_field_delete_success'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Delete EcDial: ', $ex->getMessage());
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
            $field = EcdialListField::with(['apiConfig'])->findOrFail($id);
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
            $params   = [];

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

            $apiCallerService = app(ApiCallerService::class);
            $response = $apiCallerService->call($apiConfig, $override, $params);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $response, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetchOptions for EcDial field: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function syncDependencyMappings(int $listId, string $childFieldName, array $dependencyMappings): void
    {
        EcdialListFieldDependency::where('list_id', $listId)
            ->where('child_field_name', $childFieldName)
            ->delete();

        if (empty($dependencyMappings)) {
            return;
        }

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

    private function getExistingFieldNames(int $listId): array
    {
        return EcdialListField::where('list_id', $listId)
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
}
