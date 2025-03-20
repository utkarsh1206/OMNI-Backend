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
}