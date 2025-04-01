<?php

use App\Models\EmailLog;
use App\Models\EmailConfigure;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\LicenseController;
use App\Services\AESService;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Illuminate\Support\Facades\Http;
use App\Models\NeoxConfiguration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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
                generateLogs('info', 'License expired');
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
}


if (!function_exists('establishMasterConnection')) {

    function establishMasterConnection($domain, $masterDB, $connection)
    {
        DB::purge($domain);
        establishDatabaseConnect($domain, $masterDB);
        DB::setDefaultConnection($connection);
    }
}


if (!function_exists('getDbConnection')) {
    /**
     * Set up a dynamic database connection based on the domain name.
     *
     * @param string|null $domainName The domain name to find the database connection details.
     * @return \Illuminate\Database\Connection|null The database connection instance or null if no domain is provided.
     */
    function getDbConnection($domainName = null)
    {
        // If no domain name is provided, return the default connection
        if (!$domainName) {
            return null; // Return null to use the default connection
        }

        // Get the database name associated with the domain from 'details' table
        $details = DB::table('details')->where('domain', $domainName)->first();

        if ($details) {
            $connectionDetails = [
                'database' => $details->db,
                'username' => $details->username,
                'password' => $details->password,
            ];

            $newConnectionName = str_replace(".", "_", $domainName);

            // Set the dynamic connection for this domain
            Config::set("database.connections.$newConnectionName", [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'database' => $connectionDetails['database'],
                'username' => $connectionDetails['username'],
                'password' => $connectionDetails['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            return DB::connection($newConnectionName);
        }
        return null;
    }
}

if (!function_exists('getNeoxConfig')) {
    /**
     * Retrieve the Neox local IP and token from the database.
     *
     * @return array|array The IP and token or an error response if not found.
     */
    function getNeoxConfig($domainName = null)
    {
        // Get the database connection (returns null if no domain is provided)
        $connection = getDbConnection($domainName);

        // Get the 'neox_local_ip' and 'neox_token' from the database
        if (!$connection) {
            $config = NeoxConfiguration::first(['neox_local_ip', 'neox_token']);
        } else {
            // If a custom connection is set up, retrieve the values
            $config = $connection->table('neox_configuration')
                ->select('neox_local_ip', 'neox_token')
                ->first();
        }

        // If either IP or token is missing, return an error response
        if (!$config || !$config->neox_local_ip || !$config->neox_token) {
            return [
                'status' => 'error',
                'message' => trans('message.config_not_found'), // You can customize this message
            ];
        }

        return [
            'neox_local_ip' => $config->neox_local_ip,
            'neox_token' => $config->neox_token,
        ];
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
                'status_code' => ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ];
        }
    }

    if (!function_exists('generateLogs')) {

        function generateLogs($type, $msg, $error = null)
        {
            Log::$type($msg . ($error ? ' ' . $error : ''));
        }
    }
}
