<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\LiveAgents;
use App\Models\LiveCall;
use App\Models\LiveCallLog;
use App\Models\UserDailyActivity;
use App\Models\Users;
use Carbon\Carbon;
use Exception;

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

            $this->trackUserActivity($request, $user, $uniqueId);

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
            return LiveAgents::where('user_id', $userId)
                ->where('extension', $extension)
                ->update(['customer' => $dialed_phone_number, 'uniqueid' => $uniqueId, 'call_date' => Carbon::now(), 'call_direction' => 'out_call', 'status' => 'call']);
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
            UserDailyActivity::create([
                'campaign_id'    => $request->campaign_id,
                'user_id'        => $user->id,
                'call_unique_id' => $uniqueId,
                'activity'       => 'Outcall',
            ]);
        } catch (Exception $e) {
            generateLogs('error', 'Tracking user activity failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
