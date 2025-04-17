<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\LiveAgents;
use App\Models\LiveCall;
use App\Models\LiveCallLog;
use Exception;

class AddPartyApiRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function handleAddParty($apiResult, $request)
    {
        try {
            if (isset($apiResult['code']) && $apiResult['code'] === 0) {
                $liveAgent = LiveAgents::where('user_id', $request->id)
                    ->where('extension', $request->phone)
                    ->first();

                if (!$liveAgent) {
                    return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.error'));
                }

                $logData = [
                    'user_id'           => $request->id,
                    'agent_extension'   => $request->phone,
                    'campaign_id'       => $request->campaign_id,
                    'call_unique_id'    => $liveAgent->uniqueid,
                    'mobile_number'     => $liveAgent->customer,
                    'call_date'         => $liveAgent->call_date,
                    'call_status'       => 'Party added for conference',
                    'actual_call_status' => 'Active',
                    'call_direction'    => $liveAgent->call_direction,
                ];

                $liveCallLog = LiveCallLog::create($logData);

                LiveCall::updateOrCreate(
                    ['call_unique_id' => $liveAgent->uniqueid],
                    $logData
                );

                return $this->apiResponse->getResponseStructure(trans('message.success'), $liveCallLog, trans('message.success'))->original + ['neox_status' => true];
            } else {
                return $apiResult;
            }
        } catch (Exception $e) {
            generateLogs('error', "Error in handleAddParty: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
