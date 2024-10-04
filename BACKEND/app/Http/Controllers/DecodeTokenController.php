<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class DecodeTokenController extends Controller
{
    public function decodeToken(Request $request)
    {
        // Retrieve the token from the request header
        $token = $request->header('Authorization');

        // Remove the "Bearer " part of the token if it exists
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        try {
            // Decode the token
            $payload = JWTAuth::setToken($token)->getPayload();

            // Return the decoded payload
            return response()->json([
                'status' => 'success',
                'payload' => $payload
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token'
            ], 401);
        }
    }
}

