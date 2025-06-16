<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Response\CustomApiResponse;
use App\Models\CustomCdrLead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
                return $this->getSingleLead($query, 'mobile', $mobileNumber, $columns);
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
            $lead = $query->where($column, $value)->orderBy('created_at', 'desc')->first();

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

    public function getLeadHistoryByMobile(int $listId, string $mobile)
    {
        try {
            $table = "custom_lead_" . $listId;

            if (!$this->isTableExists($table)) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    $table,
                    trans('message.table_not_found')
                );
            }

            $authorizedFields = DB::table('ecdial_lists_fields')
                ->where('list_id', $listId)
                ->where('show_authority', 'Y')
                ->pluck('field_name') // Retrieve only the 'field_name' column values
                // ->map(fn($f) => strtolower($f))
                ->map(function ($field) {
                    return Str::slug($field, '_'); // Laravel helper to convert "First Name" → "first_name"
                })
                ->toArray(); // Convert the collection to a plain PHP array

            // Fetch table columns
            $columns = DB::getSchemaBuilder()->getColumnListing($table);

            // Get all leads for this mobile
            $leads = DB::table($table)
                ->where('mobile', $mobile)
                ->orderBy('created_at', 'desc') // or 'asc' if you want oldest first
                ->get();

            if ($leads->isEmpty()) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    [],
                    trans('message.lead_not_found')
                );
            }

            // $formattedLeads = $leads->map(function ($lead) use ($columns) {
            //     return $this->formatLeadData($lead, $columns);
            // });
            $formattedLeads = $leads->map(function ($lead) use ($authorizedFields) {
                return $this->formatLeadDataShowAuth($lead, $authorizedFields);
            });

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                [
                    'columns' => $columns,
                    'displayable_fields' => $authorizedFields, // Explicitly show which fields were displayed
                    'data' => $formattedLeads
                ],
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in getting lead history by mobile: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }


    // private function getAllLeads(Request $request, $query, $columns)
    // {
    //     try {
    //         $searchTerm = $request->input('search', '');

    //         $startDate = $request->input('start_date');
    //         $endDate = $request->input('end_date');

    //         if (!empty($searchTerm)) {
    //             $query->where(function ($q) use ($searchTerm) {
    //                 $q->where('mobile', 'like', "%{$searchTerm}%")
    //                     ->orwhere('first_name', 'like', "%{$searchTerm}%")
    //                     ->orwhere('last_name', 'like', "%{$searchTerm}%");
    //             });
    //         }

    //         // Filter by start and end date (including time)
    //         if ($startDate && $endDate) {
    //             $query->whereBetween('created_at', [$startDate, $endDate]);
    //         } elseif ($startDate) {
    //             $query->where('created_at', '>=', $startDate);
    //         } elseif ($endDate) {
    //             $query->where('created_at', '<=', $endDate);
    //         }

    //         $perPage = $request->input('per_page', config('omni.PER_PAGE'));
    //         $page = $request->input('page', config('omni.DEFAULT_PAGE'));
    //         $data = $page == config('omni.ALL_PAGE') ? $query->get()->map(fn($row) => $this->formatLeadData($row, $columns))->toArray() : $query->paginate($perPage);

    //         return $this->apiResponse->getResponseStructure(trans('message.success'), [
    //             'columns' => $columns,
    //             'data' => $data
    //         ], trans('message.data_fetch'));
    //     } catch (Exception $ex) {
    //         generateLogs('error', 'Error in generating all leads: ', $ex->getMessage());
    //         return $this->apiResponse->handleAndResponseException($ex);
    //     }
    // }

    private function getAllLeads(Request $request, $query, $columns)
    {
        try {
            $searchTerm = $request->input('search', '');

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $table = $query->from; // get the dynamic table name

            // Subquery to get latest created_at per mobile
            $subQuery = DB::table($table)
                ->select(DB::raw('MAX(created_at) as latest_created_at'), 'mobile')
                ->groupBy('mobile');

            // Join original table with subquery to get full lead info
            $query = DB::table($table . ' as main')
                ->joinSub($subQuery, 'latest', function ($join) {
                    $join->on('main.mobile', '=', 'latest.mobile')
                        ->on('main.created_at', '=', 'latest.latest_created_at');
                })
                ->select('main.*');

            // Apply search filter
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('main.mobile', 'like', "%{$searchTerm}%")
                        ->orWhere('main.first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('main.last_name', 'like', "%{$searchTerm}%");
                });
            }

            // Apply date filters
            if ($startDate && $endDate) {
                $query->whereBetween('main.created_at', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('main.created_at', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('main.created_at', '<=', $endDate);
            }

            // $query->orderBy('main.created_at', 'desc'); // To be used if and when required

            // $query->orderBy('main.created_at', 'desc')   // To be used if and when required
            //     ->orderBy('main.id', 'desc');

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $data = $page == config('omni.ALL_PAGE')
                ? $query->get()->map(fn($row) => $this->formatLeadData($row, $columns))->toArray()
                : $query->paginate($perPage);

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

    private function formatLeadDataShowAuth($lead, $authorizedFields)
    {
        return collect($authorizedFields)->mapWithKeys(function ($field) use ($lead) {
            return [$field => $lead->$field ?? null];
        })->toArray();
    }

    public function createLead(int $listId, array $data, ?string $callUniqueId)
    {
        try {
            $table = "custom_lead_" . $listId;

            if (!$this->isTableExists($table)) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    $table,
                    trans('message.table_not_found')
                );
            }

            if (empty($data)) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    $data,
                    trans('message.invalid_data')
                );
            }

            if (!empty($data['mobile'])) {
                $existing = DB::table($table)->where('mobile', $data['mobile'])->exists();

                if ($existing) {
                    return $this->apiResponse->getResponseStructure(
                        trans('message.error'),
                        ['mobile' => $data['mobile']],
                        trans('message.mobile_already_exists', ['mobile' => $data['mobile']])
                    );
                }
            }

            $timestamp = now();
            $data['created_at'] = $timestamp;
            $data['updated_at'] = $timestamp;

            // Insert and get ID
            $leadId = DB::table($table)->insertGetId($data);

            if (!empty($callUniqueId)) {
                CustomCdrLead::create([
                    'lead_id' => $leadId,
                    'list_id' => $listId,
                    'call_unique_id' => $callUniqueId,
                    'lead_json' => json_encode($data),
                ]);
            }

            if ($leadId) {
                $data['id'] = $leadId;
                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $data,
                    trans('message.lead_create_success')
                );
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.error'),
                [],
                trans('message.lead_create_failed')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating lead: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function updateLead(int $listId, int $leadId, array $data, ?string $callUniqueId)
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

            // $data['updated_at'] = now();

            // // Update the lead
            // $updated = DB::table($table)->where('id', $leadId)->update($data);

            // if ($updated) {
            //     return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.lead_update_success'));
            // }

            // return $this->apiResponse->getResponseStructure(trans('message.error'), [], trans('message.no_changes_made'));

            $existingLead = DB::table($table)->where('id', $leadId)->first();

            // Prepare new data
            $newLeadData = (array)$existingLead;
            unset($newLeadData['id'], $newLeadData['created_at'], $newLeadData['updated_at']);

            $newLeadData = array_merge($newLeadData, $data);
            $timestamp = now();
            $newLeadData['created_at'] = $timestamp;
            $newLeadData['updated_at'] = $timestamp;

            $newId = DB::table($table)->insertGetId($newLeadData);

            if (!empty($callUniqueId)) {
                CustomCdrLead::create([
                    'lead_id' => $leadId,
                    'list_id' => $listId,
                    'call_unique_id' => $callUniqueId,
                    'lead_json' => json_encode($newLeadData),
                ]);
            }

            if ($newId) {
                $newLeadData['id'] = $newId;
                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $newLeadData,
                    trans('message.lead_update_success')
                );
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.error'),
                [],
                trans('message.no_changes_made')
            );
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
