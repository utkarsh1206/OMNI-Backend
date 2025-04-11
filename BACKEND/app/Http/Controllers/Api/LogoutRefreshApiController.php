<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoutApiRequest;
use App\Http\Response\CustomApiResponse;
use App\Models\LiveAgents;
use App\Services\ApiRequestService;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutRefreshApiController extends Controller
{
    private $apiResponse;
    private $apiRequestService;

    public function __construct(CustomApiResponse $customApiResponse, ApiRequestService $apiRequestService)
    {
        $this->apiResponse = $customApiResponse;
        $this->apiRequestService = $apiRequestService;
    }

    public function agentLogoutRefreshApi(Request $request)
    {
        try {
            $result = $this->apiRequestService->handleApiRequest(
                $request,
                'agent_logout',
                LogoutApiRequest::class,
                $request->domain_name
            );

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $result = $result->getData(true);
            }

            if (isset($result['code']) && $result['code'] === 0) {
                $this->omniLogout($request);
                return response()->json(array_merge($result, ['neox_status' => true]));
            }

            return $result;
        } catch (Exception $e) {
            generateLogs('error', 'Logout processing failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function omniLogout($request)
    {
        try {
            // Delete user from Live Agents Table
            $userId = auth()->id() ?? $request->id;

            $connection = getDbConnection($request->domain_name);

            $connection->table('live_agents')->where('user_id', $userId)->delete();

            // Invalidate the token (optional)
            $token = $request->bearerToken() ?: $request->auth_token;

            // Invalidate the token for the current session
            JWTAuth::setToken($token)->invalidate();
        } catch (Exception $e) {
            generateLogs('error', 'omniLogout processing failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
