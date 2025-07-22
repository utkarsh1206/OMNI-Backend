<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\RolePermission;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $moduleId
     * @param  int  $actionId
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $moduleId, $actionId)
    {
        $token = $request->bearerToken();
        // $payload = extractPayload($token);
        // if ($payload["admin_type"] == "Super") {
        //     return $next($request);
        // }
        $userInfo = auth()->user();
        $roleId = $userInfo?->user_role_id;
        if ($userInfo->usergroup_id == 0) {
           $roleId = setTenantData($userInfo);
        }
        
        if (!$roleId) {
            return response()->json(['error' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }
       
        $permission = Permission::where('module_id', $moduleId)
            ->where('action_id', $actionId)
            ->first();

        if (!$permission) {
            return response()->json(['error' => 'Permission not found.'], Response::HTTP_FORBIDDEN);
        }

        $hasPermission = RolePermission::where('role_id', $roleId)
            ->where('permission_id', $permission->id)
            ->exists();

        if (!$hasPermission) {
            return response()->json(['error' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        if ($userInfo->usergroup_id == 0) {
            getdbConnection($userInfo->domain, true);
        }

        return $next($request);
    }
}
