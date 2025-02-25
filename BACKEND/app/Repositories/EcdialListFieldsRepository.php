<?php

namespace App\Repositories;

use App\Models\EcdialListField;
use Illuminate\Http\Request;
use App\Http\Resources\EcdialListFieldResource;
use App\Http\Requests\EcdialListFieldRequest;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use Illuminate\Support\Facades\Log;
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
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = EcdialListField::query();

            // Search Filter
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('field_name', 'like', "%$searchTerm%")
                        ->orWhere('field_label', 'like', "%$searchTerm%")
                        ->orWhere('description', 'like', "%$searchTerm%");
                });
            }

            if (!empty($listId)) {
                $query->where('list_id', $listId);
            }

            if (!empty($fieldType)) {
                $query->where('field_type', $fieldType);
            }

            if ($page === 'all') {
                $fieldData = $query->get();
                return $this->apiResponse->getResponseStructure(trans('message.success'), $fieldData, trans('message.data_fetch'));
            } else {
                $fieldData = $query->paginate($perPage);
                return $this->apiResponse->getResponseStructure(trans('message.success'), $fieldData, trans('message.data_fetch'));
            }
        } catch (Exception $ex) {
            Log::error('Error fetching fields: ' . $ex->getMessage());
            return response()->json(['error' => trans('message.internal_error')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
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

            return response()->json([
                'message' => 'Field Created Successfully',
                'data' => new EcdialListFieldResource($field)
            ], ResponseHTTP::HTTP_CREATED);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('Error creating field: ' . $ex->getMessage());
            return response()->json(['error' => trans('message.internal_error')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
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

            return response()->json([
                'message' => 'Field Updated Successfully',
                'data' => new EcdialListFieldResource($field)
            ], ResponseHTTP::HTTP_OK);
        } catch (Exception $ex) {
            Log::error('Error updating field: ' . $ex->getMessage());
            return response()->json(['error' => trans('message.internal_error')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete($id)
    {
        try {
            $field = EcdialListField::findOrFail($id);
            $field->delete();

            return response()->json([
                'message' => 'Field Deleted Successfully',
            ], ResponseHTTP::HTTP_OK);
        } catch (Exception $ex) {
            Log::error('Error deleting field: ' . $ex->getMessage());
            return response()->json(['error' => 'Failed to delete the field. It may not exist.'], ResponseHTTP::HTTP_NOT_FOUND);
        }
    }
}
