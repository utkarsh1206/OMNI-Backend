<?php

namespace App\Repositories;

use App\Exceptions\InvalidCredentialsException;
use Illuminate\Http\Request;
use App\Models\Admins;
use App\Models\Users;
use App\Models\Phones;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\LicenseController;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

/**
 * Class UserRepository.
 */


class LoginRepository
{

    protected $licenseController;

    private $apiResponse;

    private $loginRepository;

    public function __construct(CustomApiResponse $customApiResponse, LicenseController $licenseController)
    {
        $this->licenseController = $licenseController;
        $this->apiResponse = $customApiResponse;
    }
    /**
     * For Updating the record respective model in storage
     *
     * @throws InvalidCredentialsException
     */
    public function login(Request $request, array $validator)
    {

        $domain = $request->input('domain');
        if ($validator["flag"] == 1 || $validator["flag"] == 2) {
            if (isLicenseExpired($request)) {
               return response()->json(['error' => trans('message.license_expired')], ResponseHTTP::HTTP_FORBIDDEN);
            }

            if (!$domain) {
                return response()->json(['error' => trans('message.domain_required')], ResponseHTTP::HTTP_BAD_REQUEST);
            }
        }

        $credentials = request(['password']);
        // Determine whether the input is an email or username
        $loginInput = $request->input('username');
        $credentials[filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username'] = $loginInput;

        try {
            if ($validator['flag'] == 0) {
                $token = authenticateUser($credentials, Admins::class);
            } elseif ($validator['flag'] == 1) {
                $token = authenticateUser($credentials, Users::class, 'api_users');
            } elseif ($validator['flag'] == 2) {
                $userData = $this->getUserData($credentials);
                if (!$userData) {
                    Log::error('User not found or deleted', ['credentials' => $credentials]);
                    return response()->json(['error' =>  trans('message.invalid_credentials')], ResponseHTTP::HTTP_UNAUTHORIZED);
                }
                $phoneData = $this->getPhoneData($request);
                if (!$phoneData) {
                    Log::error('Phone not found or inactive', ['phone' => $request->input('phone')]);
                    return response()->json(['error' =>  trans('message.invalid_phone_credentials')],  ResponseHTTP::HTTP_UNAUTHORIZED);
                }
                Log::info('Phone data retrieved:', ['phoneData' => $phoneData]);
                if ($this->authenticatePassword($credentials, $userData, $request->input('phone_password'), $phoneData)) {
                    Log::info('Both password and phone password checks passed');
                    // Set the flag to include phone in JWT
                    $userData->setAddPhoneToJWT($validator['flag']);
                    $token = JWTAuth::attempt($credentials);
                } else {
                    Log::error('Unauthorized: Invalid credentials', ['userData' => $userData]);
                    return response()->json(['error' => trans('message.invalid_credentials')],  ResponseHTTP::HTTP_UNAUTHORIZED);
                }
            }
            
           
                return is_string($token) ? $this->apiResponse->getCustomResponse("success", "token", $token, trans('message.login')) : $token;

           
        } catch (\Exception $ex) {
            Log::error('Error during authentication: ' . $ex->getMessage());
            return response()->json(['error' => trans('message.invalid_credentials')],  ResponseHTTP::HTTP_UNAUTHORIZED);
        }
    }


    private function getUserData($credentials)
    {
        return Users::where(function ($query) use ($credentials) {
            if (isset($credentials['email'])) {
                $query->where('email', $credentials['email']);
            } else {
                $query->where('username', $credentials['username']);
            }
        })
            ->where('role_id', 3)
            ->first();
    }

    private function getPhoneData($request)
    {
        return Phones::where('login', $request->input('phone'))
            ->where('status', 'ACTIVE')
            ->first();
    }

    private function authenticatePassword($credentials, $userData, $phonePassword, $phoneData)
    {
        return Hash::check($credentials['password'], $userData->password) && $phonePassword == $phoneData->pass;
    }
}
