<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Admins;
use App\Models\Users;
use App\Models\Phones;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\LicenseController;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    protected $licenseController;

    public function __construct(LicenseController $licenseController)
    {
        $this->licenseController = $licenseController;  // Inject LicenseController
    }

    public function login_action(Request $request)
    {
        $validator = $request->validate([
            'username' => 'required',
            'password' => 'required',
            'flag' => 'required|in:0,1,2',
            'phone' => 'required_if:flag,2', // Phone is required if flag is 2
            'phone_password' => 'required_if:flag,2' // Phonepassword is required if flag is 2
        ]);

        $credentials = request(['password']);
        // Determine whether the input is an email or username
        $loginInput = $request->input('username');

        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $loginInput;
        } else {
            $credentials['username'] = $loginInput;
        }

        $userData = null;
        if ($validator['flag'] == 0) {
            try {
                if (isset($credentials['email'])) {
                    $userData = Admins::where('email', $credentials['email'])
                        ->where('is_delete', 'No')
                        ->first();
                } else {
                    $userData = Admins::where('username', $credentials['username'])
                        ->where('is_delete', 'No')
                        ->first();
                }

                if (!$userData) {
                    return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
                }

                if (!$token = auth('api')->attempt($credentials)) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            } catch (\Illuminate\Database\QueryException $ex) {
                Log::error('Database error: ' . $ex->getMessage());
                return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
            }
        } elseif ($validator['flag'] == 1) {
            try {
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

                    if (!$userData) {
                        return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
                    }
                } else {
                    return response()->json(['error' => 'Domain is required'], 400);
                }

                // Check if the license is expired for this user
                if ($this->isLicenseExpired($request)) {
                    return response()->json(['error' => 'License expired'], 403);
                }

                if (!$token = auth('api_users')->attempt($credentials)) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            } catch (\Illuminate\Database\QueryException $ex) {
                Log::error('Database error: ' . $ex->getMessage());
                return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
            }
        } elseif ($validator['flag'] == 2) {
            try {
                $domain = $request->input('domain');
                if ($domain) {
                    if (isset($credentials['email'])) {
                        $userData = Users::where('email', $credentials['email'])
                            ->where('is_delete', 'No')
                            ->where('role_id', 3)
                            ->first();
                    } else {
                        $userData = Users::where('username', $credentials['username'])
                            ->where('is_delete', 'No')
                            ->where('role_id', 3)
                            ->first();
                    }

                    $phoneData = Phones::where('login', $request->input('phone'))
                        ->where('status', 'ACTIVE')
                        ->first();

                    if (!$userData) {
                        Log::error('User not found or deleted', ['credentials' => $credentials]);
                        return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
                    }

                    if (!$phoneData) {
                        Log::error('Phone not found or inactive', ['phone' => $request->input('phone')]);
                        return response()->json(['error' => 'Unauthorized: Invalid phone credentials'], 401);
                    }

                    Log::info('Phone data retrieved:', ['phoneData' => $phoneData]);

                    // If the user is found, check both password and phone_password
                    // if ($userData && Hash::check($credentials['password'], $userData->password) && Hash::check($request->input('phone_password'), $userData->phone_password)) {
                    if ($userData && Hash::check($credentials['password'], $userData->password) && $request->input('phone_password') == $phoneData->pass) {

                        Log::info('Both password and phone password checks passed');
                        // Set the flag to include phone in JWT
                        $userData->setAddPhoneToJWT($validator['flag']);
                       // $token = auth('api_users')->login($userData);
                        $token = JWTAuth::attempt($credentials);
                    } else {
                        Log::error('Unauthorized: Invalid credentials', ['userData' => $userData]);
                        return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
                    }
                } else {
                    return response()->json(['error' => 'Domain is required'], 400);
                }

                // Check if the license is expired for this user
                if ($this->isLicenseExpired($request)) {
                    return response()->json(['error' => 'License expired'], 403);
                }
            } catch (\Illuminate\Database\QueryException $ex) {
                Log::error('Database error: ' . $ex->getMessage());
                return response()->json(['error' => 'Unauthorized: Invalid credentials'], 401);
            }
        }

        if ($userData && Hash::check($credentials['password'], $userData->password)) {
            Log::error('Passwords match, proceed with token generation');
        } else {
            Log::error('Invalid credentials');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User Login successful',
            'token' => $token
        ], 200);
    }

    private function isLicenseExpired(Request $request)
    {
        $is_tanant = false;
        $licenseResponse = $this->licenseController->decodeLicense(new Request(['is_tanant' => $is_tanant]));
        $licenseInfo = $licenseResponse->getData(true);

        if (isset($licenseInfo['validity_date'])) {
            if ($licenseInfo['validity_date'] <= time()) {
                Log::info('License expired');
                return true;
            }
        } else {
            return response()->json(['error' => 'License data invalid'], 400);
        }



        return false;
    }
}
