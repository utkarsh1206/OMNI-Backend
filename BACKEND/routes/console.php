<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use App\Models\CronConfiguration;
use App\Models\Tenants;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();



Artisan::command('app:sla-email-notification', function () {
    $this->comment("Sla Email Notification");
})->purpose('Sla Email Notification')->everyMinute();

$allTenants = Tenants::where("is_delete", "No")
        ->orderBy('id')  // Ensures consistent ordering by the ID column
        ->chunkById(100, function ($tenants) {
            foreach ($tenants as $tenant) {
                $newConnectionName = str_replace(".", "_", $tenant->domain_name);
                $databaseName = str_replace(".", "_", $tenant->domain_name);
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
                DB::reconnect($newConnectionName);
                // Fetch support tickets for each tenant
                if (Schema::connection($newConnectionName)->hasTable('cron_configurations')) {
                $cronConfiguations = CronConfiguration::on($newConnectionName)->get();
                CronConfiguration::on($newConnectionName)->where('status', 'active')->get()->each(function ($cronJob) {
                    Artisan::command($cronJob->command_signature, function () use ($cronJob) {
                    
                        $this->info("Running cron job: {$cronJob->command_signature}");
                    })
                    ->cron($cronJob->schedule_expression);
                });
            }
            }
        });

