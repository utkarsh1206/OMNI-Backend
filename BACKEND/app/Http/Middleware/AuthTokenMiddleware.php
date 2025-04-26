<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuthToken;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;

class AuthTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            getDbConnection($request->id);
            $currentRoute =  request()->path();
            if (in_array($currentRoute, ["api/chatbot-room-id"])) {
                $header = $request->header('Token');
                if (!$header) {
                    return response()->json(['error' => 'Authorization token missing or invalid'], 401);
                }

                $authToken = AuthToken::where('token', $header)
                    ->where('expire_at', '>', Carbon::now())
                    ->first();

                if (!$authToken) {
                    return response()->json(['error' => 'Token invalid or expired'], ResponseHTTP::HTTP_UNAUTHORIZED);
                }
                $request->attributes->set('auth_token', $authToken);
            }

          
           
            return $next($request);
        } catch (Exception $e) {
            generateLogs('error', 'Access Token Middleware : ', $e->getMessage());
            return response()->json(['error' => 'Access Token Failed: ' . $e->getMessage()], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
