<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class JwtAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'Service unavailable.!'], Response::HTTP_SERVICE_UNAVAILABLE);
            }
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['message' => 'Token invalid.!'], Response::HTTP_UNAUTHORIZED);
            } else if ($e instanceof TokenExpiredException) {
                try {
                    JWTAuth::setToken(JWTAuth::refresh());
                    $user = JWTAuth::authenticate();
                } catch (JWTException $e) {
                    return response()->json(['message' => 'Token expired.!'], Response::HTTP_UNAUTHORIZED);
                }
            } elseif ($e->getPrevious() instanceof TokenBlacklistedException) {
                return response()->json(['message' => 'Token blacklisted.!'], Response::HTTP_UNAUTHORIZED);
            } else {
                return response()->json(['message' => "Unauthorised access.!"], Response::HTTP_UNAUTHORIZED);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token expired.!'], Response::HTTP_UNAUTHORIZED);
        }
        return $next($request);
    }
}
