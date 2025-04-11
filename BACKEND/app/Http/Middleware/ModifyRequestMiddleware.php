<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class ModifyRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $modifiedData = $request->all();

        // $token = $request->bearerToken();
        $token = $request->bearerToken() ?: $request->auth_token;

        if (!$token) {
            // If no token is provided, return an error response
            $modifiedData['isJWT'] = 'No';
            $request->merge($modifiedData);

            return $next($request);
        }

        $payload = $token ? JWTAuth::setToken($token)->getPayload() : null;

        $modifiedData['user_id'] = $payload['username'];
        $modifiedData['user'] = $payload['username'];
        $modifiedData['campaign_id'] = $payload['campaign'];
        $modifiedData['domain_name'] = $payload['domain'];
        $modifiedData['id'] = $payload['id'];
        $modifiedData['phone'] = $payload['phone'];

        // Replace the original request with modified data
        $request->merge($modifiedData);

        return $next($request);
    }
}
