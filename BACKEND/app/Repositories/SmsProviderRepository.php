<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\UserSmsGateway;
use App\Models\SmsProvider;
use Exception;
use Illuminate\Support\Facades\Log;

class SmsProviderRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {
        $providers = SmsProvider::where("status", "active")->get();
        return $this->apiResponse->getResponseStructure(trans('message.success'), $providers, trans('message.data_fetch'));
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
            Log::error('Error while generating the Sms configuration', [
            'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }
}
