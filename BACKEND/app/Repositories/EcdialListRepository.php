<?php

namespace App\Repositories;

use App\Models\EcdialLists;
use Illuminate\Http\Request;
use App\Http\Resources\EcdialListResource;
use Exception;
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
        try {
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
            $listData = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $listData, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating EcDial Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
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

            $data = new EcdialListResource($ecdialLists);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.list_create_success'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating EcDial Create: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
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

            $data = new EcdialListResource($ecdialLists);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.list_update_success'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating EcDial Update: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
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

            return $this->apiResponse->getResponseStructure(trans('message.success'), $list_id, trans('message.list_delete_success'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating EcDial Delete: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function createCustomTable($list_id)
    {
        $tableName = "custom_lead_" . $list_id;

        Schema::create($tableName, function (Blueprint $table) {
            $table->id(); // Primary Key (Auto Increment)
            $table->timestamps(); // Created & Updated timestamps
        });
    }

    /**
     * Drop the custom lead table when deleting a list
     */
    private function dropCustomTable($list_id)
    {
        $tableName = "custom_lead_" . $list_id;

        if (Schema::hasTable($tableName)) {
            Schema::dropIfExists($tableName);
        }
    }
}
