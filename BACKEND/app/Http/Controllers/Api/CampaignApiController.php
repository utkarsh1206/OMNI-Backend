<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CampaignApiRequest;

class CampaignApiController extends Controller
{
    public function campaignListApi(CampaignApiRequest $request)
    {
        $domainName = $request->domain_name;
        if (!$domainName){
            return response()->json(['error' => 'Domain name is required']);
        }
        
        $ip = getNeoxLocalIp($domainName);
        if (is_array($ip) && isset($ip['status']) && $ip['status'] === 'error') {
            return $ip;
        }

        $url = "https://{$ip}/api/admin/actions/v2/campaign_list.php";
        $validatedData = $request->validated();

        $response = sendHttpRequest($url, 'POST', $validatedData);

        return $response;
    }
}
