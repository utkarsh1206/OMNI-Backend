<?php

namespace App\Repositories;

use App\Exceptions\InvalidCredentialsException;
use Illuminate\Http\Request;
use App\Models\Admins;
use App\Models\Users;
use App\Models\Phones;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\LicenseController;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Response\CustomApiResponse;
use App\Models\Phone;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

/**
 * Class UserRepository.
 */
class LoginRepository
{

    protected $licenseController;

    private $apiResponse;

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
              //  return response()->json(['error' => trans('message.license_expired')], ResponseHTTP::HTTP_FORBIDDEN);
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
                $userData = $this->getUserData($credentials, Admins::class, $validator['flag']);
            } elseif ($validator['flag'] == 1) {
                $token = authenticateUser($credentials, Users::class, 'api_users');
                $userData = $this->getUserData($credentials, Users::class, $validator['flag']);
            } elseif ($validator['flag'] == 2) {
                $userData = $this->getUserData($credentials, Users::class, $validator['flag']);
                if (!$userData) {
                    Log::error('User not found or deleted', ['credentials' => $credentials]);
                    return response()->json(['error' =>  trans('message.invalid_credentials')], ResponseHTTP::HTTP_UNAUTHORIZED);
                }
                $phoneData = $this->getPhoneData($request);
                if (!$phoneData) {
                    Log::error('Phone not found or inactive', ['phone' => $request->input('phone')]);
                    return response()->json(['error' =>  trans('message.invalid_phone_credentials')], ResponseHTTP::HTTP_UNAUTHORIZED);
                }
                Log::info('Phone data retrieved:', ['phoneData' => $phoneData]);
                if ($this->authenticatePassword($credentials, $userData, $request->input('phone_password'), $phoneData)) {
                    Log::info('Both password and phone password checks passed');
                    // Set the flag to include phone in JWT
                    $userData->setAddPhoneToJWT($validator['flag']);
                    $token = JWTAuth::attempt($credentials);
                } else {
                    Log::error('Unauthorized: Invalid credentials', ['userData' => $userData]);
                    return response()->json(['error' => trans('message.invalid_credentials')], ResponseHTTP::HTTP_UNAUTHORIZED);
                }
            }
            $dataArr = [
                "userInfo" => $userData,
                "token" => $token,
            ];
            return is_string($token) ? $this->apiResponse->getResponseStructure(trans('message.success'), $dataArr, trans('message.login')) : $token;
        } catch (\Exception $ex) {
            Log::error('Error during authentication: ' . $ex->getMessage());
            return response()->json(['error' => trans('message.invalid_credentials')], ResponseHTTP::HTTP_UNAUTHORIZED);
        }
    }


    public function logout($request)
    {
        $token = $request->header('Authorization');
        $parseValue = JWTAuth::parseToken();
        $userId = $parseValue->authenticate()->id;
        $parseValue->invalidate($token);
        return true;
    }

    private function getUserData($credentials, $model, $flag)
    {
        $userData = $model::where(function ($query) use ($credentials) {
            if (isset($credentials['email'])) {
                $query->where('email', $credentials['email']);
            } else {
                $query->where('username', $credentials['username']);
            }
        });

        if ($flag == 2) {
            $userData = $userData->where('role_id', 3);
        }
        $userData = $userData->first();
        return $userData;
    }

    private function getPhoneData($request)
    {
        return Phone::where('login', $request->input('phone'))
            ->where('status', 'ACTIVE')
            ->first();
    }

    private function authenticatePassword($credentials, $userData, $phonePassword, $phoneData)
    {
        return Hash::check($credentials['password'], $userData->password) && $phonePassword == $phoneData->pass;
    }
}
