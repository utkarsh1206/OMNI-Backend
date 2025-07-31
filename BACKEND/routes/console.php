<?php

use App\Models\CronConfiguration;
use App\Models\Detail;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Schedule::command('app:sla-email-notification')->everyMinute();

$allTenants = Tenant::orderBy('id')
    ->chunkById(100, function ($tenants) {
        foreach ($tenants as $tenant) {
            $detailsInfo = Detail::select("uin")->where("domain",$tenant->domain_name)->first();
            $databaseName = Str::replace(".", "_", $tenant->domain_name);
            // Config::set("database.connections.$databaseName", [
            //     'driver'    => 'mysql',
            //     'host'      => env('DB_HOST', '127.0.0.1'),
            //     'port'      => env('DB_PORT', '3306'),
            //     'database'  => $databaseName,
            //     'username'  => env('DB_USERNAME'),
            //     'password'  => env('DB_PASSWORD'),
            //     'charset'   => 'utf8mb4',
            //     'collation' => 'utf8mb4_unicode_ci',
            //     'prefix'    => '',
            //     'strict'    => true,
            //     'engine'    => null,
            // ]);
            getDbConnection($tenant->domain_name);
            DB::reconnect($databaseName);
            if (Schema::connection($databaseName)->hasTable('cron_configurations')) {
                // $cronConfiguations = CronConfiguration::on($databaseName)->get();
                CronConfiguration::on($databaseName)->where('status', 'active')->get()->each(function ($cronJob) use ($detailsInfo) {
                    $signatureWithArgument = "{$cronJob->command_signature} {$detailsInfo->uin}";
                    Schedule::command($signatureWithArgument)->cron($cronJob->schedule_expression);
                });
            }
        }
    });

Schedule::command('telescope:prune --hours=96')->everySixHours();
