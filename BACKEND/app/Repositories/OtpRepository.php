<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use App\Models\Contact;
use App\Models\EndUser;
use App\Models\SmsTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tzsk\Otp\Facades\Otp;
use App\Services\SmsService;

class OtpRepository
{
    private $apiResponse;
    private $otpConfig;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
        $this->otpConfig = config('otp');
    }

    public function requestOtp(Request $request)
    {
        try {
            $field = $request->filled('mobile') ? 'mobile' : 'email';
            $key = $request->$field;

            $contact = Contact::where($field, $key)->first();

            if (!$contact) {
                return response()->json([
                    'error' => 'Mobile or Email does not exist in our records.',
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $username = trim($contact->first_name . ' ' . $contact->last_name);
            $uin = $request->attributes->get('tenant_uin');

            $otp = Otp::generate("{$uin}_{$key}");

            $expiresInMinutes = $this->otpConfig['expiry'] ?? 5;

            $otpData = [
                'otp' => $otp,
                'attempts' => 0,
                'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
                'username' => $username,
                'userid' => $contact->id,
            ];

            Cache::put("{$uin}_{$key}", $otpData, $expiresInMinutes * 60);

            try {
                if ($request->filled('email')) {
                    setEmailConfig();
                    Mail::to($request->email)->send(new \App\Mail\OtpMail($otp));
                }

                if ($request->filled('mobile')) {
                    $this->sendOtpSms($request->mobile, $otp, $otpData['expires_at']);
                }

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    null,
                    trans('message.otp_send')
                );
            } catch (Exception $e) {
                return $this->apiResponse->handleAndResponseException($e);
            }
        } catch (Exception $e) {
            generateLogs("error", "An unexpected error occurred during OTP request for UIN {$uin}: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function verifyOtp(Request $request, $uin)
    {
        try {
            $connection = getDbConnection($uin, true);

            if (!$connection) {
                return response()->json(['error' => 'Invalid UIN'], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $request->validate([
                'mobile' => 'nullable|string',
                'email' => 'nullable|email',
                'otp' => 'required|string',
            ]);

            $key = $request->mobile ?? $request->email;
            if (empty($key)) {
                return response()->json(['error' => 'Mobile or Email is required.'], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            $otpKey = "{$uin}_{$key}";
            $otpData = Cache::get($otpKey);

            if (!$otpData) {
                generateLogs("warning", "OTP not found/expired for {$otpKey}");
                return response()->json(['error' => 'OTP has expired or not found.'], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check expiration
            if (Carbon::now()->gt($otpData['expires_at'])) {
                Cache::forget($otpKey);
                generateLogs("warning", "OTP expired for {$otpKey}");
                return response()->json(['error' => 'OTP has expired.'], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Increment attempts
            $otpData['attempts']++;
            $maxAttempts = $this->otpConfig['throttle'] ?? 3;

            if ($otpData['attempts'] > $maxAttempts) {
                Cache::forget($otpKey);
                generateLogs("warning", "OTP max attempts exceeded for {$otpKey}");
                return response()->json(['error' => 'Maximum OTP attempts exceeded.'], ResponseHTTP::HTTP_TOO_MANY_REQUESTS);
            }

            // Save updated attempt count back
            $ttl = Carbon::now()->diffInSeconds($otpData['expires_at']);
            Cache::put($otpKey, $otpData, $ttl);

            // Match OTP
            if ($request->otp !== $otpData['otp']) {
                generateLogs("warning", "Invalid OTP entered for {$otpKey}: {$request->otp}");
                return response()->json(['error' => 'Invalid OTP.'], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            // OTP valid – remove it
            Cache::forget($otpKey);

            $user = EndUser::firstOrCreate([
                'mobile' => $request->mobile,
                'email' => $request->email,
            ]);

            $username = $otpData['username'] ?? 'Guest';

            $userinfo = array_merge(
                $user->toArray(),
                ['username' => $username]
            );

            $domain = getDomainByUin($uin);

            $token = auth('api_endusers')->claims([
                'username' => $username,
                'userid' => $otpData['userid'],
                'uin' => $uin,
                'domain' => $domain
            ])->login($user);

            $data = [
                'userinfo' => $userinfo,
                'token' => $token,
                'token_type' => 'bearer',
            ];
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.otp_verified'));
        } catch (Exception $e) {
            generateLogs("error", "An unexpected error occurred during OTP verification for UIN {$uin}: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function verifyOtpNew(Request $request)
    {
        try {
            $key = $request->mobile ?? $request->email;
            $uin = $request->attributes->get('tenant_uin');

            $otpKey = "{$uin}_{$key}";
            $otpData = Cache::get($otpKey);

            if (!$otpData) {
                generateLogs("warning", "OTP not found/expired for {$otpKey}");
                return response()->json(['error' => 'OTP has expired or not found.'], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check expiration
            if (Carbon::now()->gt($otpData['expires_at'])) {
                Cache::forget($otpKey);
                generateLogs("warning", "OTP expired for {$otpKey}");
                return response()->json(['error' => 'OTP has expired.'], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Increment attempts
            $otpData['attempts']++;
            $maxAttempts = $this->otpConfig['throttle'] ?? 3;

            if ($otpData['attempts'] > $maxAttempts) {
                Cache::forget($otpKey);
                generateLogs("warning", "OTP max attempts exceeded for {$otpKey}");
                return response()->json(['error' => 'Maximum OTP attempts exceeded.'], ResponseHTTP::HTTP_TOO_MANY_REQUESTS);
            }

            // Save updated attempt count back
            $ttl = Carbon::now()->diffInSeconds($otpData['expires_at']);
            Cache::put($otpKey, $otpData, $ttl);

            // Match OTP
            if ($request->otp !== $otpData['otp']) {
                generateLogs("warning", "Invalid OTP entered for {$otpKey}: {$request->otp}");
                return response()->json(['error' => 'Invalid OTP.'], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            // OTP valid – remove it
            Cache::forget($otpKey);

            $user = EndUser::firstOrCreate([
                'mobile' => $request->mobile,
                'email' => $request->email,
            ]);

            $username = $otpData['username'] ?? 'Guest';

            $userinfo = array_merge(
                $user->toArray(),
                ['username' => $username]
            );

            $domain = getDomainByUin($uin);

            $token = auth('api_endusers')->claims([
                'username' => $username,
                'userid' => $otpData['userid'],
                'uin' => $uin,
                'domain' => $domain
            ])->login($user);

            $data = [
                'userinfo' => $userinfo,
                'token' => $token,
                'token_type' => 'bearer',
            ];
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.otp_verified'));
        } catch (Exception $e) {
            generateLogs("error", "An unexpected error occurred during OTP verification for UIN {$uin}: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    function sendOtpSms($mobile, $otp, $expiresInMinutes)
    {
        $smsInfo = new SmsService;
        $templateInfo = SmsTemplate::where("name", 'OTP Send')->first();
        $templateName = $templateInfo->name ?? '';
        $template = $templateInfo->content ?? '';
        // $template = str_replace("{#var#}", $otp, $template);
        $replacements = [$otp, $expiresInMinutes];

        $finalMessage = preg_replace_callback('/\{\#var\#\}/', function () use (&$replacements) {
            return array_shift($replacements);
        }, $template);

        $smsInfo->sendSMS('1', $templateName, $finalMessage, $mobile);
    }
}
