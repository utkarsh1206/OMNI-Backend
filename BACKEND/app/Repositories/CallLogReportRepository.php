<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\NeoxCallLog;
use App\Services\DispositionListService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Illuminate\Support\Str;

class CallLogReportRepository
{
    private $apiResponse;
    protected $dispositionService;

    public function __construct(CustomApiResponse $customApiResponse, DispositionListService $dispositionService)
    {
        $this->apiResponse = $customApiResponse;
        $this->dispositionService = $dispositionService;
    }

    /**
     * Retrieves the call report data with pagination or all records.
     */
    public function getCallReport(Request $request)
    {
        try {
            $perPage = $request->input('perPage', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $query = $this->buildCallLogQuery($request);

            // Pagination or get all results
            $callLogs = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

            $callLogs->load('dispositionDetail');

            /**
             * Shared transformation logic
             */
            $transformLog = function ($log) {
                // Flatten disposition detail
                $log->disposition_detail = $log->dispositionDetail->disposition_detail ?? null;
                unset($log->dispositionDetail);

                // Recording URL
                $recording = getRecordingFile($log->call_unique_id);
                $recordingResult = ($recording instanceof \Illuminate\Http\JsonResponse)
                    ? $recording->getData(true)
                    : [];

                $log->call_recording_url = $recordingResult['url'] ?? null;

                return $log;
            };

            if ($callLogs instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $callLogs->setCollection(
                    $callLogs->getCollection()->transform($transformLog)
                );
            } else {
                $callLogs = $callLogs->transform($transformLog);
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $callLogs, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in getCallReport: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Exports the call log data as CSV.
     */
    public function exportCsvData(Request $request, $connectionName = null)
    {
        try {
            $selectedColumns = $request->input('selected_columns', []);

            if (empty($selectedColumns)) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    'No columns selected for export'
                );
            }


            $callLogs = $this->buildCallLogQuery($request, true, $connectionName)->get();
            $headers = [];
            $formattedRows = $this->formatCallLogsForCsv($callLogs, $headers, $selectedColumns);
            $url = exportCsv($headers, $formattedRows, 'call_logs');
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                ['file_url' => $url],
                trans('message.report_generated')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in exportCsvData: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Builds the query for fetching call logs based on request filters.
     */
    private function buildCallLogQuery(Request $request, $applyOrderById = true, $connectionName = null)
    {
        $filters = [
            'startDate' => $request->input('startDate', null),
            'endDate' => $request->input('endDate', null),
            'campaign_id' => $request->input('campaign', null),
            'agent' => $request->input('user', null),
        ];

        // Get logged-in user
        $user = $request->user();

        // If startDate and endDate are not provided, return null
        if (is_null($filters['startDate']) || is_null($filters['endDate'])) {
            return null;
        }

        $startDate = Carbon::parse($filters['startDate'])->toDateTimeString();
        $endDate = Carbon::parse($filters['endDate'])->toDateTimeString();
        $query = NeoxCallLog::on($connectionName)
            ->select([
                'neox_call_logs.call_unique_id',
                'campaign_id',
                'inbound_group_id',
                'user_group',
                DB::raw('DATE_FORMAT(call_date, "%Y-%m-%d %H:%i:%s") as call_date'),
                'call_type',
                'caller_username',
                'caller_user_extension',
                'called_phone_number',
                'call_did_pattern',
                DB::raw('DATE_FORMAT(SEC_TO_TIME(call_total_duration), "%H:%i:%s") as call_total_duration'),
                DB::raw('SEC_TO_TIME(call_talk_duration) as call_talk_duration'),
                'call_status',
                'call_dispo_status',
                'call_term_reason',
                'comments',
                'call_recording_url',
                'custom_cdr_leads.lead_json',
            ])
            ->leftJoin('custom_cdr_leads', 'custom_cdr_leads.call_unique_id', '=', 'neox_call_logs.call_unique_id')
            ->where('type', 'call_history')
            ->whereBetween('call_date', [$startDate, $endDate]);
        if ($user && $user->role_id == 3) {
            $query->where('caller_username', $user->username);
        }

        // Conditional ordering
        if ($applyOrderById) {
            $query->orderBy('neox_call_logs.id', 'desc');
        }

        // Apply filters if present
        if (!empty($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (!empty($filters['agent']) && $user?->role_id != 3) {
            $query->where('caller_username', $filters['agent']);
        }

        return $query;
    }

    /**
     * Formats the duration from seconds to HH:MM:SS format.
     */
    protected function formatDuration($totalSeconds)
    {
        $totalSeconds = (int) $totalSeconds;
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    /**
     * Formats the call logs for CSV export.
     */

    protected function formatCallLogsForCsv($logs, &$headers = [], $selectedColumns = [])
    {
        $dispoCache = [];
        $dynamicKeys = [];

        foreach ($logs as $log) {
            $firstDecode = json_decode($log->lead_json, true); // returns a string
            $leadJson = is_string($firstDecode) ? json_decode($firstDecode, true) : $firstDecode;
            if (is_array($leadJson)) {
                // $dynamicKeys = array_unique(array_merge($dynamicKeys, array_keys($leadJson)));
                $keys = array_diff(array_keys($leadJson), ['created_at', 'updated_at', 'omni_lead_source']);
                $dynamicKeys = array_unique(array_merge($dynamicKeys, $keys));
            }
        }

        $staticColumns = [
            'Campaign' => fn($l) => $l->campaign_id,
            'Inbound Group' => fn($l) => $l->inbound_group_id,
            'Call Date' => fn($l) => Carbon::parse($l->call_date)->setTimezone(env('APP_CSVTIMEZONE'))->format('Y-m-d H:i:s'),
            'Caller Name' => fn($l) => $l->caller_username,
            'Caller Extension' => fn($l) => $l->caller_user_extension,
            'Phone Number' => fn($l) => $l->called_phone_number,
            'Call Total Duration' => fn($l) => $l->call_total_duration,
            'Call Talk Duration' => fn($l) => $l->call_talk_duration,
            'Dispo Status' => fn($l, $d) => $d,
            'Uniqueid' => fn($l) => $l->call_unique_id,
            'DID' => fn($l) => $l->call_did_pattern,
            'Type of Call' => fn($l) => $l->call_type,
            'Term Reason' => fn($l) => $l->call_term_reason,
        ];

        $headers = $selectedColumns;

        // Final headers (passed by reference for use in exportCsv)
        // $headers = array_merge($staticHeaders, $dynamicKeys);
        // Map formatted rows
        $rows = $logs->map(function ($log) use (&$dispoCache, $dynamicKeys, $staticColumns, $selectedColumns) {
            $campaignId = $log->campaign_id;
            $dispoLabel = $log->call_dispo_status;

            //  Disposition description resolution
            if ($campaignId && !isset($dispoCache[$campaignId])) {
                $request = new \Illuminate\Http\Request([
                    'campaign_id' => $campaignId,
                    'user' => $log->caller_username
                ]);

                $response = $this->dispositionService->getDispositions($request);
                if ($response instanceof \Illuminate\Http\JsonResponse) {
                    $response = $response->getData(true);
                }
                $dispoCache[$campaignId] = $response;
            }

            if ($campaignId && isset($dispoCache[$campaignId]) && !isset($dispoCache[$campaignId]['errors'])) {
                $dispos = $dispoCache[$campaignId];
                $dispoStatus = $log->call_dispo_status;

                foreach ($dispos as $dispo) {
                    if (isset($dispo['disposition']) && $dispo['disposition'] === $dispoStatus) {
                        $dispoLabel = "{$dispoStatus} - {$dispo['disposition_description']}";
                        break;
                    }
                }
            }

            // Base row
            $row = [
                $log->campaign_id,
                $log->inbound_group_id,
                Carbon::parse($log->call_date)->format('Y-m-d H:i:s'),
                $log->caller_username,
                $log->caller_user_extension,
                $log->called_phone_number,
                $log->call_total_duration,
                $log->call_talk_duration,
                $dispoLabel,
                $log->call_unique_id,
                $log->call_did_pattern,
                $log->call_type,
                $log->call_term_reason,
            ];

            // Append dynamic fields in same order
            $leadJsonRaw = json_decode($log->lead_json, true) ?? [];
            if (is_string($leadJsonRaw)) {
                $leadJson = json_decode($leadJsonRaw, true);
            } elseif (is_array($leadJsonRaw)) {
                $leadJson = $leadJsonRaw;
            } else {
                $leadJson = [];
            }

            // foreach ($dynamicKeys as $key) {
            //     if (in_array($key, ['created_at', 'updated_at', 'omni_lead_source'])) {
            //         continue; // skip these keys
            //     }
            //     $row[] = $leadJson[$key] ?? '';
            // }
            // return $row;

            $row = [];

            foreach ($selectedColumns as $column) {
                if (isset($staticColumns[$column])) {
                    $row[] = $staticColumns[$column]($log, $dispoLabel);
                } elseif (in_array($column, $dynamicKeys)) {
                    $row[] = $leadJson[$column] ?? '';
                } else {
                    $row[] = '';
                }
            }

            return $row;
        });

        return $rows->toArray();
    }

    /**
     * Generates chart data for call logs (e.g., daily summary).
     */
    public function getChartData(Request $request)
    {
        try {
            $query = $this->buildCallLogQuery($request, false);
            if (!$query) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.record_not_found'));
            }

            $summary = $query->select(
                DB::raw('DATE(call_date) as call_day'),
                DB::raw('COUNT(*) as total_calls'),
                DB::raw('SUM(call_total_duration) as total_duration'),
                DB::raw('SUM(call_talk_duration) as total_talk_duration')
            )
                ->groupBy(DB::raw('DATE(call_date)'))
                ->orderBy('call_day', 'asc')
                ->get();

            $formatted = $summary->map(function ($item) {
                return [
                    'date' => $item->call_day,
                    'total_calls' => (int) $item->total_calls,
                    'total_duration' => (int) $item->total_duration,
                    'total_talk_duration' => (int) $item->total_talk_duration,
                    'total_duration_formatted' => $this->formatDuration($item->total_duration),
                    'talk_duration_formatted' => $this->formatDuration($item->total_talk_duration),
                ];
            });

            return $this->apiResponse->getResponseStructure(trans('message.success'), $formatted, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in getChartData: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Gets the call status distribution for charts.
     */
    public function getCallStatusDistribution(Request $request)
    {
        return $this->getGroupedData($request, 'call_dispo_status');
    }

    /**
     * Gets calls grouped by user group for charting.
     */
    public function getCallsByUserGroup(Request $request)
    {
        return $this->getGroupedData($request, 'user_group');
    }

    /**
     * Gets calls grouped by agent for charting.
     */
    public function getCallsByAgent(Request $request)
    {
        return $this->getGroupedData($request, 'caller_username');
    }

    /**
     * A reusable method for grouping data by a specific field (e.g., status, user_group, agent).
     */
    private function getGroupedData(Request $request, $groupByField)
    {
        try {
            $query = $this->buildCallLogQuery($request, false);
            if (!$query) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.record_not_found'));
            }

            $groupedData = $query->select(
                $groupByField,
                DB::raw('COUNT(*) as total_calls')
            )
                ->groupBy($groupByField)
                ->orderBy('total_calls', 'desc')
                ->get();

            $formatted = $groupedData->map(function ($item) use ($groupByField) {
                return [
                    $groupByField => $item->$groupByField ?? 'Unknown',
                    'total_calls' => (int) $item->total_calls,
                ];
            });

            return $this->apiResponse->getResponseStructure(trans('message.success'), $formatted, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in getGroupedData: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
