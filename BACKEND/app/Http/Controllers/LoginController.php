<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admins;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login_action(Request $request)
    {
        $validator = $request->validate([
            'username' => 'required',
            'password' => 'required',
            'flag' => 'required',
            'phone' => 'required_if:flag,2', // Phone is required if flag is 2
            'phone_password' => 'required_if:flag,2' // phone_password is required if flag is 2
        ]);

        $credentials = request(['password']);
        // Determine whether the input is an email or username
        $loginInput = $request->input('username');

        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $loginInput;
        } else {
            $credentials['username'] = $loginInput;
        }

        if ($validator['flag'] == 0) {
            if (isset($credentials['email'])) {
                $userData = Admins::where('email', $credentials['email'])
                    ->where('is_delete', 'No')
                    ->first();
            } else {
                $userData = Admins::where('username', $credentials['username'])
                    ->where('is_delete', 'No')
                    ->first();
            }

            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } elseif ($validator['flag'] == 1) {
            $domain = $request->input('domain');

            if ($domain) {
                if (isset($credentials['email'])) {
                    $userData = Users::where('email', $credentials['email'])
                        ->where('is_delete', 'No')
                        ->first();
                } else {
                    $userData = Users::where('username', $credentials['username'])
                        ->where('is_delete', 'No')
                        ->first();
                }
            } else {
                return response()->json(['error' => 'Domain is required'], 400);
            }

            if (!$token = auth('api_users')->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } elseif ($validator['flag'] == 2) {
            $domain = $request->input('domain');

            if ($domain) {
                if (isset($credentials['email'])) {
                    $userData = Users::where('email', $credentials['email'])
                        ->where('is_delete', 'No')
                        ->where('phone', $request->input('phone'))
                        ->first();
                } else {
                    $userData = Users::where('username', $credentials['username'])
                        ->where('is_delete', 'No')
                        ->where('phone', $request->input('phone'))
                        ->first();
                }
                Log::info('User data', ['userData' => $userData]);

                // If the user is found, check both password and phone_password
                if ($userData && Hash::check($credentials['password'], $userData->password) && Hash::check($request->input('phone_password'), $userData->phone_password)) {
                    Log::info('Both password and phone password checks passed');
                    // Set the flag to include phone in JWT
                    $userData->setAddPhoneToJWT($validator['flag']);

                    $token = auth('api_users')->login($userData);
                } else {
                    Log::error('Unauthorized: Invalid credentials', ['userData' => $userData]);
                    return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
                }
            } else {
                return response()->json(['error' => 'Domain is required'], 400);
            }
        }

        if ($userData && Hash::check($credentials['password'], $userData->password)) {
            Log::error('Passwords match, proceed with token generation');
        } else {
            Log::error('Invalid credentials');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token
        ], 200);
    }
}
