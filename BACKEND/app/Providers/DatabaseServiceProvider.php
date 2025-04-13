<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Detail;
use Illuminate\Http\Request;

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
        // dd('Here');
        $domain = $request->input('domain');
        
        $fullUrl = $request->fullUrl();
        $lastSegment = $request->segment($request->segments() ? count($request->segments()) : 0);
       
        if ($domain == '' && $lastSegment != 'login') {
            $token = $request->bearerToken();
          
            if ($token != "") {
                $payload = extractPayload($token);
               
                if ($payload['admin_type'] != 'Super') {
                    $domain = $payload['domain'];
                    $domain = str_replace("https://", "", $domain);
                    $domain = str_replace("http://", "", $domain);
                }
            }
        }

        $details = Detail::where('domain', $domain)->first();
        if ($details) {
            $connectionDetails = [
                'database' => $details->db,
                'username' => $details->username,
                'password' => $details->password,
            ];
           
            Config::set('database.connections.dynamic', [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => $connectionDetails['database'],
                'username' => $connectionDetails['username'],
                'password' => $connectionDetails['password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            Config::set('database.default', 'dynamic');

            DB::purge('dynamic');
            DB::reconnect('dynamic');
        }
    }
}
