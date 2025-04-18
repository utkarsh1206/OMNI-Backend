<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\ApiConfiguration;
use App\Models\SmsProvider;
use App\Models\ChatBotProvider;
use App\Http\Response\CustomApiResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class ApiConfigurationRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $providerName = $request->input('provider_name', '');
            $method = $request->input('method', '');
            $enabled = $request->input('enabled', '');
            $search = $request->input('search', '');
            $apiConfigId = $request->input('api_config_type_id', '');

            if ($method) {
                $smsConfigurations = ApiConfiguration::where('config_value', $method);
            } elseif ($enabled) {
                $smsConfigurations = ApiConfiguration::where('config_value', $enabled);
            } else {
                $smsConfigurations = ApiConfiguration::query();
            }

            if ($search) {
                $smsConfigurations = $smsConfigurations->orWhere('config_value', 'like', '%' . $search . '%');
            }


            if ($apiConfigId) {
                $smsConfigurations = $smsConfigurations->where('api_config_type_id',$apiConfigId);
            }

            $smsConfigurations = $smsConfigurations->pluck("provider_id");
            $configurations  = SmsProvider::whereIn("id", $smsConfigurations);

            if ($providerName) {
                $configurations =  $configurations->where('name', '=', $providerName);
            }

            if ($search) {
                $configurations = $configurations->orWhere('name', 'like', '%' . $search . '%');
            }

            $configurations = $configurations->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $this->formatListingData($configurations, $method, $enabled), trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in SMS Configuration List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store($request)
    {
        try {
            if($request->api_config_type_id == 1)
            {
                $smsProviers = SmsProvider::Create([
                    "name" => $request->provider_name,
                ]);
    
            }

            $requestConfig = $request->configurations;
            if($request->api_config_type_id == 3)
            {
                $smsProviers = ChatBotProvider::Create([
                    "name" => $request->provider_name,
                ]);
                $webhookUrl = url('api/chatboot-webhook?source=' . $request->provider_name . "&tenant=".auth()->user()->domain?? '');
                $requestConfig[0]['web_hook_url'] = $webhookUrl;

                $webhookUrl = url('api/chatboot-webhook-auth?source=' . $request->provider_name . "&tenant=".auth()->user()->domain?? '');
                $requestConfig[0]['web_hook_auth_url'] = $webhookUrl;
            }
           

            if (count($requestConfig) > 0) {
                foreach ($requestConfig as $config) {
                    foreach ($config as $key => $value) {
                        $smsConfigurations = ApiConfiguration::create([
                            'provider_id' => $smsProviers->id ?? 0,
                            'config_key' => $key,
                            'config_value' => $value,
                            'api_config_type_id'=>$request->api_config_type_id,
                        ]);
                    }
                }
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $smsConfigurations, trans('message.sms_provider_conf_add'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Storing API Configuration : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update($request, $providerId)
    {
        try {
            $smsConfigurations = ApiConfiguration::where("provider_id", $providerId)->get();

            if (!$smsConfigurations) {
                return response()->json([
                    'message' => trans("message.sms_provider_conf_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }


            if (count($request->configurations) > 0) {
                foreach ($request->configurations as $config) {
                    foreach ($config as $key => $value) {
                        foreach ($smsConfigurations as $smsConfiguration) {
                            if ($smsConfiguration->config_key == $key) {
                                $smsConfiguration->config_value = $value;
                                $smsConfiguration->save();
                            }
                        }
                    }
                }
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $smsConfigurations, trans('message.sms_provider_conf_update'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Updating SMS Configuration : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($providerId)
    {
        try {
            $configuration = ApiConfiguration::where("provider_id", $providerId)->get();

            if (!$configuration) {
                return response()->json([
                    'message' => trans("message.sms_provider_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            foreach ($configuration as $smsConfiguration) {
                $smsConfiguration->delete();
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $configuration, trans('message.sms_provider_conf_delete'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Deleting SMS Configuration : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($providerId)
    {
        try {
            $configurations = SmsProvider::where("id", $providerId)->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $this->formatListingData($configurations), trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Showing SMS Configuration : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function formatListingData($configurations, $method = null, $enabled = null)
    {
        $formattedData =  collect($configurations)->map(function ($item) use ($method, $enabled) {
            $providerInfo = ApiConfiguration::select(["id", "config_key", "config_value","api_config_type_id"])->where(["provider_id"=> $item['id'],"api_config_type_id"=>request()->api_config_type_id]);
            $providerInfo = $providerInfo->get();

            return [
                'provider_id' => $item->id ?? '',
                'provider_name' => $item->name ?? '',
                'configurations' => $providerInfo
            ];
        });
        return $formattedData;
    }
}
