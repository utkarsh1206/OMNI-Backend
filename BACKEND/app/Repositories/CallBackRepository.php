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
            $callBack = CallBack::create($request);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $callBack, trans('message.callback_create'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Storing CallBack : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
