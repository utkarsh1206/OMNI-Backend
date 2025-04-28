<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\NeoxCallLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class CallLogReportRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    /**
     * Retrieves the call report data with pagination or all records.
     */
    public function getCallReport(Request $request)
    {
        try {
            $query = $this->buildCallLogQuery($request);
            if (!$query) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.record_not_found'));
            }

            $query->orderBy('id', 'desc');
            $formatted = $this->fetchCallLogs($query, $request->input('page'));

            return $this->apiResponse->getResponseStructure(trans('message.success'), $formatted, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in getCallReport: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Exports the call log data as CSV.
     */
    public function exportCsvData(Request $request)
    {
        try {
            $callLogs = $this->buildCallLogQuery($request)->get();
            if ($callLogs->isEmpty()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.record_not_found'));
            }

            return $this->generateCsvExport($callLogs);
        } catch (Exception $e) {
            generateLogs('error', 'Error in exportCsvData: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Builds the query for fetching call logs based on request filters.
     */
    private function buildCallLogQuery(Request $request)
    {
        if (!$request->filled(['start_date', 'end_date'])) {
            return null;
        }

        return NeoxCallLog::query()
            ->where('type', 'CALL_HISTORY')
            ->whereBetween(DB::raw('DATE(call_date)'), [
                Carbon::parse($request->start_date)->toDateString(),
                Carbon::parse($request->end_date)->toDateString()
            ])
            ->when($request->filled('campaign_id'), function ($query) use ($request) {
                $query->where('campaign_id', $request->campaign_id);
            })
            ->when($request->filled('agent'), function ($query) use ($request) {
                $query->where('caller_username', $request->agent);
            });
    }

    /**
     * Fetches the call logs either paginated or all records.
     */
    private function fetchCallLogs($query, $page)
    {
        $perPage = config('omni.REPORT_DATA_PER_PAGE');
        if ($page === config('omni.ALL_PAGE')) {
            return $this->formatCallLogs($query->get());
        } else {
            $paginated = $query->paginate($perPage);
            return $this->formatCallLogs($paginated->items());
        }
    }

    /**
     * Formats the call logs for the API response.
     */
    protected function formatCallLogs($logs)
    {
        return collect($logs)->map(function ($log) {
            return [
                'call_unique_id' => $log->call_unique_id,
                'campaign_id' => $log->campaign_id,
                'inbound_group_id' => $log->inbound_group_id,
                'user_group' => $log->user_group,
                'call_date' => $this->formatDate($log->call_date),
                'call_type' => $log->call_type,
                'caller_username' => $log->caller_username,
                'caller_user_extension' => $log->caller_user_extension,
                'called_phone_number' => $log->called_phone_number,
                'call_did_pattern' => $log->call_did_pattern,
                'call_total_duration' => $this->formatDuration($log->call_total_duration),
                'call_talk_duration' => $this->formatDuration($log->call_talk_duration),
                'call_status' => $log->call_status,
                'call_dispo_status' => $log->call_dispo_status,
                'call_term_reason' => $log->call_term_reason,
                'call_recording_url' => $log->call_recording_url,
                'comments' => $log->comments
            ];
        });
    }

    /**
     * Formats the date.
     */
    protected function formatDate($dateTimeString)
    {
        return Carbon::parse($dateTimeString)->format('Y-m-d H:i:s');
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
     * Generates CSV export for the call logs.
     */
    private function generateCsvExport($callLogs)
    {
        $headers = [
            'Call Unique ID',
            'Campaign ID',
            'Inbound Group ID',
            'User Group',
            'Call Date',
            'Call Type',
            'Caller Username',
            'Caller User Extension',
            'Called Phone Number',
            'Call DID Pattern',
            'Total Duration',
            'Talk Duration',
            'Status',
            'Disposition Status',
            'Term Reason',
            'Comments',
            'Recording URL',
        ];

        $csvData = $this->formatCallLogsForCsv($callLogs);

        $callback = function () use ($headers, $csvData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers); // Write the header row
            foreach ($csvData as $row) {
                fputcsv($file, $row); // Write data rows
            }
            fclose($file);
        };

        return new StreamedResponse($callback, ResponseHTTP::HTTP_OK, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment;filename="call_logs_report.csv"',
        ]);
    }

    /**
     * Formats the call logs for CSV export.
     */
    protected function formatCallLogsForCsv($logs)
    {
        return $logs->map(function ($log) {
            return [
                $log->call_unique_id,
                $log->campaign_id,
                $log->inbound_group_id,
                $log->user_group,
                $this->formatDate($log->call_date),
                $log->call_type,
                $log->caller_username,
                $log->caller_user_extension,
                $log->called_phone_number,
                $log->call_did_pattern,
                $this->formatDuration($log->call_total_duration),
                $this->formatDuration($log->call_talk_duration),
                $log->call_status,
                $log->call_dispo_status,
                $log->call_term_reason,
                $log->comments,
                $log->call_recording_url,
            ];
        })->toArray();
    }

    /**
     * Generates chart data for call logs (e.g., daily summary).
     */
    public function getChartData(Request $request)
    {
        try {
            $query = $this->buildCallLogQuery($request);
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
        return $this->getGroupedData($request, 'call_status');
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
            $query = $this->buildCallLogQuery($request);
            if (!$query) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.record_not_found'));
            }

            $groupedData = $query->select(
                $groupByField,
                DB::raw('COUNT(*) as total_calls')
            )
                ->groupBy($groupByField)
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
