<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginApiRequest;
use App\Repositories\LoginApiRepository;
use App\Services\ApiRequestService;
use App\Models\Detail;

class LoginApiController extends Controller
{
    private $apiRequestService;

    private $loginRepository;

    public function __construct(ApiRequestService $apiRequestService, LoginApiRepository $loginRepository)
    {
        $this->apiRequestService = $apiRequestService;
        $this->loginRepository = $loginRepository;
    }

    public function agentLoginApi(LoginApiRequest $request)
    {
        $domainName = $request->domain_name;
        if (!$domainName) {
            return response()->json(['error' => 'Domain name is required']);
        }

        #NOC - 955, 966 Hosted Change Start
        if (config('omni.IS_DC_MULTITENANT') == 1) {
            establishMasterConnection($domainName, "omnichannel", "mysql");
            $details = Detail::where('domain', $domainName)->first();
            $connection   = str_replace(".", "_", $domainName);
            establishDatabaseConnect($connection, $connection);

            if (!$details || !$details->uin) {
                return response()->json(['error' => 'Authentication token (UIN) not found'], 404);
            }
            $token = $details->uin;
            $ip = rtrim(config('omni.HOSTED_URL'), '/');
            $lowerDomain = strtolower($domainName);

            $url = "{$ip}/api/agent/actions/v2/agent_login.php?domain={$lowerDomain}";

            $postData = [
                "token" => $token,
                "user_id" => $request->user_id . "@" . $lowerDomain,
                "password" => $request->password,
                "campaign_id" => $request->campaign_id,
                "user_phone_number" => $request->user_phone_number,
            ];

            $result = sendHttpRequest($url, 'POST', $postData);

            generateLogs("info", "Got the API Response: " . json_encode($result));
        }   #NOC - 955, 966 Hosted Change END
        else {
            // Pass domain name to the common service to fetch configuration and process the request
            $result = $this->apiRequestService->handleApiRequest(
                $request,
                'agent_login',
                LoginApiRequest::class,
                $domainName
            );
        }

        // Check if the result is a JsonResponse
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            $result = $result->getData(true);
        }

        if (isset($result['code']) && $result['code'] === 0) {
            $connection = getDbConnection($domainName);

            $username = $request->user_id;
            $campaignId = $request->campaign_id;

            return $this->loginRepository->loginData($connection, $username, $campaignId);
        } else {
            return response()->json($result);
        }
    }
}
