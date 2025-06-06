<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\CustomApiResponse;
use App\Models\CustomCdrLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CustomCdrLeadController extends Controller
{
    protected $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function getLeadByCallUniqueId(Request $request, $callUniqueId)
    {
        try {
            // Step 1: Find the record by call_unique_id
            $cdrLead = CustomCdrLead::where('call_unique_id', $callUniqueId)->first();

            if (!$cdrLead) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, 'CDR lead not found.');
            }

            $listId = $cdrLead->list_id;
            $leadId = $cdrLead->lead_id;

            // Step 2: Build the table name
            $table = "custom_lead_" . $listId;

            // Step 3: Check if table exists
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $table, trans('message.table_not_found'));
            }

            // Step 4: Get columns and query
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            $query = DB::table($table);

            return $this->getSingleLead($query, 'id', $leadId, $columns);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching lead by call_unique_id: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function getSingleLead($query, $column, $value, $columns)
    {
        try {
            $lead = $query->where($column, $value)->first();

            if (!$lead) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.lead_not_found'));
            }

            $leadData = $this->formatLeadData($lead, $columns);

            return $this->apiResponse->getResponseStructure(trans('message.success'), [
                'columns' => $columns,
                'data' => $leadData
            ], trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in getting single lead: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function formatLeadData($lead, $columns)
    {
        return collect($columns)->mapWithKeys(function ($column) use ($lead) {
            return [$column => $lead->$column ?? null];
        })->toArray();
    }
}
