<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        Tenant::orderBy('id')
            ->chunkById(100, function ($tenants) {
                foreach ($tenants as $tenant) {
                    $newConnectionName = str_replace(".", "_", $tenant->domain_name);
                    // $databaseName = str_replace(".", "_", $tenant->domain_name);
                    // establishDatabaseConnect($newConnectionName, $databaseName);
                    // DB::reconnect($newConnectionName);
                    getDbConnection($tenant->domain_name);
                    DB::reconnect($newConnectionName);
                    Artisan::call('db:seed', [
                        '--database' => $newConnectionName,
                        '--class' => 'TenantSeeder',
                        '--force' => true,
                    ]);
                }
            });
    }
}
