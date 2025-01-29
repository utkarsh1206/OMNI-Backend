<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\Module;
use App\Models\SubModule;
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
                    'action' => $request->path(), 
                    'module_name'=> $this->getModuleName($request->method()." ".$request->path()),
                    'ipaddress' => $request->ip(),  
                    'request_params' => json_encode($request->all()),  
                    'request_method' => $request->method(),  
                    // 'response_params' => json_encode($response->getContent()), 
            ];   
        ActivityLog::create($logData);
        return $response;
    }

    public function getModuleName($path)
    {
        $normalizedPath = preg_replace('/\/\d+/', '/{id}', $path);
        $subModule = SubModule::where("name",$normalizedPath)->first();
        return $subModule->module->name ?? '';
    }
}
