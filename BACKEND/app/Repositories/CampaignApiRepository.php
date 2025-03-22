<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use Exception;
use App\Http\Response\CustomApiResponse;
use App\Models\NeoxConfiguration;

class CampaignApiRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function getCampaignList($data)
    {
        $neoxLocalIp = NeoxConfiguration::first()->neox_local_ip ?? null;

        if (!$neoxLocalIp) {
            return $this->apiResponse->getResponseStructure(trans('message.error'), $neoxLocalIp, trans('message.local_ip_not_found'));
        }

        $url = "https://$neoxLocalIp/api/admin/actions/v2/campaign_list.php";

        try {
            // First attempt with SSL verification
            $response = $this->makeRequest($url, $data);

            // If the response fails, retry without SSL verification
            if (!$response->successful()) {
                $response = $this->makeRequest($url, $data, true);
            }

            return $this->formatResponse($response);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function makeRequest($url, array $data, bool $withoutVerifying = false)
    {
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
            ])
                ->post($url, $data);

            return $response;
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function formatResponse($response): array
    {
        if ($response && $response->successful()) {
            $data = $response->json()['data'] ?? null;

            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.data_fetch'));
        }

        return $this->apiResponse->getResponseStructure(trans('message.error'),null,trans('message.failed_to_fetch_data'));
    }
}
