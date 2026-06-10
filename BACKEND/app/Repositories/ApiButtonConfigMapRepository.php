<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\ApiButtonConfigMapping;
use Exception;
use Illuminate\Support\Facades\DB;

class ApiButtonConfigMapRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function store(array $payload)
    {
        try {
            $buttonKey = $payload['button_key'];
            $mappings = $payload['mappings'] ?? [];

            DB::transaction(function () use ($buttonKey, $mappings) {
                ApiButtonConfigMapping::where('button_key', $buttonKey)->delete();

                foreach ($mappings as $mapping) {
                    ApiButtonConfigMapping::create([
                        'button_key' => $buttonKey,
                        'api_config_id' => $mapping['api_config_id'],
                        'display_name' => $mapping['display_name'] ?? null,
                        'sort_order' => $mapping['sort_order'] ?? 0,
                        'is_active' => $mapping['is_active'] ?? true,
                    ]);
                }
            });

            return $this->getByButton($buttonKey, trans('message.api_button_mapping_modified'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing api button config mapping: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getByButton(string $buttonKey, ?string $message = null)
    {
        try {
            $rows = ApiButtonConfigMapping::query()
                ->with('apiConfig:id,name,base_url,endpoint,method,status')
                ->where('button_key', $buttonKey)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                [
                    'button_key' => $buttonKey,
                    'count' => $rows->count(),
                    'mappings' => $rows,
                ],
                $message ?? trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching api button config mapping: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function clearByButton(string $buttonKey)
    {
        try {
            ApiButtonConfigMapping::where('button_key', $buttonKey)->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                null,
                trans('message.api_button_mapping_deleted')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting api button config mapping: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
