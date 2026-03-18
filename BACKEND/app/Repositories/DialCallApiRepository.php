<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\EcdialHopper;
use App\Models\EcdialList;
use App\Models\EcdialLists;
use App\Models\LiveAgents;
use App\Models\LiveCall;
use App\Models\LiveCallLog;
use App\Models\UserDailyActivity;
use App\Models\Users;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Claims\Custom;

class DialCallApiRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function processDialCall($apiResult, $request)
    {
        try {
            if (isset($apiResult['code']) && $apiResult['code'] === 0) {
                $uniqueId = $apiResult['data']['UniqueId'];
                $data = $this->handleSuccessfulCall($uniqueId, $request);

                return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.success'))->original + ['neox_status' => true];
            } else {
                return $apiResult;
            }
        } catch (Exception $e) {
            generateLogs('error', 'DialCall processing failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function handleSuccessfulCall($uniqueId, $request)
    {
        try {
            $user = $this->getUserByUsername($request->user_id);

            $this->updateLiveAgentUniqueId($user->id, $request->phone, $uniqueId, $request->dialed_phone_number);

            $callData = $this->buildCallData($user->id, $request, $uniqueId);

            // $this->trackUserActivity($request, $user, $uniqueId);

            $this->updateAfterSuccessfulDial($request, $uniqueId);

            return [
                'live_call_logs' => $this->createLiveCallLog($callData),
                'live_logs' => $this->createOrUpdateLiveCall($callData),
            ];
        } catch (Exception $e) {
            generateLogs('error', 'Handle successful call failed: ', $e->getMessage());
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

    public function updateLiveAgentUniqueId($userId, $extension, $uniqueId, $dialed_phone_number)
    {
        try {
            // return LiveAgents::where('user_id', $userId)
            //     ->where('extension', $extension)
            //     ->update(['customer' => $dialed_phone_number, 'uniqueid' => $uniqueId, 'call_date' => Carbon::now(), 'call_direction' => 'out_call', 'status' => 'call']);

            $liveAgent = LiveAgents::where('user_id', $userId)
                ->where('extension', $extension)
                ->first();

            if ($liveAgent) {
                $liveAgent->customer = $dialed_phone_number;
                $liveAgent->uniqueid = $uniqueId;
                $liveAgent->call_date =  Carbon::now();
                $liveAgent->call_direction = 'out_call';
                $liveAgent->status = 'incall';
                $liveAgent->save();
            }

            return $liveAgent;
        } catch (Exception $e) {
            generateLogs('error', 'Updating LiveAgent unique ID failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function buildCallData(int $userId, $request, string $uniqueId): array
    {
        return [
            'user_id' => $userId,
            'agent_extension' => $request->phone,
            'campaign_id' => $request->campaign_id,
            'call_unique_id' => $uniqueId,
            'mobile_number' => $request->dialed_phone_number,
            'call_date' => Carbon::now(),
            'call_status' => 'call',
            'actual_call_status' => 'Active',
            'call_direction' => 'out_call',
        ];
    }

    public function createLiveCallLog($data)
    {
        return $this->saveData(LiveCallLog::class, $data);
    }

    public function createOrUpdateLiveCall($data)
    {
        try {
            return LiveCall::updateOrCreate(['call_unique_id' => $data['call_unique_id']], $data);
        } catch (Exception $e) {
            generateLogs('error', 'Updating/Creating LiveCall failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function saveData(string $model, array $data)
    {
        try {
            return $model::create($data);
        } catch (Exception $e) {
            generateLogs('error', "Error saving data to {$model}: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function trackUserActivity($request, $user, $uniqueId)
    {
        try {
            $latestActivity = getLatestUserActivity($user->id, $request->campaign_id);

            if (!$latestActivity) {
                return null;
            }

            // Calculate time difference from last activity
            $timeDiff = calculateTimeDiff($latestActivity->created_at);
            $durationField = $latestActivity->activity === 'Pause'
            ? 'pause_duration'
            : 'resume_duration';

            UserDailyActivity::create([
                'campaign_id'    => $request->campaign_id,
                'user_id'        => $user->id,
                'call_unique_id' => $uniqueId,
                'activity'       => 'Outcall',
                $durationField   => $timeDiff,
            ]);
        } catch (Exception $e) {
            generateLogs('error', 'Tracking user activity failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    private function updateAfterSuccessfulDial($request, $uniqueId)
    {
        $listIds = EcdialLists::where('campaign_id', $request->campaign_id)
            ->where('status', 'active')
            ->pluck('list_id');
        $listId = $listIds->first();

        $table = 'custom_lead_' . $listId;
        $lead = DB::table($table)
            ->where('mobile', $request->dialed_phone_number)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lead) {
            // Handle the case where no lead is found (optional)
            return response()->json(['error' => 'Lead not found'], 404);
        }

        // Only delete from EcdialHopper if the lead status is 'new'
        $leadInEcdialList = EcdialList::where('lead_id', $lead->id)
            ->where('list_id', $listId)
            ->first();

        if ($leadInEcdialList && $leadInEcdialList->status === 'NEW') {
            // Delete from EcdialHopper only if the lead status is 'new'
            EcdialHopper::where('lead_id', $lead->id)
                ->where('list_id', $listId)
                ->delete();
        }

        $calledSinceLastReset = $this->updateCalledSinceLastReset('N');

        EcdialList::where('lead_id', $lead->original_lead_id)
            ->where('list_id', $listId)
            ->update([
                'status' => 'INCALL',
                'user'   => $request->id,
                'called_since_last_reset' => $calledSinceLastReset,
                'called_count' => DB::raw('called_count + 1'),
                'modify_date' => now(),
                'last_local_call_time' => now(),
            ]);


        LiveAgents::where('user_id', $request->id)->update([
            'status' => 'INCALL',
            'uniqueid' => $uniqueId,
            'lead_id' => $lead->original_lead_id,
            'customer' => $request->dialed_phone_number,
            'updated_at' => Carbon::now(),
        ]);

        // return [
        //     'lead_id' => $lead->id,
        //     'list_id' => $listIds,
        //     'user_id' => $request->id,
        //     'status' => 'INCALL',
        //     'customer' => $request->dialed_phone_number,
        //     'uniqueid' => $uniqueId,
        //     'called_since_last_reset' => $calledSinceLastReset,
        //     'call_time' => now(),
        // ];
    }

    private function updateCalledSinceLastReset($value)
    {
        if (str_contains($value, 'Y')) {
            $number = (int) str_replace('Y', '', $value);
            return 'Y' . (++$number);
        }

        return 'Y';
    }
}
