<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\CustomeFieldController;
use App\Models\EcdialCloserLog;
use App\Models\AgentTransitionLog;
use App\Models\EcdialStatus;
use App\Models\EcdialCampaign;
use App\Models\EcdialInboundGroup;
use App\Models\EcdialUser;
use App\Models\InboundCallTransitionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Api\CustomFieldController;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CallCDRListController extends Controller
{
    public function getCallCDRList(Request $request)
    {
        // Validate incoming request parameters
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'per_page' => 'integer|min:1|max:100',
            'ingroup' => 'nullable|array',
            'ingroup.*' => 'string',
            'search_agent' => 'nullable|string',
            'agent_dialed_type' => 'nullable|string|in:ManualBox,DialNext',
            'search_duration' => 'nullable|in:1,2',
            'search_second' => 'nullable|integer',
            'search_phone' => 'nullable|string',
            'search_list' => 'nullable|integer',
            'group_status' => 'nullable|string|in:all,ans_call,abn_call',
            'search_agent_call_rank' => 'nullable|integer',
            'format' => 'nullable|string|in:json,csv,pdf',
            'graph' => 'nullable|string|in:Y'
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Check the requested format and handle CSV download
        if ($request->input('format') === 'csv') {
            return $this->downloadCSV($request, $startDate, $endDate);
        }

        if ($request->input('format') === 'pdf') {
            return $this->downloadPDF($request, $startDate, $endDate);
        }

        if ($request->input('graph') === 'Y') {
            return $this->todoForGraph($request, $startDate, $endDate);
        }

        // Fetch paginated call logs
        $callLogs = $this->getFilteredCallLogs($request, $startDate, $endDate);

        // Transform the data for the JSON response
        $dataList = $this->transformCallLogs($callLogs);

        return response()->json([
            'meta' => [
                'current_page' => $callLogs->currentPage(),
                'last_page' => $callLogs->lastPage(),
                'per_page' =>  $request->input('per_page'),
                'total' => $callLogs->total(),
            ],
            'data' => $dataList,
        ]);
    }

    private function downloadCSV(Request $request, $startDate, $endDate)
    {
        // Fetch all filtered call logs without pagination
        $callLogs = $this->getFilteredCallLogs($request, $startDate, $endDate, false);

        // Check if records exist
        if ($callLogs->isEmpty()) {
            return response()->json(['message' => 'No records found'], 404);
        }

        $response = new StreamedResponse(function () use ($callLogs) {
            $handle = fopen('php://output', 'w');
    
            // Add the CSV headers
            fputcsv($handle, [
                'List ID',
                'DID',
                'Campaign',
                'Ingroup',
                'Call Date',
                'User',
                'Extension',
                'Phone Number',
                'Type of Call',
                'IVR Time',
                'Hold Duration',
                'Talk Duration',
                'Disposition Duration',
                'Total Duration',
                'Customer Setup Time',
                'Customer Ringing Time',
                'Transfer To Agent',
                'Status',
                'Customer Dispo Status',
                'Term Reason',
                'Customer Time',
                'Abandoned Time',
                'Unique ID',
                'Agents Comment',
                'Agent Call Rank',
            ]);
    
            // Add the rows dynamically
            foreach ($callLogs as $listItem) {
                $dataRow = $this->transformCallLogForCSV($listItem);
                fputcsv($handle, $dataRow);
            }
    
            fclose($handle);
        });
    
        // Set the CSV headers
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="call_data.csv"');
    
        return $response;
    }

    private function downloadPDF(Request $request, $startDate, $endDate)
    {
        $callLogs = $this->getFilteredCallLogs($request, $startDate, $endDate, false);
        $dataList = $this->transformCallLogs($callLogs);

        return response()->json([
            'data' => $dataList,
        ]);
    }

    private function todoForGraph(Request $request, $startDate, $endDate)
    {
        $callLogs = $this->getFilteredCallLogs($request, $startDate, $endDate, false);
        $dataList = $this->transformCallLogs($callLogs);

        return response()->json([
            'data' => $dataList,
        ]);
    }

    private function getFilteredCallLogs(Request $request, $startDate, $endDate, $paginate = true)
    {
        $search_query = '';

        // Get search parameters from the request
        $searchAgent = $request->input('search_agent');
        $campaign = $request->input('campaign');
        $agentDialedType = $request->input('agent_dialed_type');
        $searchDuration = $request->input('search_duration');
        $searchSecond = $request->input('search_second');
        $searchPhone = $request->input('search_phone');
        // $groupStatus = $request->input('group_status');
        $searchAgentCallRank = $request->input('search_agent_call_rank');
        $ingroup = $request->input('ingroup'); // Ingroup array
        $search_list = $request->input('search_list');

        // if ($search_list !== null && 
        // !in_array($campaign, ['none', 'empty', 'all']))
        // {

        // }

        // Build search query based on conditions
        if ($searchAgent) {
            $search_query .= " AND user = '$searchAgent'";
        }

        if ($campaign) {
            $search_query .= " AND campaign_id = '$campaign'";
        }

        if ($agentDialedType === "ManualBox") {
            $search_query .= " AND neox_dial_method IN ('AMIMANUAL', 'AMIPREDICTIVE') AND is_dialed_from_manual_box = 'Y'";
        } elseif ($agentDialedType === "DialNext") {
            $search_query .= " AND neox_dial_method IN ('AMIMANUAL', 'AMIPREDICTIVE') AND is_dialed_from_manual_box = 'N'";
        }

        if ($searchDuration && $searchSecond) {
            if ($searchDuration == "1") {
                $search_query .= " AND length_in_sec <= '$searchSecond'";
            } elseif ($searchDuration == "2") {
                $search_query .= " AND length_in_sec >= '$searchSecond'";
            }
        }

        if ($searchPhone) {
            $search_query .= " AND phone_number = '$searchPhone'";
        }

        // Handle multiple ingroups using IN() if provided
        if ($ingroup && is_array($ingroup)) {
            $ingroupList = implode("','", $ingroup); // Convert array to string for SQL IN()
            $search_query .= " AND group_id IN ('$ingroupList')";
        }

        // if ($groupStatus === "all") {
        //     // Assuming $status_query is defined somewhere in your code
        //     $search_query .= $status_query; // Existing status query
        // } elseif ($groupStatus === "ans_call") {
        //     $search_query .= " AND status IN ($status_check_y)";
        // } elseif ($groupStatus === "abn_call") {
        //     $search_query .= " AND status IN ($status_check_n)";
        // }

        if ($searchAgentCallRank) {
            $search_query .= " AND agent_call_rank = $searchAgentCallRank";
        }

        $callLogs = EcdialCloserLog::with(['agentTransitionLogs', 'campaign', 'group', 'status'])
            ->where('call_date', '>=', $startDate)
            ->where('call_date', '<=', $endDate)
            ->where('voice_blaster_call', '=', 'N');

        if (!empty($search_query)) {
            $callLogs->whereRaw("1=1 $search_query");
        }

        // Check if we need pagination
        // if ($paginate) {
        //     return $callLogs->orderBy('closecallid', 'desc')->paginate($request->input('per_page'));
        // } else {
        //     return $callLogs->orderBy('closecallid', 'desc')->get(); // Get all records without pagination
        // }

        // Check if we need pagination
        if ($paginate) {
            return $callLogs->orderBy('closecallid', 'desc')->paginate($request->input('per_page'));
        } else {
            return $callLogs->orderBy('closecallid', 'desc')->get(); // Get all records without pagination
        }
    }

    private function transformCallLogs($callLogs)
    {
        $customFieldController = new CustomFieldController();

        // Transform the data for the response
        return $callLogs->map(function ($listItem) use ($customFieldController) {
            // Aggregate durations
            $parkDuration = (int)$listItem->agentTransitionLogs->sum('park_duration');
            $dispoDuration = (int)$listItem->agentTransitionLogs->sum('dispo_duration');
            $customerDialingDuration = (int)$listItem->agentTransitionLogs->sum('customer_dialing_duration');

            // Fetch status data
            $statusData = EcdialStatus::where('status', $listItem->status)
                ->first(['status_name', 'human_answered']);

            $statusName = $statusData->status_name ?? 'Unknown';
            $callType = $statusData->human_answered === 'Y' ? 'Answered' : 'Abandoned';

            $userFullName = EcdialUser::where('user', $listItem->user)
                ->value('full_name') ?? 'Unknown User';

            // Fetch IVR time and customer setup time using InboundCallTransitionLog model
            $ivrData = InboundCallTransitionLog::select([
                'uniqueid',
                DB::raw('SUM(call_menu_seconds) AS call_menu_seconds'),
                DB::raw("(TIMEDIFF(
                (SELECT MIN(entry_date) FROM inbound_call_transition_log WHERE stage = 'AGNTRSVD' AND uniqueid = '{$listItem->uniqueid}'),
                (SELECT MIN(entry_date) FROM inbound_call_transition_log WHERE stage = 'IVR' AND uniqueid = '{$listItem->uniqueid}' AND entry_date < (
                    SELECT MIN(entry_date) FROM inbound_call_transition_log WHERE stage = 'AGNTRSVD' AND uniqueid = '{$listItem->uniqueid}'
                ))
            )) AS time_difference")
            ])
                ->where('uniqueid', $listItem->uniqueid)
                ->groupBy('uniqueid')
                ->first();

            // Ensure valid integer values for IVR and setup time
            $ivrTime = (int)($ivrData->call_menu_seconds ?? 0);
            $customerSetupTime = $ivrData->time_difference ?? '00:00:00';

            $xferLog = EcdialCloserLog::where('root_uid', $listItem->uniqueid)
                ->where('status', 'XFER')
                ->whereNotIn('xfer_to', ['Feedback', ''])
                ->first(['uniqueid', 'xfer_to', 'root_uid', 'status']);

            $transferToAgent = $xferLog->xfer_to ?? '';

            // Ensure talk and total durations are integers
            $talkDuration = (int)$listItem->talk_duration; // Ensure this is an integer
            $totalDuration = (int)$listItem->length_in_sec; // Ensure this is an integer

            // Format durations
            $talkDurationFormatted = gmdate('H:i:s', $talkDuration);
            $totalDurationFormatted = gmdate('H:i:s', $totalDuration);
            $abandonedTime = $talkDuration === 0 ? gmdate('H:i:s', $totalDuration) : '';

            // Format other durations
            $holdDurationFormatted = gmdate('H:i:s', $parkDuration);
            $dispoDurationFormatted = gmdate('H:i:s', $dispoDuration);
            $ivrTimeFormatted = gmdate('H:i:s', $ivrTime);
            $customerRingTimeFormatted = gmdate('H:i:s', $customerDialingDuration);

            // Now call the CustomFieldController to add custom fields after agent_call_rank
            $customFields = [];
            if (request()->has('campaign') && !empty(request()->input('campaign'))) {
                $customFieldResponse = $customFieldController->custumFieldvalue(new Request([
                    'cdr_list' => $listItem->list_id,
                    'custom_list_id' => $listItem->list_id,
                    'closecallid' => $listItem->closecallid,
                    'isNumberHidingOn' => 'N',
                    'export' => 'no',
                ]));

                // Extract the custom field data from the response
                // $customFields = $customFieldResponse->getData()->data;

                $customFieldData = $customFieldResponse->getData()->data;

                foreach ($customFieldData as $field) {
                    $customFields[$field->label_name] = [
                        'label' => $field->label,
                        'type' => $field->type,
                        'label_name' => $field->label_name,
                        'value' => $field->value,
                        'editable' => $field->editable
                    ];
                }
            }

            $customFieldsh = [];
            if (request()->has('campaign') && !empty(request()->input('campaign'))) {
                if (empty($customFields)) {
                    $customFieldHeader = $customFieldController->custumFieldHeader(new Request([
                        'cdr_list' => $listItem->list_id,
                        'isNumberHidingOn' => 'N',
                        'export' => 'no',
                    ]));

                    $customFieldData = json_decode(json_encode($customFieldHeader->getData()), true);

                    // Check if the list ID exists in the data and the 'fields' key exists
                    if (isset($customFieldData[$listItem->list_id]['fields'])) {
                        // Use 'name' as the key for each field
                        foreach ($customFieldData[$listItem->list_id]['fields'] as $field) {
                            $customFieldsh[$field['label_name']] = [
                                'label' => $field['label'],
                                'label_name' => $field['label_name'],
                                'type' => $field['type'],
                                'editable' => $field['editable'],
                            ];
                        }
                    }
                }
            }

            // $result[$listItem->list_id] = !empty($customFields) ? $customFields : $customFieldsh;

            $result = [
                'list_id' => $listItem->list_id,
                'did' => $listItem->did_pattern,
                'campaign' => $listItem->campaign_id . ' - ' . ($listItem->campaign->campaign_name ?? ''),
                'ingroup' => $listItem->group_id . ' - ' . ($listItem->group->group_name ?? ''),
                'call_date' => $listItem->call_date,
                'user' => $listItem->user . ' - ' . $userFullName,
                'extension' => $listItem->extension,
                'phone_number' => $listItem->phone_number,
                'type_of_call' => $callType,
                'IVR_time' => $ivrTimeFormatted,
                'hold_duration' => $holdDurationFormatted,
                'talk_duration' => $talkDurationFormatted,
                'dispo_duration' => $dispoDurationFormatted,
                'total_duration' => $totalDurationFormatted,
                'customer_setup_time' => $customerSetupTime,
                'customer_ringing_time' => $customerRingTimeFormatted,
                'transfer_to_agent' => $transferToAgent,
                'status' => $statusName,
                'customer_dispo_status' => $listItem->customer_dispo_status,
                'term_reason' => $listItem->term_reason,
                'customer_time' => $listItem->customer_time,
                'abandoned_time' => $abandonedTime,
                'uniqueid' => $listItem->uniqueid,
                'agents_comment' => $listItem->agents_comment,
                'agent_call_rank' => $listItem->agent_call_rank,
            ];

            $result[$listItem->list_id] = !empty($customFields) ? $customFields : $customFieldsh;

            // Return the final result
            return $result;
        });
    }

    private function transformCallLogForCSV($listItem)
    {
        // Similar transformations for CSV output
        $parkDuration = (int)$listItem->agentTransitionLogs->sum('park_duration');
        $dispoDuration = (int)$listItem->agentTransitionLogs->sum('dispo_duration');
        $customerDialingDuration = (int)$listItem->agentTransitionLogs->sum('customer_dialing_duration');

        $statusData = EcdialStatus::where('status', $listItem->status)
            ->first(['status_name', 'human_answered']);

        $statusName = $statusData->status_name ?? 'Unknown';
        $callType = $statusData->human_answered === 'Y' ? 'Answered' : 'Abandoned';

        $userFullName = EcdialUser::where('user', $listItem->user)
            ->value('full_name') ?? 'Unknown User';

        // Fetch IVR time and customer setup time
        $ivrData = InboundCallTransitionLog::select([
            'uniqueid',
            DB::raw('SUM(call_menu_seconds) AS call_menu_seconds'),
            DB::raw("(TIMEDIFF(
            (SELECT MIN(entry_date) FROM inbound_call_transition_log WHERE stage = 'AGNTRSVD' AND uniqueid = '{$listItem->uniqueid}'),
            (SELECT MIN(entry_date) FROM inbound_call_transition_log WHERE stage = 'IVR' AND uniqueid = '{$listItem->uniqueid}' AND entry_date < (
                SELECT MIN(entry_date) FROM inbound_call_transition_log WHERE stage = 'AGNTRSVD' AND uniqueid = '{$listItem->uniqueid}'
            ))
        )) AS time_difference")
        ])
            ->where('uniqueid', $listItem->uniqueid)
            ->groupBy('uniqueid')
            ->first();

        $ivrTime = (int)($ivrData->call_menu_seconds ?? 0);
        $customerSetupTime = $ivrData->time_difference ?? '00:00:00';

        $xferLog = EcdialCloserLog::where('root_uid', $listItem->uniqueid)
            ->where('status', 'XFER')
            ->whereNotIn('xfer_to', ['Feedback', ''])
            ->first(['uniqueid', 'xfer_to', 'root_uid', 'status']);

        $transferToAgent = $xferLog->xfer_to ?? '';

        // Ensure valid integer values for durations
        $talkDuration = (int)$listItem->talk_duration;
        $totalDuration = (int)$listItem->length_in_sec;

        return [
            'List ID' => $listItem->list_id,
            'DID' => $listItem->did_pattern,
            'Campaign' => $listItem->campaign_id . ' - ' . ($listItem->campaign->campaign_name ?? ''),
            'Ingroup' => $listItem->group_id . ' - ' . ($listItem->group->group_name ?? ''),
            'Call Date' => $listItem->call_date,
            'User' => $listItem->user . ' - ' . $userFullName,
            'Extension' => $listItem->extension,
            'Phone Number' => $listItem->phone_number,
            'Type of Call' => $callType,
            'IVR Time' => gmdate('H:i:s', $ivrTime),
            'Hold Duration' => gmdate('H:i:s', $parkDuration),
            'Talk Duration' => gmdate('H:i:s', $talkDuration),
            'Disposition Duration' => gmdate('H:i:s', $dispoDuration),
            'Total Duration' => gmdate('H:i:s', $totalDuration),
            'Customer Setup Time' => $customerSetupTime,
            'Customer Ringing Time' => gmdate('H:i:s', $customerDialingDuration),
            'Transfer To Agent' => $transferToAgent,
            'Status' => $statusName,
            'Customer Dispo Status' => $listItem->customer_dispo_status,
            'Term Reason' => $listItem->term_reason,
            'Customer Time' => $listItem->customer_time,
            'Abandoned Time' =>$talkDuration === 0 ? gmdate('H:i:s', $totalDuration) : '',
            'Unique ID' => $listItem->uniqueid,
            'Agents Comment' => $listItem->agents_comment,
            'Agent Call Rank' => $listItem->agent_call_rank,
        ];
    }
}
