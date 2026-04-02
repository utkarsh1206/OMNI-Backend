<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $payload = $token ? extractPayload($token) : null;

        if ($payload['role_id'] == 1 || $payload['role_id'] == 2) { // To-Do If Manager then the request parameter should be blank.
            $request->merge($modifiedData);
            return $next($request);
        }

        if (empty($request->campaign_id) && isset($payload['campaign'])) {
            $modifiedData['campaign_id'] = $payload['campaign'];
        }

        // $modifiedData['user_id'] = $payload['username'];
        if (empty($request->user_id) && isset($payload['username'])) {
            // NOC-996, TODO DC Hosted, START
            if (config('omni.IS_DC_MULTITENANT') == 1) {
                $modifiedData['user_id'] = $payload['username'] . '@' . $payload['domain'];
            } else {
                $modifiedData['user_id'] = $payload['username'];
            }
            // NOC-996, END
        }

        if (empty($request->id) && isset($payload['id'])) {
            $modifiedData['id'] = $payload['id'];
        }

        if (empty($request->user) && isset($payload['username'])) {
            $modifiedData['user'] = $payload['username'];
        }

        if (empty($request->domain_name) && isset($payload['domain'])) {
            $modifiedData['domain_name'] = $payload['domain'];
        }

        if (empty($request->phone) && isset($payload['phone'])) {
            $modifiedData['phone'] = $payload['phone'];
        }

        // $modifiedData['user'] = $payload['username'];
        // $modifiedData['campaign_id'] = $payload['campaign'];
        // $modifiedData['domain_name'] = $payload['domain'];
        // $modifiedData['id'] = $payload['id'];
        // $modifiedData['phone'] = $payload['phone'];

        // Replace the original request with modified data
        $request->merge($modifiedData);

        return $next($request);
    }
}
