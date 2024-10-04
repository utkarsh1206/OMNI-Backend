<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Detail;

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
        // $domain = $request->host();
        $domain = $request->input('domain'); 
        $details = Detail::where('domain', $domain)->first();

        if ($details) {
            // Log::info("Using database: " . $details->db);
            
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
