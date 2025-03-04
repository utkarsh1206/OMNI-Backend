<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\AgentLog;
use App\Models\AgentTransitionLog;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgentActivityRepository
{

    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    private function getAgentActivityListing(Request $request)
    {
        try {
            $validated = $request->only(['users', 'start_date', 'end_date']);
            $users = $validated['users'];
            $campaign = $request->input('campaign', null);
            $start_date = $validated['start_date'];
            $end_date = $validated['end_date'];
            $per_page = $request->input('per_page', 15);

            $agent_logs_data = $this->getAgentLogQuery($users, $start_date, $end_date, $campaign)->paginate($per_page);
            $formattedData = $this->formatAgentLogData($agent_logs_data);
            $agent_logs_data->setCollection($formattedData);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $agent_logs_data, trans('message.data_fetch'));
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function downloadAgentActivityCSV(Request $request)
    {
        try {
            $validated = $request->all();
            $users = $validated['users'];
            $start_date = $validated['start_date'];
            $end_date = $validated['end_date'];
            $campaign = $request->input('campaign', null);

            $agent_logs = $this->getAgentLogQuery($users, $start_date, $end_date, $campaign)->get();

            $response = new StreamedResponse(function () use ($agent_logs) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'USER',
                    'EXTENSION',
                    'CAMPAIGN',
                    'USER GROUP',
                    'DATE',
                    'LOGGED IN DURATION'
                ]);

                foreach ($agent_logs as $log) {
                    $logged_in_duration = ($log->event_stop_epoch < 1) ? 0 : ($log->event_stop_epoch - $log->event_start_epoch);
                    fputcsv($handle, [
                        $log->user . ' - ' . ($log->getUser->full_name ?? ''),
                        $log->extension,
                        $log->campaign_id . ' - ' . ($log->getCampaign->campaign_name ?? ''),
                        $log->user_group . ' - ' . ($log->getUserGroup->group_name ?? ''),
                        $log->event_date,
                        gmdate('H:i:s', $logged_in_duration)
                    ]);

                    fputcsv($handle, [
                        'DATE/TIME',
                        'EVENT',
                        'PAUSE CODE',
                        'PAUSE DURATION',
                        'PREVIEW DURATION',
                        'WAIT DURATION',
                        'DIALING DURATION',
                        'TALK DURATION',
                        'DISPO DURATION',
                        'BREAK DURATION',
                        'HOLD DURATION',
                        'STATUS'
                    ]);

                    $transition_details = $this->getTransitionDetails($log->user_log_id);
                    foreach ($transition_details['transition_data'] as $transition) {
                        fputcsv($handle, [
                            $transition['event_time'],
                            $transition['event'],
                            $transition['pause_code'] ?? '--',
                            $transition['pause_duration'] ?? '--',
                            $transition['preview_duration'] ?? '--',
                            $transition['wait_duration'] ?? '--',
                            $transition['dial_duration'] ?? '--',
                            $transition['talk_duration'] ?? '--',
                            $transition['dispo_duration'] ?? '--',
                            $transition['break_duration'] ?? '--',
                            $transition['hold_duration'] ?? '--',
                            $transition['status'] ?? '--'
                        ]);
                    }

                    $totals = $transition_details['totals'];
                    fputcsv($handle, [
                        'TOTAL',
                        '',
                        '',
                        $totals['total_pause'],
                        $totals['total_preview'],
                        $totals['total_wait'],
                        $totals['total_dial'],
                        $totals['total_talk'],
                        $totals['total_dispo'],
                        $totals['total_break'],
                        $totals['total_hold'],
                        ''
                    ]);

                    fputcsv($handle, []);
                }

                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="agent_activity_log.csv"');

            return $response;
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function downloadAgentActivityPDF(Request $request)
    {
        try {
            $validated = $request->only(['users', 'start_date', 'end_date']);
            $users = $validated['users'];
            $start_date = $validated['start_date'];
            $end_date = $validated['end_date'];
            $campaign = $request->input('campaign', null);

            $agent_logs_data = $this->getAgentLogQuery($users, $start_date, $end_date, $campaign)->get();

            $formattedData = $this->formatAgentLogData($agent_logs_data);

            return response()->json([
                'data' => $formattedData,
            ]);
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function getTransitionDetails($user_log_id)
    {
        $transition_logs = AgentTransitionLog::where('user_log_id', $user_log_id)->get();

        $transition_data = [];
        $total_seconds = [
            'pause' => 0,
            'preview' => 0,
            'wait' => 0,
            'talk' => 0,
            'dispo' => 0,
            'dial' => 0,
            'hold' => 0,
            'rec' => 0,
            'xfer' => 0,
            'break' => 0,
        ];

        foreach ($transition_logs as $index => $row) {
            $break_duration = 0;
            $next_pause = $transition_logs->skip($index + 1)->first();
            if ($next_pause && $next_pause->pause_duration > 0 && $row->sub_status != "") {
                $break_duration = $next_pause->pause_duration;
            }

            $pause_code_name = $this->getPauseCodeName($row->sub_status);

            $transition_data[] = [
                'serial_number' => $index + 1,
                'event_time' => $row->event_time,
                'event' => $row->event,
                'pause_code' => $pause_code_name,
                'pause_duration' => ($row->pause_duration > 0) ? gmdate('H:i:s', $row->pause_duration) : '-',
                'preview_duration' => ($row->preview_duration > 0) ? gmdate('H:i:s', $row->preview_duration) : '-',
                'wait_duration' => ($row->wait_duration > 0) ? gmdate('H:i:s', $row->wait_duration) : '-',
                'dial_duration' => ($row->dialing_duration > 0) ? gmdate('H:i:s', $row->dialing_duration) : '-',
                'talk_duration' => ($row->talk_duration > 0) ? gmdate('H:i:s', $row->talk_duration) : '-',
                'dispo_duration' => ($row->dispo_duration > 0) ? gmdate('H:i:s', $row->dispo_duration) : '-',
                'break_duration' => ($break_duration > 0) ? gmdate('H:i:s', $break_duration) : '-',
                'hold_duration' => ($row->park_duration > 0) ? gmdate('H:i:s', $row->park_duration) : '-',
                'status' => $row->dispoStatus
            ];

            // Accumulate total seconds
            $total_seconds['pause'] += $row->pause_duration;
            $total_seconds['preview'] += $row->preview_duration;
            $total_seconds['wait'] += $row->wait_duration;
            $total_seconds['talk'] += $row->talk_duration;
            $total_seconds['dispo'] += $row->dispo_duration;
            $total_seconds['dial'] += $row->dialing_duration;
            $total_seconds['hold'] += $row->park_duration;
            $total_seconds['rec'] += $row->rec_duration;
            $total_seconds['xfer'] += $row->xfer_wait_duration;
            $total_seconds['break'] += $break_duration;
        }

        // Convert total seconds to HH:MM:SS format
        $total_duration = [
            'total_pause' => gmdate('H:i:s', $total_seconds['pause']),
            'total_preview' => gmdate('H:i:s', $total_seconds['preview']),
            'total_wait' => gmdate('H:i:s', $total_seconds['wait']),
            'total_dial' => gmdate('H:i:s', $total_seconds['dial']),
            'total_talk' => gmdate('H:i:s', $total_seconds['talk']),
            'total_dispo' => gmdate('H:i:s', $total_seconds['dispo']),
            'total_break' => gmdate('H:i:s', $total_seconds['break']),
            'total_hold' => gmdate('H:i:s', $total_seconds['hold']),
        ];

        return [
            'transition_data' => $transition_data,
            'totals' => $total_duration
        ];
    }

    private function getPauseCodeName($pause_code)
    {
        if (empty($pause_code)) {
            return '';
        }

        return DB::table('ecdial_pause_codes')
            ->where('pause_code', $pause_code)
            ->value('pause_code_name');
    }
    private function getAgentLogQuery(array $users, string $start_date, string $end_date, ?string $campaign = null)
    {
        $query = AgentLog::select('user_log_id', 'user', 'extension', 'campaign_id', 'user_group', 'event_date', 'event_start_epoch', 'event_stop_epoch')
            ->with([
                'getUser:user,full_name',
                'getCampaign:campaign_id,campaign_name',
                'getUserGroup:user_group,group_name'
            ])
            ->whereIn('user', $users)
            ->whereBetween('event_date', [$start_date, $end_date])
            ->orderBy('event_date', 'desc');

        if ($campaign) {
            $query->where('campaign_id', $campaign);
        }

        return $query;
    }

    private function formatAgentLogData($agent_logs_data)
    {
        return $agent_logs_data->map(function ($log) {
            $logged_in_duration = ($log->event_stop_epoch < 1) ? 0 : ($log->event_stop_epoch - $log->event_start_epoch);

            return [
                'user_log_id' => $log->user_log_id,
                'user' => $log->user . ' - ' . ($log->getUser->full_name ?? null),
                'extension' => $log->extension,
                'campaign_id' => $log->campaign_id . ' - ' . ($log->getCampaign->campaign_name ?? null),
                'user_group' => $log->user_group . ' - ' . ($log->getUserGroup->group_name ?? null),
                'event_date' => $log->event_date,
                'logged_in_duration' => gmdate('H:i:s', $logged_in_duration),
                'transition_details' => $this->getTransitionDetails($log->user_log_id),
            ];
        });
    }
    public function index(Request $request)
    {
        try {
            $validated = $request->validated();
            $format = $validated['format'] ?? 'json';

            if ($format === 'csv') {
                return $this->downloadAgentActivityCSV($request);
            }

            if ($format === 'pdf' || $format === 'graph') {
                return $this->downloadAgentActivityPDF($request);
            }
            return $this->getAgentActivityListing($request);
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }

    }

}
