<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\CallHoldUnholdLog;
use App\Models\LiveAgents;
use App\Models\LiveCall;
use App\Models\LiveCallLog;
use App\Models\UserDailyActivity;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Exception;

class HoldApiRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function processHoldAction($result, $request)
    {
        if (isset($result['code']) && $result['code'] === 0) {
            $data = $this->handleHoldApi($request);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.success'));
        } else {
            return $result;
        }
    }

    public function handleHoldApi($request)
    {
        DB::beginTransaction();

        try {
            $user = $this->getUser($request->user_id);
            $liveCallData = $this->getLiveCallData($user, $request);

            $callStatus = $this->getCallStatus($request->action);

            $holdLog = $this->logCallHold($user->id, $liveCallData, $callStatus);

            // Prepare call log data
            $callLogData = $this->prepareCallLogData($request, $user, $liveCallData, $callStatus);

            // Store in call log and update live call
            $callLog = $this->storeCallLog($callLogData);

            // Track user activity
            $this->trackUserActivity($request, $user, $liveCallData, $callStatus);

            DB::commit();

            return [
                'hold_log' => $holdLog,
                'call_log' => $callLog
            ];
        } catch (Exception $e) {
            DB::rollBack();
            generateLogs('error', "Error in handleHoldApi: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function getUser(string $username)
    {
        return Users::where('username', $username)->firstOrFail();
    }

    private function getLiveCallData($user, $request)
    {
        try {
            return LiveAgents::where('user_id', $user->id)
                ->where('extension', $request->phone)
                ->first();
        } catch (Exception $e) {
            generateLogs('error', "Error in getLiveCallData: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function getCallStatus($action)
    {
        return $action === 'UnPark' ? 'Unhold' : 'Hold';
    }

    private function logCallHold($userId, $liveCall, $callStatus)
    {
        try {
            return CallHoldUnholdLog::create([
                'user_id'        => $userId,
                'call_unique_id' => $liveCall->uniqueid,
                'mobile_number'  => $liveCall->customer,
                'call_date'      => $liveCall->call_date,
                'call_status'    => $callStatus,
                'call_direction' => 'out_call',
            ]);
        } catch (Exception $e) {
            generateLogs('error', "Error in logCallHold: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function prepareCallLogData($request, $user, $liveCall, $callStatus)
    {
        return [
            'user_id'        => $user->id,
            'agent_extension' => $request->phone,
            'campaign_id'    => $request->campaign_id,
            'call_unique_id' => $liveCall->uniqueid,
            'mobile_number'  => $liveCall->customer,
            'call_date'      => $liveCall->call_date,
            'call_status'    => $callStatus,
            'actual'         => 'Active',
            'call_direction' => 'out_call',
        ];
    }

    private function storeCallLog(array $callLogData)
    {
        try {
            $callLog = LiveCallLog::create($callLogData);

            // Update or create the live call record
            LiveCall::updateOrCreate(
                ['call_unique_id' => $callLogData['call_unique_id']],
                $callLogData
            );

            return $callLog;
        } catch (Exception $e) {
            generateLogs('error', "Error in storeCallLog: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function trackUserActivity($request, $user, $liveCall, $callStatus)
    {
        try {
            return UserDailyActivity::create([
                'campaign_id'    => $request->campaign_id,
                'user_id'        => $user->id,
                'call_unique_id' => $liveCall->uniqueid,
                'activity'       => $callStatus,
            ]);
        } catch (Exception $e) {
            generateLogs('error', "Error in trackUserActivity: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
