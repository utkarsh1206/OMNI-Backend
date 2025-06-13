<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\NeoxCallLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class LiveCallDashboardRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function getLiveCalls($startDate = null, $endDate = null)
    {
        try {
            $now = Carbon::now();

            // Default to today if dates not provided
            $startDate = $startDate ? Carbon::parse($startDate)->startOfDay()->toDateTimeString() : Carbon::today()->startOfDay()->toDateTimeString();
            $endDate   = $endDate   ? Carbon::parse($endDate)->endOfDay()->toDateTimeString()   : Carbon::today()->endOfDay()->toDateTimeString();

            //Get latest live record per call within date range
            $latestLiveLogs = NeoxCallLog::from(DB::raw('neox_call_logs nc'))
                ->join(DB::raw("
            (
                SELECT call_unique_id, MAX(call_date) AS max_date
                FROM neox_call_logs
                WHERE call_date BETWEEN '{$startDate}' AND '{$endDate}'
                GROUP BY call_unique_id
            ) AS latest
        "), function ($join) {
                    $join->on('nc.call_unique_id', '=', 'latest.call_unique_id')
                        ->on('nc.call_date', '=', 'latest.max_date');
                })
                ->whereIn('nc.type', ['call_initiated', 'call_connected'])
                ->select(
                    'nc.call_unique_id',
                    'nc.caller_user_extension',
                    'nc.caller_username',
                    'nc.called_phone_number',
                    'nc.inbound_group_id',
                    'nc.call_date as latest_call_date',
                    'nc.type as latest_type',
                    'nc.campaign_id'
                )
                ->cursor()
                ->map(function ($call) use ($now, $startDate, $endDate) {
                    //Get first `call_initiated` within the range
                    $initiated = NeoxCallLog::where('call_unique_id', $call->call_unique_id)
                        ->where('type', 'call_initiated')
                        ->whereBetween('call_date', [$startDate, $endDate])
                        ->orderBy('call_date', 'asc')
                        ->value('call_date');

                    $startTime = $initiated ? Carbon::parse($initiated) : $call->latest_call_date;

                    $call->duration = gmdate("H:i:s", $startTime->diffInSeconds($now));
                    $call->status = $call->latest_type === 'call_connected' ? 'INCALL' : 'RINGING';

                    unset($call->latest_type, $call->latest_call_date);

                    return $call;
                });

            $results = $latestLiveLogs->values()->all();

            if (empty($results)) {
                return response()->json([
                    'message' => trans("message.record_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $results,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Live Calls : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
