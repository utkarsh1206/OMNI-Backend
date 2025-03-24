<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;

class ExistingTenantSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:existing-tenant-seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $allTenants = Tenant::orderBy('id')  // Ensures consistent ordering by the ID column
            ->chunkById(100, function ($tenants) {
                foreach ($tenants as $tenant) {
                    $newConnectionName = str_replace(".", "_", $tenant->domain_name);
                    $databaseName = str_replace(".", "_", $tenant->domain_name);
                    $this->establishDatabaseConnect($newConnectionName, $databaseName);
                    DB::reconnect($newConnectionName);
                    Artisan::call('db:seed', [
                        '--database' => $newConnectionName,
                        '--class' => 'TenantSeeder',
                        '--force' => true, // Run seeders in production if necessary
                    ]);
                }
            });
    }

    public function establishDatabaseConnect($newConnectionName, $databaseName)
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
