<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\EcdialList;
use App\Models\LiveAgents;
use App\Models\LiveCall;
use App\Models\LiveCallLog;
use App\Models\NeoxCallDispositionDetail;
use App\Models\PauseResumeUserLog;
use App\Models\UserDailyActivity;
use App\Models\Users;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class DisposCallApiRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function processDisposCall($apiResult, $request)
    {
        try {
            $user = $this->getUserByUsername($request->user_id);
            $liveCallData = $this->getLiveCallData($user, $request);

            if (isset($apiResult['code']) && $apiResult['code'] === 0) {
                $disposData = $this->saveDisposition($request, $user, $liveCallData);
                $liveCall = $this->logLiveCall($user, $liveCallData);
                $this->updateOrCreateLiveCall($liveCallData, $user);
                $this->updateLeadStatus($liveCallData, $disposData);

                if ($liveCallData->call_direction === 'in_call') {
                    $this->pauseAgent($user->id, $liveCallData->campaign_id);
                }

                $this->updateAgentStatus($user, $request, $liveCallData);

                $this->logDailyActivity($request, $user, $liveCallData);

                $data = [
                    'dispos_data' => $disposData,
                    'live_call' => $liveCall,
                ];

                return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.success'))->original + ['neox_status' => true];
            } else {
                $this->logDailyActivity($request, $user, $liveCallData);
                return $apiResult;
            }
        } catch (Exception $e) {
            generateLogs('error', 'DisposCall processing failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function getUserByUsername($username)
    {
        try {
            return Users::where('username', $username)->firstOrFail();
        } catch (Exception $e) {
            generateLogs('error', 'User not found: ', $e->getMessage());
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

    public function saveDisposition($request, $user, $liveCallData)
    {
        try {
            // Gather the data
            $data_dispo = [
                'user_id' => $user->id,
                'campaign_id' => $liveCallData->campaign_id,
                'agent_neox_phone' => $liveCallData->extension,
                'call_unique_id' => $liveCallData->uniqueid,
                'mobile_number' => $liveCallData->customer,
                'disposition_status' => $request->disposition_status,
                'disposition_detail' => $request->comment,
            ];

            // Save the data using Eloquent
            return NeoxCallDispositionDetail::create($data_dispo);
        } catch (Exception $e) {
            generateLogs('error', 'saveDisposition failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function logLiveCall(Users $user, LiveAgents $liveCallData)
    {
        try {
            $data = $this->prepareLiveCallLogData($user, $liveCallData);
            return LiveCallLog::create($data);
        } catch (Exception $e) {
            generateLogs('error', 'logLiveCall failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function updateOrCreateLiveCall(LiveAgents $liveCallData, Users $user)
    {
        try {
            $data = $this->prepareLiveCallLogData($user, $liveCallData);

            LiveCall::updateOrCreate(
                ['call_unique_id' => $liveCallData->uniqueid],
                $data
            );
        } catch (Exception $e) {
            generateLogs('error', 'updateOrCreateLiveCall failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function prepareLiveCallLogData(Users $user, LiveAgents $liveCallData)
    {
        return [
            'user_id'           => $user->id,
            'agent_extension'   => $liveCallData->extension,
            'campaign_id'       => $liveCallData->campaign_id,
            'call_unique_id'    => $liveCallData->uniqueid,
            'mobile_number'     => $liveCallData->customer,
            'call_date'         => $liveCallData->call_date,
            'call_status'       => 'Disposed',
            'actual_call_status' => 'Inactive',
            'dispose_status'    => 'Completed',
            'call_direction'    => 'end_call',
        ];
    }

    private function pauseAgent($userId, $campaignId)
    {
        try {
            PauseResumeUserLog::create([
                'user_id'     => $userId,
                'campaign_id' => $campaignId,
                'user_status' => 'Pause',
                'pause_time'  => Carbon::now()->format('H:i:s'),
            ]);
        } catch (Exception $e) {
            generateLogs('error', 'pauseAgent failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function logDailyActivity(Request $request, Users $user, LiveAgents $liveCallData)
    {
        try {
            $today = Carbon::today()->toDateString();

            $lastActivity = UserDailyActivity::where('user_id', $user->id)
                ->where('campaign_id', $liveCallData->campaign_id)
                ->where('activity', 'Disconnect')
                ->whereDate('created_at', $today)
                ->orderByDesc('id')
                ->first();

            if (!$lastActivity) return;

            $dispoDuration = Carbon::parse($lastActivity->created_at)
                ->diff(Carbon::now())
                ->format('%H:%I:%S');

            UserDailyActivity::create([
                'user_id'       => $user->id,
                'campaign_id'   => $liveCallData->campaign_id,
                'activity'      => 'Disposed',
                'dispo_duration' => $dispoDuration,
                'dispo_code'    => $request->disposition_status,
                'call_unique_id' => $liveCallData->uniqueid,
            ]);
        } catch (Exception $e) {
            generateLogs('error', 'logDailyActivity failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function updateAgentStatus($user, $request, $liveCallData)
    {
        try {
            // $status = ($liveCallData->call_direction === 'in_call') ? 'Pause' : 'Resume';
           
            // LiveAgents::where('user_id', $user->id)
            //     ->where('extension', $request->phone)
            //     ->where('uniqueid', $liveCallData->uniqueid)
            //     ->update(['status' => $status]);

            $liveAgent = LiveAgents::where('user_id', $user->id)
                ->where('extension', $request->phone)
                ->where('uniqueid', $liveCallData->uniqueid)
                ->first();
            if ($liveAgent->dial_method == "MANUAL") {
                $status = "Pause";
            } elseif ($liveAgent->dial_method == "INBOUND_MAN" || $liveAgent->dial_method == "RATIO") {
                $status = "Resume";
            }
            if ($liveAgent) {
                $liveAgent->status = $status;
                $liveAgent->lead_id = 0;
                $liveAgent->save();
            }
        } catch (Exception $e) {
            generateLogs('error', "Error in updateAgentStatus: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function updateLeadStatus($liveCallData, $disposData)
    {
        generateLogs("info", "test ". json_encode($liveCallData));
        generateLogs("info", "disp  ". json_encode($disposData));
        EcdialList::where('lead_id', $liveCallData->lead_id)
            ->update([
                'status' => $disposData->disposition_status,
            ]);
    }
}
