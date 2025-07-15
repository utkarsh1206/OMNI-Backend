<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Request $request): void
    {
        $domain = $request->input('domain');
        
        $lastSegment = $request->segment($request->segments() ? count($request->segments()) : 0);
       
        if ($domain == '' && $lastSegment != 'login') {
            $token = $request->bearerToken();
            if ($token != "") {
                $payload = extractPayload($token);
                if ($payload['admin_type'] != 'Super') {
                    $domain = $payload['domain'];
                    $domain = Str::replace("https://", "", $domain);
                    $domain = Str::replace("http://", "", $domain);
                }
            }
        }
        
        $currentRoute = request()->path();
        $protectedPrefixes = [
            'api/chatbot-room-id',
            'api/fetch-session-messages',
            // 'api/call-initiate',
            // 'api/call-connected',
            // 'api/call-hangup',
            // 'api/call-history',
            'api/dc-user-logout',
        ];
        if (!Str::startsWith($currentRoute, $protectedPrefixes)) {
            $details = Detail::where('domain', $domain)->first();
            if ($details) {
                $connectionDetails = [
                    'database' => $details->db,
                    'username' => $details->username,
                    'password' => $details->password,
                ];
            
                Config::set('database.connections.dynamic', [
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
                    'dump' => [
                        'dump_binary_path' => 'C:\xampp\mysql\bin',
                        'use_single_transaction',
                        'timeout' => 60 * 5, // 5 minute timeout
                    ]
                ]);
                Config::set('database.default', 'dynamic');
                DB::purge('dynamic');
                DB::reconnect('dynamic');
            }
        }
    }
}
