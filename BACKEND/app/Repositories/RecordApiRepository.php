<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\LiveAgents;
use App\Models\LiveCall;
use App\Models\LiveCallLog;
use Exception;

class RecordApiRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function handleCallRecording($apiResult, $request)
    {
        try {
            if (isset($apiResult['code']) && $apiResult['code'] === 0) {
                $apiResult['status'] = $request->action;
                return $this->apiResponse->getResponseStructure(trans('message.success'), $apiResult, trans('message.success'))->original + ['neox_status' => true];
            } else {
                return $apiResult;
            }
        } catch (Exception $e) {
            generateLogs('error', "Error in handleCallRecording: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
