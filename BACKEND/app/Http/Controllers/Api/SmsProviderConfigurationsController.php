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
        $searchTerm = $request->input('search', '');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $smsConfigurations = SmsProviderConfiguration::query();
        if (!empty($searchTerm)) {
            $smsConfigurations->where(function ($q) use ($searchTerm) {
                $q->where('config_key', 'like', '%' . $searchTerm . '%')
                    ->orwhere('config_value', 'like', '%' . $searchTerm . '%');
            });
        }
        
        if ($page === 'all') {
            $smsConfigurations = $smsConfigurations->get();
           
            return response()->json([
                'data' => $smsConfigurations,
            ]);
        } else {
            $cronConfiguration = $smsConfigurations->paginate($perPage);
            $formattedData =  collect($cronConfiguration->items())->map(function ($item) {
               $providerInfo = SmsProvider::find($item['provider_id']);
                unset($item['provider_id']);
                return [
                    'provider_name' => $providerInfo->name ?? '',
                    'configurations' => $item
                ];
            });
            return response()->json([
                'data' => $formattedData,
                'meta' => [
                    'current_page' => $cronConfiguration->currentPage(),
                    'last_page' => $cronConfiguration->lastPage(),
                    'per_page' => $cronConfiguration->perPage(),
                    'total' => $cronConfiguration->total(),
                ],
            ]);
        }
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

    public function show($id)
    {
        $smsConfigurations = SmsProviderConfiguration::find($id);
        return new SmsProviderConfigurationResource($smsConfigurations);
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
                            //'provider_id' => $request->provider_id,
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
}
    