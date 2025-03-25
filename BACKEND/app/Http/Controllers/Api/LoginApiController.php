<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginApiRequest;

class LoginApiController extends Controller
{
    public function agentLoginApi(LoginApiRequest $request)
    {
        $domainName = $request->domain_name;
        if (!$domainName) {
            return response()->json(['error' => 'Domain name is required']);
        }
        
        $config = getNeoxConfig($domainName);
        if (isset($config['status']) && $config['status'] === 'error') {
            return response()->json($config);
        }

        $ip = $config['neox_local_ip'] ?? null;
        $neoxToken = $config['neox_token'] ?? null;

        $url = "https://{$ip}/api/agent/actions/v2/agent_login.php";
        
        $requestData = array_merge($request->validated(), ['token' => $neoxToken]);

        return sendHttpRequest($url, 'POST', $requestData);
    }
}
