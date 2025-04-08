<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginApiRequest;
use App\Repositories\LoginApiRepository;
use App\Services\ApiRequestService;

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

        // Pass domain name to the common service to fetch configuration and process the request
        $result = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_login',
            LoginApiRequest::class,
            $domainName
        );

        // Check if the result is a JsonResponse
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            $result = $result->getData(true);
        }

        if (isset($result['code']) && $result['code'] === 0) {
            $connection = getDbConnection($domainName);

            $username = $request->user_id;
            $campaignId = $request->campaign_id;

            return $this->loginRepository->loginData($connection, $username, $campaignId);
        }
    }
}
