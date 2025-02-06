<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsProvider;
use App\Models\UserSmsGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\SmsProviderConfigurationResource;

class SmsProviderController extends Controller
{
    public function index()
    {
            $providers = SmsProvider::where("status","active")->get();
                return response()->json([
                    'data' => $providers,
                ]);    
    }

    public function saveUserProvider(Request $request)
    {
        try {
            $apiUser = auth()->user();
            $validator = Validator::make($request->all(), [
                'provider_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

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
       
            return response()->json([
                'message' => ' Sms Provider Configuration created successfully!',
                'data' => new SmsProviderConfigurationResource($smsProviers)
            ], 200);
        
        } catch (Exception $e) {
            Log::error('Error while generating the Sms configuration', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }
}
