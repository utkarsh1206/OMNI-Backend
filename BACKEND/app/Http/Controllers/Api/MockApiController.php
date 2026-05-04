<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MockApiController extends Controller
{
    /**
     * GET /api/mock/basic-auth
     */
    public function basicAuth(Request $request)
    {
        $user = $request->getUser();
        $pass = $request->getPassword();

        if ($user === 'admin' && $pass === 'password123') {
            return response()->json([
                'status' => 'success',
                'message' => 'Authenticated via Basic Auth',
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'Unauthorized Basic Auth'], 401);
    }

    /**
     * GET /api/mock/api-key
     */
    public function apiKey(Request $request)
    {
        $keyName = $request->query('key_name', 'X-API-KEY');
        $expectedKey = 'secret-token-789';

        $providedKey = $request->header($keyName) 
            ?? $request->query($keyName) 
            ?? $request->input($keyName);

        if ($providedKey === $expectedKey) {
            return response()->json([
                'status' => 'success',
                'message' => 'Authenticated via API Key',
                'location' => $request->header($keyName) ? 'header' : ($request->query($keyName) ? 'query' : 'body')
            ]);
        }

        return response()->json(['message' => 'Invalid API Key'], 401);
    }

    /**
     * POST /api/mock/oauth/token
     */
    public function oauthToken(Request $request)
    {
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        // Simple validation
        if ($clientId === 'omni_app' && $clientSecret === 'omni_secret') {
            return response()->json([
                'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImlhdCI6MTUxNjIzOTAyMn0.HvWlHGRGr0VFjjCtQqEuCeAnxQPxo2rLp_AqZKddCjA',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'refresh_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsInJlZnJlc2giOnRydWUsImlhdCI6MTUxNjIzOTAyMn0.wW0zpTpga2Y5ln_TLCb0Xrn_6uaroRaIv2RP70oaZPs'
            ]);
        }

        return response()->json(['error' => 'invalid_client'], 400);
    }

    /**
     * GET /api/mock/oauth/resource
     */
    public function oauthResource(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if ($authHeader === 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImlhdCI6MTUxNjIzOTAyMn0.HvWlHGRGr0VFjjCtQqEuCeAnxQPxo2rLp_AqZKddCjA') {
            return response()->json([
                'status' => 'success',
                'message' => 'Authenticated via OAuth2 Bearer Token',
                'data' => [
                    'leads' => [
                        ['id' => 1, 'name' => 'Lead A'],
                        ['id' => 2, 'name' => 'Lead B']
                    ]
                ]
            ]);
        }

        return response()->json(['error' => 'unauthorized'], 401);
    }

    /**
     * POST /api/mock/echo
     */
    public function echo(Request $request)
    {
        return response()->json([
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'body' => $request->all(),
        ]);
    }
}
