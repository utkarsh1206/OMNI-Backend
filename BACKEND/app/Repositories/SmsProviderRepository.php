<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\UserSmsGateway;
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

    public function saveUserProvider($request)
    {
        $apiUser = auth()->user();
        try {
            $smsProviers = UserSmsGateway::updateOrCreate(
                [
                    'provider_id' => $request->provider_id,  // Condition for matching an existing record
                    'user_id' => $apiUser->id,  // Another condition for matching an existing record
                ],
                [
                    'provider_id' => $request->provider_id,  // Fields to be created or updated
                    'user_id' => $apiUser->id,  // Fields to be created or updated
                ]
            );

            return $this->apiResponse->getResponseStructure(trans('message.success'), $smsProviers, trans('message.provider_configurations_created'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Saving User SMS Provider : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
