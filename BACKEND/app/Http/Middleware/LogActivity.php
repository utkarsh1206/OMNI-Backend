<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\SubModule;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // ❌ Skip logging GET requests
        if ($request->method() === 'GET') {
            return $response;
        }

        $method = $request->method();
        $path = $request->path();
        $actionType = $this->determineActionType($method, $path);
        $logData = [
            'user_id' => Auth::check() ? Auth::id() : 1,
            'action' => $path,
            'module_name' => $this->getModuleName($method . " " . $path),
            'ipaddress' => $request->ip(),
            'request_params' => json_encode($request->all()),
            'request_method' => $method,
            'action_type' => $actionType
            // 'response_params' => json_encode($response->getContent()),
        ];
        ActivityLog::create($logData);
        return $response;
    }

    public function getModuleName($path)
    {
        $normalizedPath = preg_replace('/\/\d+/', '/{id}', $path);
        $subModule = SubModule::where("name", $normalizedPath)->first();
        return $subModule->module->name ?? NULL;
    }

    protected function determineActionType($method, $path)
    {
        $path = strtolower($path);

        switch ($method) {
            case 'GET':
                if (preg_match('/\/create$/', $path)) {
                    return 'Create';
                } elseif (preg_match('/\/\d+\/edit$/', $path)) {
                    return 'Edit';
                } elseif (preg_match('/\/\d+$/', $path)) {
                    return 'View';
                } else {
                    return 'List';
                }

            case 'POST':
                return 'Create';

            case 'PUT':
            case 'PATCH':
                return 'Update';

            case 'DELETE':
                return 'Delete';

            default:
                return 'Other';
        }
    }
}
