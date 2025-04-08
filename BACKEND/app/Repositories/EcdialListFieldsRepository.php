<?php

namespace App\Repositories;

use App\Models\EcdialListField;
use Illuminate\Http\Request;
use App\Http\Resources\EcdialListFieldResource;
use App\Http\Requests\EcdialListFieldRequest;
use Exception;
use App\Http\Response\CustomApiResponse;
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

    public function create(EcdialListFieldRequest $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $field = EcdialListField::create($validatedData);
            DB::commit();

            $listId = $validatedData['list_id'];
            $fieldLabel = $validatedData['field_label'];

            // Sanitize and create column name dynamically
            $columnName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $fieldLabel));

            // Define table name
            $tableName = "custom_lead_{$listId}";

            if (!Schema::hasColumn($tableName, $columnName)) {
                Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                    $table->string($columnName)->nullable()->after('updated_at');
                });
            }

            $data = new EcdialListFieldResource($field);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.list_field_create_success'));
        } catch (Exception $ex) {
            DB::rollBack();
            generateLogs('error', 'Error in generating EcDial: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    // Update field method
    public function update(EcdialListFieldRequest $request, $id)
    {
        try {
            $field = EcdialListField::findOrFail($id);

            // Validate data automatically
            $validatedData = $request->validated();

            $field->update($validatedData);

            $data = new EcdialListFieldResource($field);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.list_field_update_success'));
        } catch (Exception $ex) {
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
}
