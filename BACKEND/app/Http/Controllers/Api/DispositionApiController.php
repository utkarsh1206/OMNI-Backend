<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DispositionListRequest;

class DispositionApiController extends Controller
{
    public function dispositionListApi(DispositionListRequest $request)
    {
        $ip = getNeoxLocalIp();
        if (is_array($ip) && isset($ip['status']) && $ip['status'] === 'error') {
            return $ip;
        }

        $url = "https://{$ip}/api/agent/actions/v2/disposition_list.php";

        $validatedData = $request->validated();
        $response = sendHttpRequest($url, 'POST', $validatedData);

        return $response;
    }
}
