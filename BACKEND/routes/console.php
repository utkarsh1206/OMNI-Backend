<?php
use Illuminate\Support\Facades\Artisan;
use App\Models\CronConfiguration;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

Artisan::command('app:sla-email-notification', function () {
    $this->comment("Sla Email Notification");
})->purpose('Sla Email Notification')->everyMinute();

$allTenants = Tenant::orderBy('id')  // Ensures consistent ordering by the ID column
    ->chunkById(100, function ($tenants) {
        foreach ($tenants as $tenant) {
            $newConnectionName = Str::replace(".", "_", $tenant->domain_name);
            
            $databaseName = Str::replace(".", "_", $tenant->domain_name);

            Config::set("database.connections.$databaseName", [
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
            DB::reconnect($databaseName);
            // Fetch support tickets for each tenant
            if (Schema::connection($databaseName)->hasTable('cron_configurations')) {
                $cronConfiguations = CronConfiguration::on($databaseName)->get();
                CronConfiguration::on($databaseName)->where('status', 'active')->get()->each(function ($cronJob) {
                    Artisan::command($cronJob->command_signature, function () use ($cronJob) {
                        $this->info("Running cron job: {$cronJob->command_signature}");
                    })
                    ->cron($cronJob->schedule_expression);
                });
            }
        }
    });

Schedule::command('telescope:prune --hours=168')->daily();
