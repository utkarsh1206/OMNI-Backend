<?php

use App\Models\EmailLog;
use App\Models\EmailConfigure;
use Illuminate\Support\Facades\Http;
use App\Models\NeoxConfiguration;
use Illuminate\Support\Facades\Log;


if (! function_exists('logEmail')) {
    /**
     * Format price with a currency symbol.
     *
     * @param  float  $price
     * @param  string  $currency
     * @return string
     */
    function logEmail($data, $newConnectionName = NULL)
    {
        $emailLog = new EmailLog;
        if ($newConnectionName) {
            $emailLog->setConnection($newConnectionName);
        }
        // dd($data);
        $emailLog->fill($data);
        $emailLog->save();
    }
}

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

    if (!function_exists('getNeoxLocalIp')) {
        /**
         * Retrieve the Neox local IP from the database.
         *
         * @return string|array The IP address or an error response if not found.
         */
        function getNeoxLocalIp()
        {
            $ip = NeoxConfiguration::first()->neox_local_ip ?? null;
            if (!$ip) {
                return [
                    'status' => 'error',
                    'message' => trans('message.local_ip_not_found'),
                ];
            }
            return $ip;
        }
    }

    if (!function_exists('validateSecretKey')) {
        /**
         * Validate the provided API token against the stored token.
         *
         * @param string $token The API token to validate.
         * @return mixed Null if valid, otherwise an error response.
         */
        function validateSecretKey($token)
        {
            $secretKeyFromDb = NeoxConfiguration::first()->neox_token ?? null;

            if (!$secretKeyFromDb || $token !== $secretKeyFromDb) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The provided token is invalid.',
                ]);
            }

            return null; // Return null if validation passes
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
                        $formattedResponse = [
                            'code' => $responseData['code'],
                            'message' => $responseData['message'],
                        ];

                        if (!empty($responseData['data'])) {
                            $formattedResponse['data'] = $responseData['data'];
                        }

                        return response()->json($formattedResponse, $response->status());
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
                        'code' => $responseData['code'] ?? $response->status(),
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
