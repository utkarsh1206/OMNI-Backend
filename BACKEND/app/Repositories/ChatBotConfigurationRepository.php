<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\ChatBotConfiguration;
use App\Models\ChatBotProvider;
use App\Http\Response\CustomApiResponse;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class ChatBotConfigurationRepository
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
            $chatBotConfigurations = ChatBotConfiguration::where('config_value', $method);
        } elseif ($enabled) {
            $chatBotConfigurations = ChatBotConfiguration::where('config_value', $enabled);
        } else {
            $chatBotConfigurations = ChatBotConfiguration::query();
        }

        if ($search) {
            $chatBotConfigurations = $chatBotConfigurations->orWhere('config_value', 'like', '%' . $search . '%');
        }

        $chatBotConfigurations = $chatBotConfigurations->pluck("provider_id");
        $configurations  = ChatBotProvider::whereIn("id", $chatBotConfigurations);

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
            $chatProviders = ChatBotProvider::Create([
                "name" => $request->provider_name,
            ]);

            if (count($request->configurations) > 0) {
                foreach ($request->configurations as $config) {
                    foreach ($config as $key => $value) {
                        $chatConfigurations = ChatBotConfiguration::create([
                            'chat_id' => $chatProviders->id ?? '',
                            'config_key' => $key,
                            'config_value' => $value,
                        ]);
                    }
                }
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $chatConfigurations, trans('message.chat_provider_conf_add'));
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
            $chatBotConfiguration = ChatBotConfiguration::where("provider_id", $providerId)->get();

            if (!$chatBotConfiguration) {
                return response()->json([
                    'message' => trans("message.chat_provider_conf_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }


            if (count($request->configurations) > 0) {
                foreach ($request->configurations as $config) {
                    foreach ($config as $key => $value) {
                        foreach ($chatBotConfiguration as $chatConfiguration) {
                            if ($chatConfiguration->config_key == $key) {
                                $chatConfiguration->config_value = $value;
                                $chatConfiguration->save();
                            }
                        }
                    }
                }
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $chatConfiguration, trans('message.sms_provider_conf_update'));
        } catch (Exception $e) {
            Log::error('Error while generating the ChatBot configuration', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }


    public function delete($chatId)
    {
        try {
            $configuration = ChatBotConfiguration::where("chat_id", $chatId)->get();

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
        $configurations = ChatBotProvider::where("id", $providerId)->get();
        return $this->apiResponse->getResponseStructure(trans('message.success'), $this->formatListingData($configurations), trans('message.data_fetch'));
    }

    private function formatListingData($configurations, $method = null, $enabled = null)
    {
        $formattedData =  collect($configurations)->map(function ($item) use ($method, $enabled) {
            $providerInfo = ChatBotConfiguration::select(["id", "config_key", "config_value"])->where("provider_id", $item['id']);
            $providerInfo = $providerInfo->get();

            return [
                'chat_id' => $item->id ?? '',
                'name' => $item->name ?? '',
                'configurations' => $providerInfo
            ];
        });

        return $formattedData;
    }
}
