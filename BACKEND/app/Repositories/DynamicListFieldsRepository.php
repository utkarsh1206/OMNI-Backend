<?php

namespace App\Repositories;

use App\Models\DynamicListField;
use Illuminate\Http\Request;
use App\Http\Resources\DynamicListFieldResource;
use App\Http\Requests\DynamicListFieldRequest;
use Exception;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DynamicListFieldsRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
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
            $fieldData = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $fieldData, trans('message.data_fetch'));
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
            $insertedFields = [];
            $columnsToAdd = [];

            // Optionally, delete all existing fields for this list
            DynamicListField::where('list_id', $listId)->delete();

            foreach ($allFields as $column => $fields) {
                foreach (array_values($fields) as $index => $field) {
                    $field['list_id'] = $listId;
                    $field['field_column'] = $column;
                    $field['field_order'] = $index + 1;

                    if (array_key_exists('order', $field)) {
                        unset($field['order']);
                    }

                    if (isset($field['id']) && preg_match('/^new-\w+$/', $field['id'])) {
                        unset($field['id']);
                    }

                    // Create the new field
                    $fieldModel = DynamicListField::create($field);
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

            $resource = DynamicListFieldResource::collection($insertedFields);
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $resource,
                trans('message.list_field_create_success')
            );
        } catch (\Exception $ex) {
            DB::rollBack();
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
            'field_type' => ['required', Rule::in(['text', 'select', 'multi', 'checkbox', 'radio', 'textarea', 'date'])],
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
        ];

        try {
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

            $field = DynamicListField::findOrFail($id);

            $field->update($validatedData);

            $data = new DynamicListFieldResource($field);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.list_field_update_success'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Update DynamicListField: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $field = DynamicListField::findOrFail($id);
            $field->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $id, trans('message.list_field_delete_success'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Delete EcDial: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
