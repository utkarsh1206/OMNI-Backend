<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\CallBack;
use App\Http\Response\CustomApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class CallBackRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function store($request)
    {
        try {
            $payload = extractPayload($request->bearerToken());
            $input = [
                'lead_id' => $request->lead_id,
                'list_id' => $request->list_id,
                'campaign_id' => $payload['campaign'],
                'user_id' => auth()->user()->id,
                'call_back_time' => $request->call_back_time,
                'recipient' => $request->recipient,
                'comments' => $request->comments,
                'user_group' => $request->user_group,
                'lead_status' => 'active',
                'last_modified_user' => $request->last_modified_user,
            ];
            $callBack = CallBack::create($input);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $callBack, trans('message.callback_create'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Storing CallBack : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
