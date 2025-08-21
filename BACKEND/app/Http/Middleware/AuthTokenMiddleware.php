<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuthToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
class AuthTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $protectedPrefixes = [
                'api/chatbot-room-id',
                // 'api/call-initiate',
                // 'api/call-connected',
                // 'api/call-hangup',
                // 'api/call-history',
                'api/dc-user-logout',
                'api/fetch-chatbot-templates',
            ];

            if (Str::startsWith(request()->path(), $protectedPrefixes)) {
                if (!$request->header('Token')) {
                    return response()->json(['error' =>  trans('message.token_missing')], ResponseHTTP::HTTP_UNAUTHORIZED);
                }
                $decrypted = Crypt::decryptString($request->header('Token'));
                $parts = explode('+', $decrypted);
            
                [$accessTokenValue, $domain] = $parts;

                getDbConnection($domain, true);
                
                $authToken = AuthToken::where('token', $accessTokenValue)
                    ->where('expire_at', '>', Carbon::now())
                    ->first();
                if (!$authToken) {
                    return response()->json(['error' =>  trans('message.token_expired')], ResponseHTTP::HTTP_UNAUTHORIZED);
                }
                $request->attributes->set('auth_token', $authToken);
                $request->attributes->set('tenant_domain', $domain);
            } else {
                $uinId = $request->route('uinId');
                getDbConnection($uinId->domain, true);
                $request->attributes->set('tenant_uin', $uinId->uin);
            }
            return $next($request);
        } catch (Exception $e) {
            generateLogs('error', 'Access Token Middleware : ', $e->getMessage());
            return response()->json(['error' => 'Access Token Failed: ' . $e->getMessage()], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
