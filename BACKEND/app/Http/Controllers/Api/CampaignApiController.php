<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignApiRequest;
use App\Http\Response\CustomApiResponse;
use Exception;

class CampaignApiController extends Controller
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function campaignListApi(CampaignApiRequest $request)
    {
        try {
            $domainName = $request->domain_name;
            if (!$domainName) {
                return response()->json(['error' => 'Domain name is required']);
            }

            $config = getNeoxConfig($domainName);
            if (isset($config['status']) && $config['status'] === 'error') {
                return response()->json($config);
            }

            $ip = $config['neox_local_ip'];

            $url = "https://{$ip}/api/admin/actions/v2/campaign_list.php";
            $validatedData = $request->validated();

            return sendHttpRequest($url, 'POST', $validatedData);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
