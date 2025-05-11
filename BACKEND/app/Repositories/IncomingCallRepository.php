<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\LiveAgents;
use App\Models\LiveCallLog;
use App\Models\NeoxTempCallLog;
use App\Models\PauseResumeUserLog;
use App\Models\UserDailyActivity;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class IncomingCallRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function handleIncomingCall($request)
    {
        DB::beginTransaction();

        try {
            $userCheck = $this->getNeoxTempCallLog($request->phone);

            if (!$userCheck) {
                DB::rollBack();
                return null;
            }

            $lastStatus = $this->getLastCallStatus($userCheck->call_unique_id);
            $call_status = $this->resolveCallStatus($lastStatus, $request);

            $liveCallLogData = $this->prepareLiveCallLogData($request, $userCheck, $call_status);

            $liveCallLog = LiveCallLog::updateOrCreate(
                ['call_unique_id' => $userCheck->call_unique_id],
                $liveCallLogData
            );

            $this->updateTempCallLogStatus($userCheck->temp_id);

            $updatedTempCallLog = NeoxTempCallLog::find($userCheck->temp_id);

            $live_call_neox = [
                'uniqueid' => $userCheck->call_unique_id,
                'customer' => $userCheck->customer,
                'call_date' => Carbon::parse($userCheck->created_at)->format('Y-m-d H:i:s'),
                'status' => 'call',
                'call_direction' => 'in_call',
            ];

            LiveAgents::where('uniqueid', $userCheck->call_unique_id)
                ->update($live_call_neox);

            $updatedLiveAgent = $this->getLiveCallData($request);

            $this->logUserActivity($request, $updatedLiveAgent);

            DB::commit();

            $data = [
                'live_call_log' => $liveCallLog,
                'live_agent' => $updatedLiveAgent,
                'temp_call_log' => $updatedTempCallLog
            ];

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.success')
            )->original + ['neox_status' => true];
        } catch (Exception $e) {
            DB::rollBack();
            generateLogs('error', "Error in handleIncomingCall: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function getNeoxTempCallLog($phone)
    {
        $user_check = NeoxTempCallLog::select(
            'neox_temp_call_logs.id as temp_id',
            'neox_temp_call_logs.created_at',
            'neox_temp_call_logs.*',
            'live_agents.*'
        )
            ->leftJoin('live_agents', 'live_agents.extension', '=', 'neox_temp_call_logs.caller_user_extension')
            ->where('neox_temp_call_logs.caller_user_extension', $phone)
            ->whereDate('neox_temp_call_logs.created_at', Carbon::today())
            ->where('neox_temp_call_logs.call_initiated_status', 'unread')
            ->where('neox_temp_call_logs.call_connected_status', 'unread')
            ->where('neox_temp_call_logs.call_hangup_status', 'unread')
            ->whereNotNull('neox_temp_call_logs.caller_user_extension')
            ->where('neox_temp_call_logs.type', '!=', 'call_history')
            ->where('neox_temp_call_logs.call_type', 'incoming')
            ->orderByDesc('neox_temp_call_logs.id')
            ->first();

        return $user_check;
    }

    private function getLastCallStatus($call_unique_id)
    {
        return NeoxTempCallLog::where('call_unique_id', $call_unique_id)
            ->orderByDesc('id')
            ->first();
    }

    private function resolveCallStatus($lastStatus, $request)
    {
        if (!$lastStatus) return 'Unknown';

        switch ($lastStatus->type) {
            case 'CALL_INITIATED':
                $ttt = PauseResumeUserLog::create([
                    "user_id" => $request->id,
                    "campaign_id" => $request->campaign_id,
                    "user_status" => 'Pause',
                    "pause_time" => Carbon::now()->format('H:i:s'),
                ]);
                return "In Queue";

            case 'CALL_CONNECTED':
                return "call";

            case 'CALL_HANGUP':
                return "Disposition";

            default:
                return "Unknown";
        }
    }

    private function prepareLiveCallLogData($request, $userCheck, $call_status)
    {
        $data = [
            "user_id" => $request->id,
            "agent_extension" => $request->phone,
            "campaign_id" => $request->campaign_id,
            "call_unique_id" => $userCheck->call_unique_id,
            "mobile_number" => $userCheck->called_phone_number,
            "call_date" => Carbon::parse($userCheck->created_at)->format('Y-m-d H:i:s'),
            "call_status" => $call_status,
            "actual_call_status" => "Active",
            "call_direction" => 'in_call',
        ];
        return $data;
    }

    private function updateTempCallLogStatus($temp_id)
    {
        NeoxTempCallLog::where('id', $temp_id)->update([
            "call_initiated_status" => "read",
            "call_connected_status" => "read",
            "call_hangup_status" => "read"
        ]);
    }

    private function getLiveCallData($request)
    {
        return LiveAgents::where('user_id', $request->id)
            ->where('extension', $request->phone)
            ->first();
    }

    private function logUserActivity($request, $liveCall)
    {
        UserDailyActivity::create([
            "campaign_id" => $request->campaign_id,
            "user_id" => $request->id,
            "call_unique_id" => $liveCall->uniqueid ?? null,
            "activity" => 'Incall'
        ]);
    }
}
