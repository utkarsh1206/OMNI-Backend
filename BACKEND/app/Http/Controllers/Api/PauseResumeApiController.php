<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PauseResumeApiRequest;

class PauseResumeApiController extends Controller
{
    public function pauseResumeApi(PauseResumeApiRequest $request)
    {
        $ip = getNeoxLocalIp();
        if (is_array($ip) && isset($ip['status']) && $ip['status'] === 'error') {
            return $ip;
        }
        $url = "https://{$ip}/api/agent/actions/v2/agent_pause_resume.php";

        if ($response = validateSecretKey($request->input('token'))) {
            return $response;
        }

        $validatedData = $request->validated();
        $response = sendHttpRequest($url, 'POST', $validatedData);

        return $response;
    }
}
