<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginApiRequest;

class LoginApiController extends Controller
{
    public function agentLoginApi(LoginApiRequest $request)
    {
        $ip = getNeoxLocalIp();
        if (is_array($ip) && isset($ip['status']) && $ip['status'] === 'error') {
            return $ip;
        }

        $url = "https://{$ip}/api/agent/actions/v2/agent_login.php";

        if ($response = validateSecretKey($request->input('token'))) {
            return $response;
        }
        
        $validatedData = $request->validated();
        $response = sendHttpRequest($url, 'POST', $validatedData);

        return $response;
    }
}
