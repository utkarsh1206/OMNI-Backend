<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SmsProviderConfigurationResource;
use App\Models\SmsProviderConfiguration;
use App\Models\SmsProvider;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SmsProviderConfigurationsController extends Controller
{
    public function index(Request $request)
    {
        $smsConfigurations = SmsProviderConfiguration::pluck("provider_id");
        $configurations  = SmsProvider::whereIn("id",$smsConfigurations)->get();
        return response()->json([
                'data' => $this->formatListingData($configurations),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'provider_name' => 'required',
                'configurations' => 'required|array',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $smsProviers = SmsProvider::Create([
                "name"=>$request->provider_name,
            ]);

            if(count($request->configurations) > 0)
            {
                foreach ($request->configurations as $config) {
                    foreach ($config as $key => $value) {
                       $smsConfigurations= SmsProviderConfiguration::create([
                            'provider_id' => $smsProviers->id ?? '',
                            'config_key' => $key, 
                            'config_value' => $value, 
                        ]);
                    }
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

    public function show($providerId)
    {
        $configurations = SmsProvider::where("id",$providerId)->get();
        return new SmsProviderConfigurationResource($this->formatListingData($configurations));
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
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
            

            if(count($request->configurations) > 0)
            {
                foreach ($request->configurations as $config) {
                    foreach ($config as $key => $value) {
                        $smsConfigurations->update([
                            'config_key' => $key, 
                            'config_value' => $value, 
                        ]);
                    }
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

    private function formatListingData($configurations)
    {
        $formattedData =  collect($configurations)->map(function ($item) {
            $providerInfo = SmsProviderConfiguration::select(["id","config_key","config_value"])->where("provider_id",$item['id'])->get();
             return [
                 'provider_name' => $item->name ?? '',
                 'configurations' => $providerInfo
             ];
         });

         return $formattedData;
    }
}
    