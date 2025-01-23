<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $logData = [
                    'user_id' => Auth::check() ? Auth::id() : 1,  
                    'action' => $request->method() . ' ' . $request->path(), 
                    'ipaddress' => $request->ip(),  
                    'request_params' => json_encode($request->all()),  
                    'request_method' => $request->method(),  
                    'response_params' => json_encode($response->getContent()), 
            ];   
        ActivityLog::create($logData);
        return $response;
    }
}
