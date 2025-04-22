<?php

namespace App\Repositories;

use App\Models\EcdialLists;
use Illuminate\Http\Request;
use App\Http\Resources\EcdialListResource;
use Exception;
use App\Http\Response\CustomApiResponse;
use App\Models\EcdialListField;
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
                "status" => $request->status,
                "campaign_id" => $request->campaign_id
            ]);
            // Generate custom table name
            $this->createCustomTable($ecdialLists->list_id);

            $baseFields = [
                [
                    'field_name' => 'First Name',
                    'field_label' => 'First Name',
                    'field_type' => 'text',
                    'description' => 'User\'s first name',
                    'options' => null,
                    'mandatory' => 'Y',
                    'calling_authority' => 'Y',
                    'show_authority' => 'Y',
                    'agent_view' => 'Y',
                    'field_order' => 1,
                    'field_help' => 'Enter the first name',
                    'field_size' => 12,
                    'field_max_length' => 50,
                    'search_filter' => 'Y',
                    'editable' => 'Y',
                    'default_value' => null,
                    'value_masked' => 'N',
                    'duplicate_check' => 'N',
                    'status' => 1,
                    'field_column' => 'A',
                ],
                [
                    'field_name' => 'Last Name',
                    'field_label' => 'Last Name',
                    'field_type' => 'text',
                    'description' => 'User\'s last name',
                    'options' => null,
                    'mandatory' => 'Y',
                    'calling_authority' => 'Y',
                    'show_authority' => 'Y',
                    'agent_view' => 'Y',
                    'field_order' => 1,
                    'field_help' => 'Enter the last name',
                    'field_size' => 12,
                    'field_max_length' => 50,
                    'search_filter' => 'Y',
                    'editable' => 'Y',
                    'default_value' => null,
                    'value_masked' => 'N',
                    'duplicate_check' => 'N',
                    'status' => 1,
                    'field_column' => 'B',
                ],
                [
                    'field_name' => 'Email',
                    'field_label' => 'Email',
                    'field_type' => 'text',
                    'description' => 'User\'s email address',
                    'options' => null,
                    'mandatory' => 'Y',
                    'calling_authority' => 'Y',
                    'show_authority' => 'Y',
                    'agent_view' => 'Y',
                    'field_order' => 1,
                    'field_help' => 'Enter the email',
                    'field_size' => 12,
                    'field_max_length' => 100,
                    'search_filter' => 'Y',
                    'editable' => 'Y',
                    'default_value' => null,
                    'value_masked' => 'N',
                    'duplicate_check' => 'Y',
                    'status' => 1,
                    'field_column' => 'C',
                ],
                [
                    'field_name' => 'Mobile',
                    'field_label' => 'Mobile Number',
                    'field_type' => 'text',
                    'description' => 'User\'s mobile number',
                    'options' => null,
                    'mandatory' => 'Y',
                    'calling_authority' => 'Y',
                    'show_authority' => 'Y',
                    'agent_view' => 'Y',
                    'field_order' => 1,
                    'field_help' => 'Enter the 10-digit mobile number',
                    'field_size' => 10,
                    'field_max_length' => 10,
                    'search_filter' => 'Y',
                    'editable' => 'Y',
                    'default_value' => null,
                    'value_masked' => 'N',
                    'duplicate_check' => 'Y',
                    'status' => 1,
                    'field_column' => 'D',
                ],
                [
                    'field_name' => 'Subject',
                    'field_label' => 'Subject',
                    'field_type' => 'text',
                    'description' => 'Subject of the message',
                    'options' => null,
                    'mandatory' => 'N',
                    'calling_authority' => 'N',
                    'show_authority' => 'Y',
                    'agent_view' => 'Y',
                    'field_order' => 2,
                    'field_help' => 'Enter the subject',
                    'field_size' => 12,
                    'field_max_length' => 150,
                    'search_filter' => 'N',
                    'editable' => 'Y',
                    'default_value' => null,
                    'value_masked' => 'N',
                    'duplicate_check' => 'N',
                    'status' => 1,
                    'field_column' => 'A',
                ],
                [
                    'field_name' => 'Description',
                    'field_label' => 'Description',
                    'field_type' => 'textarea',
                    'description' => 'Additional message or notes',
                    'options' => null,
                    'mandatory' => 'N',
                    'calling_authority' => 'N',
                    'show_authority' => 'Y',
                    'agent_view' => 'Y',
                    'field_order' => 2,
                    'field_help' => 'Enter additional details',
                    'field_size' => 12,
                    'field_max_length' => 500,
                    'search_filter' => 'N',
                    'editable' => 'Y',
                    'default_value' => null,
                    'value_masked' => 'N',
                    'duplicate_check' => 'N',
                    'status' => 1,
                    'field_column' => 'B',
                ],
            ];

            // Add list_id to each field in a clean way
            $fields = array_map(function ($field) use ($ecdialLists) {
                return ['list_id' => $ecdialLists->list_id] + $field;
            }, $baseFields);

            EcdialListField::insert($fields);

            $tableName = 'custom_lead_' . $ecdialLists->list_id; // Replace with your actual table name

            foreach ($baseFields as $field) {
                $columnName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $field['field_name'])); // Convert field label to snake case for column name

                if (!Schema::hasColumn($tableName, $columnName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($columnName, $field) {
                        switch ($field['field_type']) {
                            case 'textarea':
                                $table->text($columnName)->nullable()->after('updated_at');
                                break;
                            default:
                                $table->string($columnName, $field['field_max_length'] ?? 255)->nullable()->after('updated_at');
                                break;
                        }
                    });
                }
            }

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
                "status" => $request->status,
                "campaign_id" => $request->campaign_id
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
