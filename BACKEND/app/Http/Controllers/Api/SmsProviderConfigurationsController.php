<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SmsProviderConfigurationResource;
use App\Models\SmsProviderConfiguration;
use Exception;
use Illuminate\Support\Facades\Validator;

class SmsProviderConfigurationsController extends Controller
{
   
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'provider_id' => 'required',
                'configurations' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
        

            if(count($request->configuration) > 0)
            {
                foreach($request->configuration as $key=>$value)
                {
                    $smsConfigurations = SmsProviderConfiguration::create([
                        'provider_id' => $request->provider_id,
                        'config_key' => $key,
                    ]);
                }
                
            }

            return response()->json([
                'message' => ' Sms Provider Configuration created successfully!',
                'data' => new SmsProviderConfigurationResource($smsConfigurations)
            ], 200);
        
        } catch (Exception $e) {
            Log::error('Error while generating the Sms configuration', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(SmsProviderConfiguration $cronConfiguration)
    {
        return new SmsProviderConfigurationResource($cronConfiguration);
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'provider_id' => 'required',
                'configurations' => 'required|array',
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $smsConfigurations = SmsProviderConfiguration::find($id);

            if (!$smsConfigurations) {
                return response()->json([
                    'message' => 'Sms Provider Configuration not found.',
                ], 404);
            }
            

            if(count($request->configuration) > 0)
            {
                foreach($request->configuration as $key=>$value)
                {
                    $smsConfigurations->update([
                        'provider_id' => $request->provider_id,
                        'config_key' => $key,
                    ]);
                }
                
            }

            return response()->json([
                'message' => 'Sms Provider Configuration updated successfully!',
                'data' => new SmsProviderConfigurationResource($smsConfigurations)
            ], 200);
        } catch (Exception $e) {
            Log::error('Error while generating the cron configuration', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $configuration = SmsProviderConfiguration::find($id);

            if (!$configuration) {
                return response()->json([
                    'message' => 'Sms Provider Configuration not found.',
                ], 404);
            }
            
            $configuration->delete();

            return response()->json([
                'message' => ' Sms Provider Configuration deleted successfully!',
                'data' => new SmsProviderConfigurationResource($configuration)
            ], 200);
        } catch (Exception $e) {
            Log::error('Error while generating the Sms configuration', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }
}
