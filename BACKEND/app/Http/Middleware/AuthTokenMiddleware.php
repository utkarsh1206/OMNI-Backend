<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuthToken;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class AuthTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['error' => 'Authorization token missing or invalid'], ResponseHTTP::HTTP_UNAUTHORIZED);
        }

        $token = substr($header, 7);
        $authToken = AuthToken::where('token', $token)
            ->where('expire_at', '>', Carbon::now())
            ->first();
        if (!$authToken) {
            return response()->json(['error' => 'Token invalid or expired'], ResponseHTTP::HTTP_UNAUTHORIZED);
        }
        $request->attributes->set('auth_token', $authToken);
        return $next($request);
    }
}
