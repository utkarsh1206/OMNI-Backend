<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\SmsProvider;
use Exception;

class SmsProviderRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {
        try {
            $providers = SmsProvider::where("status", "active")->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $providers, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Sms Provider List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

   
}
