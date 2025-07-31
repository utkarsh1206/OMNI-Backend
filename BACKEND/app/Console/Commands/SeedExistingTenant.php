<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Tenant;

class SeedExistingTenant extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seed-existing-tenant {domain?} {--class=}';

    /**
     * The console command description.
     */
    protected $description = 'Seed the database of an existing tenant using a specified or default seeder class.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain') ?? $this->ask('Enter the tenant domain name');

        $seederClass = $this->option('class');

        if (!$seederClass) {
            $useDefault = $this->confirm('Use TenantSeeder?', true);
            $seederClass = $useDefault ? 'TenantSeeder' : $this->ask('Enter the seeder class');
        }

        Tenant::where("domain_name", $domain)->orderBy('id')
            ->chunkById(100, function ($tenants) use ($seederClass) {
                foreach ($tenants as $tenant) {
                    $connectionName = str_replace('.', '_', $tenant->domain_name);
                    $databaseName = str_replace('.', '_', $tenant->domain_name);

                    establishDatabaseConnect($connectionName, $databaseName);
                    DB::reconnect($connectionName);

                    $this->info("Seeding tenant '{$tenant->domain_name}' using '{$seederClass}'...");

                    Artisan::call('db:seed', [
                        '--database' => $connectionName,
                        '--class' => $seederClass,
                        '--force' => true,
                    ]);
                }
            });

        $this->info('Seeding completed.');
    }
}
