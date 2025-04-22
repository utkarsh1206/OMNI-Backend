<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Response\CustomApiResponse;
use Illuminate\Http\Request;

class CustomLeadRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function getLeadById(Request $request, $listId, $leadId = null)
    {
        try {
            $table = "custom_lead_" . $listId;

            // Check if the table exists
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $table, trans('message.table_not_found'));
            }

            // Get table columns
            $columns = DB::getSchemaBuilder()->getColumnListing($table);

            // If leadId is provided, get only that lead; otherwise, get all leads
            $query = DB::table($table);
            if ($leadId) {
                return $this->getSingleLead($query, 'id', $leadId, $columns);
            }

            // If leadId is not given but mob-no is
            $mobileNumber = $request->query('mob-no');
            if ($mobileNumber) {
                return $this->getSingleLead($query, 'mobile_number', $mobileNumber, $columns);
            }

            return $this->getAllLeads($request, $query, $columns);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in getting Lead By Id: ', $ex->getMessage());
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

    private function getAllLeads(Request $request, $query, $columns)
    {
        try {
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));
            $data = $page == config('omni.ALL_PAGE') ? $query->get()->map(fn($row) => $this->formatLeadData($row, $columns))->toArray() : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(trans('message.success'), [
                'columns' => $columns,
                'data' => $data
            ], trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating all leads: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function formatLeadData($lead, $columns)
    {
        return collect($columns)->mapWithKeys(function ($column) use ($lead) {
            return [$column => $lead->$column ?? null];
        })->toArray();
    }

    public function updateLead(int $listId, int $leadId, array $data)
    {
        try {
            $table = "custom_lead_" . $listId;

            if (!$this->isTableExists($table)) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $table, trans('message.table_not_found'));
            }

            // Check if the lead exists
            if (!DB::table($table)->where('id', $leadId)->exists()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $leadId, trans('message.lead_not_found'));
            }

            // Ensure there is data to update
            if (empty($data)) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $data, trans('message.invalid_data'));
            }

            $data['updated_at'] = now();

            // Update the lead
            $updated = DB::table($table)->where('id', $leadId)->update($data);

            if ($updated) {
                return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.lead_update_success'));
            }

            return $this->apiResponse->getResponseStructure(trans('message.error'), [], trans('message.no_changes_made'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating lead: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function deleteLead(int $listId, int $leadId)
    {
        try {
            $table = "custom_lead_" . $listId;

            if (!$this->isTableExists($table)) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $table, trans('message.table_not_found'));
            }

            // Check if the lead exists
            if (!DB::table($table)->where('id', $leadId)->exists()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $leadId, trans('message.lead_not_found'));
            }

            // Delete the lead
            $deleted = DB::table($table)->where('id', $leadId)->delete();

            if ($deleted) {
                return $this->apiResponse->getResponseStructure(trans('message.success'), $leadId, trans('message.lead_delete_success'));
            }

            return $this->apiResponse->getResponseStructure(trans('message.error'), $leadId, trans('message.lead_delete_failed'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting lead: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function isTableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }
}
