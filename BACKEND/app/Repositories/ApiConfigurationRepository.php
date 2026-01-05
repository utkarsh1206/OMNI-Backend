<?php

namespace App\Repositories;

use App\Enums\ProviderType;
use Illuminate\Http\Request;
use App\Models\ApiConfiguration;
use App\Models\SmsProvider;
use App\Http\Response\CustomApiResponse;
use App\Models\ApiConfigurationType;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Detail;
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
            $apiConfigId = $request->input('type', '');

            $configurations = SmsProvider::query();

            // filter by provider name
            if ($providerName) {
                $configurations->where('name', 'like', '%' . $providerName . '%');
            }

            // filter: provider must have config_key=method & config_value=<method>
            if ($method !== '') {
                $configurations->whereHas('configurations', function ($q) use ($method) {
                    $q->where('config_key', 'method')
                        ->where('config_value', $method);
                });
            }

            // filter: provider must have config_key=enabled & config_value=<enabled>
            if ($enabled !== '') {
                $configurations->whereHas('configurations', function ($q) use ($enabled) {
                    $q->where('config_key', 'enabled')
                        ->where('config_value', $enabled);
                });
            }

            // Filter by api_config_type_id
            if ($apiConfigId !== '') {
                $configurations->whereHas('configurations', function ($q) use ($apiConfigId) {
                    $q->where('api_config_type_id', $apiConfigId);
                });
            }

            // Search across provider name OR config_value
            if ($search) {
                $configurations->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhereHas('configurations', function ($qc) use ($search) {
                            $qc->where('config_value', 'like', "%$search%");
                        });
                });
            }

            $result = $configurations->get();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $this->formatListingData($result, $method, $enabled),
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in SMS Configuration List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function store($request)
    {
        try {
            $smsProviers = SmsProvider::Create([
                "name" => $request->provider_name,
                "type" => $request->api_config_type_id,
            ]);

            $requestConfig = $request->configurations;
            if ($request->api_config_type_id == ProviderType::CHATBOT->value) {
                establishMasterConnection(auth()->user()->domain, "omnichannel", "mysql");
                $detailsInfo = Detail::where("domain", auth()->user()->domain)->first();
                $uid = $detailsInfo->uin;
                $webhookUrl = url("api/fetch-session-messages/" . $uid);
                $requestConfig[0]['web_hook_url'] = $webhookUrl;
                $webhookUrl = url("api/chatbot-room-id/");
                $requestConfig[0]['web_hook_auth_url'] = $webhookUrl;
                getDbConnection($detailsInfo->domain, true);
            }

            if (count($requestConfig) > 0) {
                foreach ($requestConfig as $config) {
                    foreach ($config as $key => $value) {
                        if ($key === 'enabled') {
                            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                        }
                        $smsConfigurations = ApiConfiguration::create([
                            'provider_id' => $smsProviers->id ?? 0,
                            'config_key' => $key,
                            'config_value' => $value,
                            'api_config_type_id' => $request->api_config_type_id,
                        ]);
                    }
                }
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $smsConfigurations,
                trans('message.sms_provider_conf_add')
            );
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

            if ($request->has('api_config_type_id')) {
                foreach ($smsConfigurations as $smsConfiguration) {
                    $smsConfiguration->api_config_type_id = $request->api_config_type_id;
                    $smsConfiguration->save();
                }
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

            if ($request->has('provider_name')) {
                $provider = SmsProvider::find($providerId);
                if ($provider) {
                    $provider->name = $request->provider_name;
                    $provider->save();
                }
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $smsConfigurations,
                trans('message.sms_provider_conf_update')
            );
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
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $configuration,
                trans('message.sms_provider_conf_delete')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Deleting SMS Configuration : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($providerId)
    {
        try {
            $configurations = SmsProvider::where("id", $providerId)->get();
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $this->formatListingData($configurations),
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Showing SMS Configuration : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function formatListingData($configurations, $method = null, $enabled = null)
    {
        $formattedData =  collect($configurations)->map(function ($item) use ($method, $enabled) {
            $providerInfo = ApiConfiguration::select(["id", "config_key", "config_value", "api_config_type_id"])
                ->where(["provider_id" => $item['id']]);
            if (request()->api_config_type_id) {
                $providerInfo->where(["api_config_type_id" => request()->api_config_type_id]);
            }
            $providerInfo = $providerInfo->get();
            $typeArr = [];
            $apiConfigTypes = ApiConfigurationType::pluck("name", "id");
            if ($apiConfigTypes->count()) {
                $typeArr = $apiConfigTypes->toArray();
            }
            return [
                'provider_type' =>  $typeArr[$item->type] ?? '',
                'provider_id' => $item->id ?? '',
                'provider_name' => $item->name ?? '',
                'configurations' => $providerInfo
            ];
        });
        return $formattedData;
    }
}
