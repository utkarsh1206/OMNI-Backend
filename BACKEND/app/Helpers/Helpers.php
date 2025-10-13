<?php

use App\Models\EmailLog;
use App\Models\EmailConfigure;
use App\Http\Controllers\Api\LicenseController;
use App\Mail\SendEmail;
use App\Services\AESService;
use App\Models\NeoxConfiguration;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\Tenant;
use App\Models\TenantModule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    function setEmailConfig($connectionName = null)
    {
        $query = new EmailConfigure;

        if ($connectionName) {
            $query = $query->setConnection($connectionName);
        }

        $emailConfig = $query->where('type', 'gmail')->first();

        if (!$emailConfig) {
            throw new Exception("No email config found for connection: {$connectionName}");
        }

        $smtpHost = $emailConfig->smtp_host;
        $smtpPort = $emailConfig->smtp_port;
        $smtpUser = $emailConfig->smtp_user;
        $smtpPassword = $emailConfig->smtp_password;
        $sender_name = $emailConfig->sender_name;
        $sender_email = $emailConfig->sender_email;

        config([
            'mail.default' => 'smtp',
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
            if ($licenseInfo['validity_date'] <= date('Y-m-d')) {
                generateLogs('info', 'License expired');
                return [
                    'status' => true,
                    'message' => 'License expired',
                    'data' => $licenseInfo,
                ];
            }

            return [
                'status' => false,
                'message' => 'License valid',
                'data' => $licenseInfo,
            ];
        } else {
            return response()->json([
                'status' => false,
                'error' => trans('message.invalid_license'),
            ], ResponseHTTP::HTTP_BAD_REQUEST);
        }
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
    function getDbConnection($domainName = null, $isExternal = false)
    {
        // If no domain name is provided, return the default connection
        if (!$domainName) {
            return null; // Return null to use the default connection
        }

        // Get the database name associated with the domain from 'details' table
        $details = DB::table('details')->where('domain', $domainName)
            ->orWhere('uin', $domainName)->first();

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

            if ($isExternal) {
                DB::setDefaultConnection($newConnectionName);
            }

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
if (!function_exists('sendHttpRequest')) {
    function sendHttpRequest(
        $url,
        $method = 'POST',
        $data = [],
        $headers = [],
        $verifySSL = false,
        $standardizeResponse = true,
        $rawGet = false
    ) {
        try {
            $defaultHeaders = [];

            if (!$rawGet) {
                $defaultHeaders['Content-Type'] = 'application/json';
            }

            if (!empty($headers)) {
                $defaultHeaders = array_merge($defaultHeaders, $headers);
            }

            if ($rawGet) {
                $response = Http::timeout(config('omni.TIMEOUT'))
                    ->retry(config('omni.RETRY_ATTEMPTS'), config('omni.RETRY_WAIT'))
                    ->withHeaders($defaultHeaders)
                    ->withOptions(['verify' => $verifySSL])
                    ->$method($url);
            } else {
                $response = Http::timeout(config('omni.TIMEOUT'))
                    ->retry(config('omni.RETRY_ATTEMPTS'), config('omni.RETRY_WAIT'))
                    ->withHeaders($defaultHeaders)
                    ->withOptions(['verify' => $verifySSL])
                    ->$method($url, $data);
            }

            if ($response->successful()) {
                $responseData = $response->json();
                if (!$standardizeResponse) {
                    return $responseData;
                }

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
}

if (!function_exists('generateLogs')) {

    function generateLogs($type, $msg, $error = null)
    {
        // Get the file and line info where the log was triggered
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0] ?? null;
        $file = isset($backtrace['file']) ? basename($backtrace['file']) : 'unknown_file';
        $line = $backtrace['line'] ?? 'unknown_line';

        // Construct the log message
        $logMessage = "{$msg} | File: {$file} | Line: {$line}";
        if ($error) {
            $logMessage .= " | Error: {$error}";
        }

        $logFilePath = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
        if (file_exists($logFilePath) && env("APP_ENV") != "local") {
            $fileOwner = posix_getpwuid(fileowner($logFilePath));
            if ($fileOwner && isset($fileOwner['name']) && $fileOwner['name'] === 'root') {
                unlink($logFilePath);
                $logMessage .= ' | Log file owned by root was deleted.';
            }
        }
        Log::$type($logMessage);
    }
}

if (!function_exists('generateScheduleLogs')) {

    function generateScheduleLogs($type, $msg, $error = null)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0] ?? null;
        $file = isset($backtrace['file']) ? basename($backtrace['file']) : 'unknown_file';
        $line = $backtrace['line'] ?? 'unknown_line';

        $logMessage = "{$msg} | File: {$file} | Line: {$line}";
        if ($error) {
            $logMessage .= " | Error: {$error}";
        }

        // Log file specific to scheduler logs
        $logFilePath = storage_path('logs/cron/schedule-' . date('Y-m-d') . '.log');

        // Log to the scheduler channel (you must define this in config/logging.php)
        Log::channel('scheduler')->$type($logMessage);
    }
}

if (!function_exists('logMessage')) {
    function logMessage($level, $message, $data = [])
    {
        switch ($level) {
            case 'error':
                Log::error($message, $data);
                break;
            case 'info':
                Log::info($message, $data);
                break;
            case 'warning':
                Log::warning($message, $data);
                break;
            case 'debug':
                Log::debug($message, $data);
                break;
            default:
                Log::info($message, $data);
                break;
        }
    }
}

if (! function_exists('uploadAttachment')) {
    function uploadAttachment(Request $request, $fieldName = 'attachment', $directory = 'attachments')
    {
        $fileInfo = [];
        if ($request->hasFile($fieldName)) {
            $attachment = $request->file($fieldName);
            if (is_array($attachment)) {
                foreach ($attachment as $value) {
                    $filename = getSanitizedOriginalName(
                        $value->getClientOriginalName()
                    ) . '_' . now()->timestamp . '.' . $value->getClientOriginalExtension();
                    $filePath = $value->storeAs($directory, $filename, 'public');
                    $fileInfo[] = [
                        'fileName' => $filename,
                        'path' => $filePath,
                        'mimeType' => $value->getMimeType()
                    ];
                }
            } else {
                $filename = getSanitizedOriginalName(
                    $attachment->getClientOriginalName()
                ) . '_' . now()->timestamp . '.' . $attachment->getClientOriginalExtension();
                $filePath = $attachment->storeAs($directory, $filename, 'public');
                $fileInfo = [
                    'fileName' => $filename,
                    'path' => $filePath,
                    'mimeType' => $attachment->getMimeType()
                ];
            }
        }
        return $fileInfo;
    }
}

if (! function_exists('extractPayload')) {
    function extractPayload($token)
    {
        return $token ? JWTAuth::setToken($token)->getPayload() : null;
    }
}

if (! function_exists('convertToLower')) {
    function convertToLower($value)
    {
        return Str::lower($value);
    }
}

if (! function_exists('getIconforNotification')) {
    function getIconforNotification($type)
    {
        $icon =   [
            "Ticket Created" => "cilUser",
            "Ticket Transfer" => "cilTransfer",
            "Ticket Merge" => "cilSync",
            "Priority Changed" => "cilChevronDoubleUp",
            "Type Changed" => "cilFire",
            "Ticket Close" => "cilCheck",
            "Status Changed" => "cilBolt",
        ];

        return $icon[$type] ?? "cilAsteriskCircle";
    }
}

if (! function_exists('getColorforNotification')) {
    function getColorforNotification($type)
    {
        $color =   [
            "Ticket Created" => "primary",
            "Ticket Transfer" => "info",
            "Ticket Merge" => "warning",
            "Priority Changed" => "secondary",
            "Type Changed" => "danger",
            "Ticket Close" => "success",
            "Status Changed" => "info",
        ];

        return $color[$type] ?? "secondary";
    }
}


if (! function_exists('getSanitizedOriginalName')) {
    function getSanitizedOriginalName($fileName)
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
    }
}

if (! function_exists('getPermissionFromRoleId')) {
    function getPermissionFromRoleId($roleId)
    {
        $permissionData = RolePermission::where("role_id", $roleId)->pluck("permission_id");
        $permissionInfo = Permission::with(["module", "action"])->whereIn("id", $permissionData)->get();
        $permissionInfo = $permissionInfo->toArray();
        $formattedPermissions = [];

        foreach ($permissionInfo as $permission) {
            $moduleName = $permission['module']['name'];
            $actionName = $permission['action']['name'];

            if (!isset($formattedPermissions[$moduleName])) {
                $formattedPermissions[$moduleName] = [];
            }

            if (!in_array($actionName, $formattedPermissions[$moduleName])) {
                $formattedPermissions[$moduleName][] = $actionName;
            }
        }

        return $formattedPermissions;
    }
}

if (!function_exists('getRecordingFile')) {
    function getRecordingFile($uniqueId)
    {
        $recordingPath = base_path(config('omni.RECORDING_FILE_PATH'));

        if (!is_dir($recordingPath)) {
            return null;
        }

        $files = File::files($recordingPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();

            if (str_contains($filename, $uniqueId)) {
                return response()->json([
                    'filename' => $filename,
                    'url' => asset("storage/recordings/$filename")
                ]);
            }
        }

        return null;
    }
}

if (!function_exists('exportCsv')) {
    function exportCsv(array $headers, array $rows, string $filenamePrefix = 'export'): string
    {
        $filename = $filenamePrefix . '_' . now()->format('Ymd_His') . '.csv';
        $directory = storage_path('app/public/exports');

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . '/' . $filename;

        $file = fopen($path, 'w');
        fputcsv($file, $headers);
        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return asset('storage/exports/' . $filename);
    }
}

if (! function_exists('getModuleId')) {
    function getModuleId($moduleName)
    {
        $moduleInfo = TenantModule::where("name", $moduleName)->first();
        return $moduleInfo->id ?? 0;
    }
}

if (! function_exists('getPermissionFromRoleIdForTenant')) {
    function getPermissionFromRoleIdForTenant($domain)
    {
        establishMasterConnection($domain, "omnichannel", "mysql");
        $tenant = Tenant::select("tenant_role_id")->where('domain_name', $domain)
            ->first();
        $permissionData = RolePermission::where("role_id", $tenant->tenant_role_id)->pluck("permission_id");
        $permissionInfo = Permission::with(["module", "action"])->whereIn("id", $permissionData)->get();
        $permissionInfo = $permissionInfo->toArray();
        $formattedPermissions = [];

        foreach ($permissionInfo as $permission) {
            $moduleName = $permission['module']['name'];
            $actionName = $permission['action']['name'];

            if (!isset($formattedPermissions[$moduleName])) {
                $formattedPermissions[$moduleName] = [];
            }

            if (!in_array($actionName, $formattedPermissions[$moduleName])) {
                $formattedPermissions[$moduleName][] = $actionName;
            }
        }
        getdbConnection($domain, false);
        return $formattedPermissions;
    }
}

if (! function_exists('setTenantData')) {
    function setTenantData($userInfo)
    {
        establishMasterConnection($userInfo->domain, "omnichannel", "mysql");
        $tenant = Tenant::select("tenant_role_id")->where('domain_name', $userInfo->domain)->first();
        return $tenant->tenant_role_id;
    }
}

if (!function_exists('getDomainByUin')) {
    function getDomainByUin($uin)
    {
        return DB::table('omnichannel.details')
            ->where('uin', $uin)
            ->value('domain');
    }
}

if (! function_exists('getTemplatePlaceholders')) {
    /**
     * Build placeholder replacements for ticket emails.
     *
     * @param  object $ticket   Eloquent Ticket object
     * @param  array  $extra    Additional placeholders to merge
     * @return array
     */
    function getTemplatePlaceholders($body, $ticket = null, $extra = [])
    {
        if ($ticket) {
            $placeholders = [
                "customer_name"      => $ticket->contact_name ?? '',
                "ticket_number"      => $ticket->support_ticket_id ?? '',
                "ticket_subject"     => $ticket->subject ?? '',
                "ticket_status"      => $ticket->status_name ?? '',
                "ticket_created"     => optional($ticket->created_at)->format('d M Y, H:i:s A T') ?? '',
                "ticket_description" => $ticket->description ?? '',
                "assigned_user"      => $ticket->assigned_agent_name ?? '',
                "first_response_due" => optional($ticket->first_response_time)->format('d M Y, H:i:s A T') ?? '',
                "resolve_time_due"   => optional($ticket->resolved_time)->format('d M Y, H:i:s A T') ?? '',
            ];

            $placeholders = array_merge($placeholders, $extra);

            return str_replace(
                array_map(fn($key) => "{" . $key . "}", array_keys($placeholders)),
                array_values($placeholders),
                $body
            );
        }
        return $body;
    }
}

if (! function_exists('sendProcessedEmail')) {
    /**
     * Generic email sender with logging & template replacement.
     *
     * @param string $to
     * @param string $subject
     * @param string $bodyTemplate
     * @param int $mailTemplateId
     * @param array $mailAttachments
     * @param object $ticket
     * @param string $senderEmail
     * @param int $assignToId
     * @param string $module
     * @param array|null $cc
     * @param array|null $bcc
     * @return void
     */
    function sendProcessedEmail(
        $to,
        $subject,
        $bodyTemplate,
        $mailTemplateId,
        $mailAttachments,
        $ticket,
        $senderEmail,
        $assignToId,
        $module,
        ?array $cc = [],
        ?array $bcc = [],
        $newConnectionName = null
    ) {
        $body = !empty($bodyTemplate)
            ? getTemplatePlaceholders($bodyTemplate, $ticket)
            : '';
        $emailJson = [
            'to'  => $to,
            'cc'  => $cc,
            'bcc' => $bcc,
        ];
        $sendEmailData = new SendEmail($subject, $body, $mailAttachments, $ticket);
        $renderedHtml  = $sendEmailData->render();
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $renderedHtml, $matches)) {
            $bodyContent = $matches[1];
        } else {
            $bodyContent = $renderedHtml;
        }
        // Final body to be logged
        $finalBody = $bodyContent;
        try {
            Mail::to($to)->cc($cc)->bcc($bcc)->send($sendEmailData);
            logEmail([
                "user_id"           => $assignToId,
                "email_template_id" => $mailTemplateId,
                "from_email"        => $senderEmail,
                "module_name"       => $module,
                "entity_id"         => $ticket->id ?? 0,
                "status"            => "Sent",
                "email_json"        => json_encode($emailJson),
                "subject"           => $subject,
                "body"              => $finalBody,
            ], $newConnectionName);
            return 1;
        } catch (Exception $e) {
            $reason = !empty($e->getMessage()) ? substr($e->getMessage(), 0, 250) : 'Unknown error occurred.';
            logEmail([
                "user_id"           => $assignToId,
                "email_template_id" => $mailTemplateId,
                "from_email"        => $senderEmail,
                "module_name"       => $module,
                "entity_id"         => $ticket->id ?? 0,
                "status"            => "Fail",
                "email_json"        => json_encode($emailJson),
                "subject"           => $subject,
                "body"              => $finalBody,
                "fail_reason"       => $reason,
            ], $newConnectionName);
            generateLogs('error', "{$module} mail failed: " . $e->getMessage());
            // throw $e;
            return 0;
        }
    }
}

if (!function_exists('failedValidation')) {
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    function failedValidation($validator)
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], ResponseHTTP::HTTP_BAD_REQUEST)
        );
    }
}

if (!function_exists('encryptString')) {
    function encryptString($string)
    {
        return Crypt::encryptString($string);
    }
}

if (!function_exists('decryptString')) {
    function decryptString($string)
    {
        return Crypt::decryptString($string);
    }
}
