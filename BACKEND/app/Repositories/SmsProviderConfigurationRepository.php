<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\SmsProviderConfiguration;
use App\Models\SmsProvider;
use App\Http\Response\CustomApiResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class SmsProviderConfigurationRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $providerName = $request->input('provider_name', '');
        $method = $request->input('method', '');
        $enabled = $request->input('enabled', '');
        $search = $request->input('search', '');

        if ($method) {
            $smsConfigurations = SmsProviderConfiguration::where('config_value', $method);
        } elseif ($enabled) {
            $smsConfigurations = SmsProviderConfiguration::where('config_value', $enabled);
        } else {
            $smsConfigurations = SmsProviderConfiguration::query();
        }

        if ($search) {
            $smsConfigurations = $smsConfigurations->orWhere('config_value', 'like', '%' . $search . '%');
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
    }

    public function store($request)
    {
        try {
            $smsProviers = SmsProvider::Create([
                "name" => $request->provider_name,
            ]);

            if (count($request->configurations) > 0) {
                foreach ($request->configurations as $config) {
                    foreach ($config as $key => $value) {
                        $smsConfigurations = SmsProviderConfiguration::create([
                            'provider_id' => $smsProviers->id ?? '',
                            'config_key' => $key,
                            'config_value' => $value,
                        ]);
                    }
                }
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $smsConfigurations, trans('message.sms_provider_conf_add'));
        } catch (Exception $e) {
            Log::error('Error while generating the Sms configuration', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => trans('message.wrong_with_query'), 'error' => $e->getMessage()], 500);
        }
    }


    public function update($request, $providerId)
    {
        try {
            $smsConfigurations = SmsProviderConfiguration::where("provider_id", $providerId)->get();

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
            Log::error('Error while generating the cron configuration', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }


    public function delete($providerId)
    {
        try {
            $configuration = SmsProviderConfiguration::where("provider_id", $providerId)->get();

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
            Log::error('Error while generating the Sms configuration', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => trans("message.wrong_with_query"), 'error' => $e->getMessage()], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function show($providerId)
    {
        $configurations = SmsProvider::where("id", $providerId)->get();
        return $this->apiResponse->getResponseStructure(trans('message.success'), $this->formatListingData($configurations), trans('message.data_fetch'));
    }

    private function formatListingData($configurations, $method = null, $enabled = null)
    {
        $formattedData =  collect($configurations)->map(function ($item) use ($method, $enabled) {
            $providerInfo = SmsProviderConfiguration::select(["id", "config_key", "config_value"])->where("provider_id", $item['id']);
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
