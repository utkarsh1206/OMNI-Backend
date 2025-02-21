<?php

namespace App\Repositories;

use App\Models\EcdialLists;
use Illuminate\Http\Request;
use App\Http\Resources\EcdialListResource;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class EcdialListRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $searchTerm = $request->input('search', '');
        $status = $request->input('status', '');
        $name = $request->input('name');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = EcdialLists::query();

        // Search Filter
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%$searchTerm%")
                    ->orWhere('description', 'like', "%$searchTerm%")
                    ->orWhere('status', 'like', "%$searchTerm%");
            });
        }

        // Filter by status
        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($name)) {
            $query->where('name', $name);
        }

        if ($page === 'all') {
            $listData = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $listData, trans('message.data_fetch'));
        } else {
            $listData = $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $listData, trans('message.data_fetch'));
        }
    }

    public function create(Request $request)
    {
        try {
            $ecdialLists = EcdialLists::create([
                'name' => $request->name,
                "description" => $request->description,
                "status" => $request->status
            ]);
            // Generate custom table name
            $this->createCustomTable($ecdialLists->list_id);

            return response()->json([
                'message' => 'List Created Successfully',
                'data' => new EcdialListResource($ecdialLists)
            ]);
        } catch (Exception $ex) {
            Log::error('Error during authentication: ' . $ex->getMessage());
            return response()->json(['error' => trans('message.invalid_credentials')],  ResponseHTTP::HTTP_UNAUTHORIZED);
        }
    }

    public function update(Request $request, $list_id)
    {
        try {
            $ecdialLists = EcdialLists::findOrFail($list_id);

            $ecdialLists->update([
                'name' => $request->name,
                "description" => $request->description,
                "status" => $request->status
            ]);
            return response()->json([
                'message' => 'List Updated Successfully',
                'data' => new EcdialListResource($ecdialLists)
            ]);
        } catch (Exception $ex) {
            Log::error('Error during authentication: ' . $ex->getMessage());
            return response()->json(['error' => trans('message.invalid_credentials')],  ResponseHTTP::HTTP_UNAUTHORIZED);
        }
    }

    public function delete($list_id)
    {
        try {
            $ecdialList = EcdialLists::where('list_id', $list_id)->firstOrFail(); // Find by list_id

            // Drop the associated custom table
            $this->dropCustomTable($list_id);

            // Delete the list
            $ecdialList->delete();

            return response()->json([
                'message' => 'List Deleted Successfully',
            ], ResponseHTTP::HTTP_OK);
        } catch (Exception $ex) {
            Log::error('Error during deletion: ' . $ex->getMessage());
            return response()->json([
                'error' => 'Failed to delete the list. It may not exist.',
            ], ResponseHTTP::HTTP_NOT_FOUND);
        }
    }

    private function createCustomTable($list_id)
    {
        $tableName = "custom_lead_" . $list_id;

        Schema::create($tableName, function (Blueprint $table) {
            $table->id(); // Primary Key (Auto Increment)
            $table->timestamps(); // Created & Updated timestamps
        });

        Log::info("Custom table '$tableName' created successfully.");
    }

    /**
     * Drop the custom lead table when deleting a list
     */
    private function dropCustomTable($list_id)
    {
        $tableName = "custom_lead_" . $list_id;

        if (Schema::hasTable($tableName)) {
            Schema::dropIfExists($tableName);
            Log::info("Custom table '$tableName' deleted successfully.");
        }
    }
}
