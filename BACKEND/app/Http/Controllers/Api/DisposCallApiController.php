<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisposCallApiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Http\Response\CustomApiResponse;

class DisposCallApiController extends Controller
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function disposCallApi(DisposCallApiRequest $request)
    {
        try {
            $ip = getNeoxLocalIp();
            if (is_array($ip) && isset($ip['status']) && $ip['status'] === 'error') {
                return $ip;
            }

            $url = "https://{$ip}/api/agent/actions/v2/agent_dispo_call.php";

            if ($response = validateSecretKey($request->input('token'))) {
                return $response;
            }

            $validatedData = $request->validated();
            return sendHttpRequest($url, 'POST', $validatedData);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
