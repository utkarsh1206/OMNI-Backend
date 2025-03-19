<?php

use App\Models\EmailLog;
use App\Models\EmailConfigure;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\LicenseController;
use App\Services\AESService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/**
 * Format price with a currency symbol.
 *
 * @param  float  $price
 * @param  string  $currency
 * @return string
 */
if (! function_exists('logEmail')) {
    function logEmail($data, $newConnectionName = null)
    {
        $emailLog = new EmailLog;
        if ($newConnectionName) {
            $emailLog->setConnection($newConnectionName);
        }
        $emailLog->fill($data);
        $emailLog->save();
    }
}

/**
 * Set email configuration
 *
 */
if (! function_exists('setEmailConfig')) {
    function setEmailConfig()
    {
        $emailConfig = EmailConfigure::where('type', 'gmail')->first();
        $smtpHost = $emailConfig->smtp_host;
        $smtpPort = $emailConfig->smtp_port;
        $smtpUser = $emailConfig->smtp_user;
        $smtpPassword = $emailConfig->smtp_password;
        $sender_name = $emailConfig->sender_name;
        $sender_email = $emailConfig->sender_email;

        config([
            'mail.mailers.smtp.host' => $smtpHost,
            'mail.mailers.smtp.port' => $smtpPort,
            'mail.mailers.smtp.username' => $smtpUser,
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
            'mail.from.address' =>  $sender_email,
            'mail.from.name' => $sender_name,
        ]);
    }
}

/**
 * Check License is Expired or not
 *
 * @return string
 */
if (! function_exists('isLicenseExpired')) {
    function isLicenseExpired(Request $request)
    {
        $is_tanant = true;
        $aesServices = new AESService;
        $licenseController = new LicenseController($aesServices);
        $licenseResponse = $licenseController->decodeLicense(new Request(['is_tanant' => $is_tanant]));
        $licenseInfo = $licenseResponse->getData(true);
        if (isset($licenseInfo['validity_date'])) {
            if ($licenseInfo['validity_date'] <= time()) {
                Log::info('License expired');
                return true;
            }
        } else {
            return response()->json(['error' => trans('message.invalid_license')], ResponseHTTP::HTTP_BAD_REQUEST);
        }

        return false;
    }
}

if (! function_exists('authenticateUser')) {

    function authenticateUser($credentials, $model, $authGuard = 'api')
    {
      
        $credentialsKey = isset($credentials['email']) ? 'email' : 'username';
        $credentialsValue = $credentials[$credentialsKey];
        $userData = $model::where($credentialsKey, $credentialsValue)->first();
       
        if (!$userData) {
            return response()->json(['error' => trans('message.invalid_credentials')], 401);
        }
        
        // Attempt to authenticate the user
        if (!$token = auth($authGuard)->attempt($credentials)) {
            return response()->json(['error' => trans('message.invalid_credentials')], 401);
        }
        return $token;
    }

    if (! function_exists('establishDatabaseConnect')) {
        function establishDatabaseConnect($newConnectionName, $databaseName)
        {
            Config::set("database.connections.$newConnectionName", [
                'driver'    => 'mysql',
                'host'      => env('DB_HOST', '127.0.0.1'),
                'port'      => env('DB_PORT', '3306'),
                'database'  => $databaseName,
                'username'  => env('DB_USERNAME'),
                'password'  => env('DB_PASSWORD'),
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
            ]);
        }
    }



    if (!function_exists('sendHttpRequest')) {
        /**
         * Send an HTTP request using Laravel's HTTP client
         *
         * @param string $url The API URL.
         * @param string $method The HTTP method (GET, POST, PUT, DELETE, etc.).
         * @param array $data The data to send with the request (optional).
         * @param array $headers Custom headers (optional).
         * @param bool $verifySSL Whether to verify SSL certificate (default: false).
         * @return array The response body and status code or error message.
         * @throws \Exception if any error occurs during the HTTP request.
         */
        function sendHttpRequest($url, $method = 'POST', $data = [], $headers = [], $verifySSL = false)
        {
            try {
                // Set headers and prepare the request
                $defaultHeaders = [
                    'Content-Type' => 'application/json',
                ];
    
                if (!empty($headers)) {
                    $defaultHeaders = array_merge($defaultHeaders, $headers);
                }
    
                // Send the HTTP request using Laravel's Http facade
                $response = Http::withHeaders($defaultHeaders)
                    ->withOptions(['verify' => $verifySSL])  // Set SSL verification option
                    ->$method($url, $data);  // Use dynamic HTTP method (GET, POST, etc.)
    
                // Check if the request was successful
                if ($response->successful()) {
                    // If the response contains a code, handle it
                    $responseData = $response->json();
                    if (isset($responseData['code'])) {
                        return response()->json([
                            'message' => $responseData['message'],
                            'data' => $responseData['data'],
                        ]);
                    }
    
                    return response()->json([
                        'message' => $responseData["message"] ?? 'No code in the response.',
                        'data' => $responseData,
                    ], $response->status());
                } else {
                    // Handle errors by getting the response message and code
                    $responseData = $response->json();
                    return response()->json([
                        'message' => $responseData["message"] ?? 'Error communicating with the API.',
                        'code' => $responseData['data']['code'] ?? $response->status(),
                    ], $response->status());
                }
            } catch (\Exception $e) {
                return [
                    'status_code' => 500,
                    'message' => $e->getMessage(),
                ];
            }
        }
}

}