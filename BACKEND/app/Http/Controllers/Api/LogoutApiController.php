<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoutApiRequest;
use Illuminate\Http\Request;

class LogoutApiController extends Controller
{
    public function agentLogoutApi(LogoutApiRequest $request) 
    {
        $ip = getNeoxLocalIp();
        if (is_array($ip) && isset($ip['status']) && $ip['status'] === 'error') {
            return $ip;
        }

        $url = "https://{$ip}/api/agent/actions/v2/agent_logout.php";

        if ($response = validateSecretKey($request->input('token'))) {
            return $response;
        }
        
        $validatedData = $request->validated();

        $response = sendHttpRequest($url, 'POST', $validatedData);

        return $response;
    }
}
