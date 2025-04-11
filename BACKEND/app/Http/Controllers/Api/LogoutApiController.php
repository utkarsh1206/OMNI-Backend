<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoutApiRequest;
use App\Http\Response\CustomApiResponse;
use App\Services\ApiRequestService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LogoutApiController extends Controller
{
    private $apiResponse;
    private $apiRequestService;

    public function __construct(CustomApiResponse $customApiResponse, ApiRequestService $apiRequestService)
    {
        $this->apiResponse = $customApiResponse;
        $this->apiRequestService = $apiRequestService;
    }

    public function agentLogoutApi(Request $request)
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
                return (response()->json($result))->original + ['neox_status' => true];
            }

            return $result;
        } catch (Exception $e) {
            generateLogs('error', 'Logout processing failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
