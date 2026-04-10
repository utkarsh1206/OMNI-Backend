<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\LiveAgents;
use App\Models\LiveCall;
use App\Models\LiveCallLog;
use App\Models\PauseResumeUserLog;
use App\Models\UserDailyActivity;
use App\Models\Users;
use Carbon\Carbon;
use Exception;

class HangupApiRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function handleSuccessfulHangup($request, $result)
    {
        try {
            $user_id = explode('@', $request->user_id)[0];
            $user = Users::where('username', $user_id)->firstOrFail();
            $liveCallData = $this->getLiveCallData($user, $request);

            $this->updatePauseLog($user, $request);
            $this->updateAgentStatus($user, $request);

            $logData = $this->prepareLogData($user, $request, $liveCallData);

            $callLogData = $this->updateOrCreateCallLogs($logData);
            $liveCallRecord = $this->updateOrCreateLiveCall($logData);
            // $this->logUserActivity($user, $request, $liveCallData);
            // $this->logDisconnectIfNeeded($liveCallData, $user, $request);

            $data = [
                'message' => $result['message'],
                'dispostion_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'call_log' => $callLogData,
                'live_call' => $liveCallRecord,
            ];

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.success')
            )->original + ['neox_status' => true];
        } catch (Exception $e) {
            generateLogs('error', "Error in handleSuccessfulHangup: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function handleFailedHangup($request, $result)
    {
        try {
            $user_id = explode('@', $request->user_id)[0];
            $user = Users::where('username', $user_id)->firstOrFail();
            $liveCallData = $this->getLiveCallData($user, $request);

            $logData = $this->prepareLogData($user, $request, $liveCallData);

            $this->updateOrCreateCallLogs($logData);
            $this->updateOrCreateLiveCall($logData);
            // $this->logUserActivity($user, $request, $liveCallData);
            // $this->logDisconnectIfNeeded($liveCallData, $user, $request);

            $data = [
                'message' => $result['message'],
                'dispostion_time' => Carbon::now()->format('Y-m-d H:i:s'),
            ];

            return $this->apiResponse->getResponseStructure(
                trans('message.error'),
                $data,
                trans('message.error')
            );
        } catch (Exception $e) {
            generateLogs('error', "Error in handleFailedHangup: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
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

    private function updatePauseLog($user, $request)
    {
        try {
            PauseResumeUserLog::create([
                'user_id' => $user->id,
                'campaign_id' => $request->campaign_id,
                'user_status' => 'Disposition',
                'pause_time' => Carbon::now()->toTimeString(),
            ]);
        } catch (Exception $e) {
            generateLogs('error', "Error in updatePauseLog: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function updateAgentStatus($user, $request)
    {
        try {
            // LiveAgents::where('user_id', $user->id)
            //     ->where('extension', $request->phone)
            //     ->update(['status' => 'Disposition']);

            $liveAgent = LiveAgents::where('user_id', $user->id)
                ->where('extension', $request->phone)
                ->first();

            if ($liveAgent) {
                $liveAgent->status = 'Disposition';
                $liveAgent->save();
            }
        } catch (Exception $e) {
            generateLogs('error', "Error in updateAgentStatus: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function prepareLogData($user, $request, $liveCallData)
    {
        return [
            'user_id' => $user->id,
            'agent_extension' => $request->phone,
            'campaign_id' => $request->campaign_id,
            'call_unique_id' => $liveCallData->uniqueid,
            'mobile_number' => $liveCallData->customer,
            'call_date' => $liveCallData->call_date,
            'call_status' => 'Disposition',
            'actual_call_status' => 'Active',
            'call_direction' => 'end_call',
        ];
    }

    private function updateOrCreateCallLogs($logData)
    {
        try {
            $existing = LiveCallLog::where([
                'call_unique_id' => $logData['call_unique_id'],
                'call_date' => $logData['call_date'],
                'call_status' => 'Disposition',
                'actual_call_status' => 'Active',
                'dispose_status' => 'Pending',
                'call_direction' => 'end_call',
            ])->first();

            return $existing
                ? tap($existing, fn($model) => $model->update($logData))->fresh()
                : LiveCallLog::create($logData);
        } catch (Exception $e) {
            generateLogs('error', "Error in updateOrCreateCallLogs: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function updateOrCreateLiveCall($logData)
    {
        try {
            $liveCall = LiveCall::where('call_unique_id', $logData['call_unique_id'])->first();

            return $liveCall
                ? tap($liveCall, fn($model) => $model->update($logData))->fresh()
                : LiveCall::create($logData);
        } catch (Exception $e) {
            generateLogs('error', "Error in updateOrCreateLiveCall: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function logUserActivity($user, $request, $liveCallData)
    {
        try {
            $lastActivity = UserDailyActivity::where('user_id', $user->id)
                ->where('campaign_id', $request->campaign_id)
                ->orderBy('id', 'desc')
                ->first();

            $data = [
                'campaign_id' => $request->campaign_id,
                'user_id' => $user->id,
                'call_unique_id' => $liveCallData->uniqueid,
                'activity' => 'Endcall',
            ];

            if ($lastActivity) {
                $duration = Carbon::parse($lastActivity->created_at)
                    ->diff(Carbon::now())
                    ->format('%H:%I:%S');
                $data['call_duration'] = $duration;
            }

            UserDailyActivity::create($data);
        } catch (Exception $e) {
            generateLogs('error', "Error in logUserActivity: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function logDisconnectIfNeeded($liveCallData, $user, $request)
    {
        try {
            if ($liveCallData && $liveCallData->uniqueid) {
                $exists = UserDailyActivity::where('call_unique_id', $liveCallData->uniqueid)
                    ->where('activity', 'Disconnect')
                    ->exists();

                if (!$exists) {
                    UserDailyActivity::create([
                        'user_id' => $user->id,
                        'campaign_id' => $request->campaign_id,
                        'call_unique_id' => $liveCallData->uniqueid,
                        'activity' => 'Disconnect',
                    ]);
                }
            }
        } catch (Exception $e) {
            generateLogs('error', "Error in logDisconnectIfNeeded: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
