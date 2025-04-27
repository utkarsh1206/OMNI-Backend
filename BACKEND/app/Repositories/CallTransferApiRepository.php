<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\LiveAgents;
use App\Models\TransferedCallLog;
use App\Models\Users;
use Exception;

class CallTransferApiRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function handleCallTransfer($apiResult, $request)
    {
        try {
            if (isset($apiResult['code']) && $apiResult['code'] === 0) {

                $liveAgent = $this->getLiveAgent($request->id, $request->campaign_id);

                if (!$liveAgent) {
                    return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.error'));
                }

                [$transferTo, $transferToType] = $this->determineTransferTarget($request);

                $logData = $this->buildTransferLogData($request, $liveAgent, $transferTo, $transferToType);

                $data = TransferedCallLog::create($logData);

                return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.success'))->original + ['neox_status' => true];
            } else {
                return $apiResult;
            }
        } catch (Exception $e) {
            generateLogs('error', 'Call transfer failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function getLiveAgent($userId, $campaignId)
    {
        return LiveAgents::where('user_id', $userId)
            ->where('campaign_id', $campaignId)
            ->first();
    }

    private function determineTransferTarget($request)
    {
        $callRoute = $request->callroute;
        $callRouteData = $request->callroutedata;
        $user = Users::where('username', $request->transfer_target_user_id)->first();

        $transferTo = null;
        $transferToType = null;

        if ($callRoute === 'IN_GROUP') {
            if ($callRouteData === 'AGENTDIRECT') {
                $transferTo = optional($user)->id;
                $transferToType = 'AGENTDIRECT';
            } else {
                $transferTo = $callRouteData;
                $transferToType = 'INGROUPS';
            }
        }

        if ($request->filled('transfer_target_phone_number')) {
            $transferTo = $request->transfer_target_phone_number;
            $transferToType = null;
        }

        return [$transferTo, $transferToType];
    }

    private function buildTransferLogData($request, $liveAgent, $transferTo, $transferToType)
    {
        $type = 'INTERNAL';
        if ($request->filled('transfer_target_phone_number')) {
            $type = "EXTERNAL";
        }
        return [
            'user_id'           => $request->id,
            'campaign_id'       => $request->campaign_id,
            'call_unique_id'    => $liveAgent->uniqueid,
            'mobile_number'     => $liveAgent->customer,
            'call_date'         => $liveAgent->call_date,
            'transfer_from'     => $request->id,
            'transfer_to'       => $transferTo,
            'transfer_to_type'  => $transferToType,
            'call_route_data'   => $request->call_route_data,
            'transfer_type'     => $type,
            'call_direction'    => $liveAgent->call_direction,
        ];
    }
}
