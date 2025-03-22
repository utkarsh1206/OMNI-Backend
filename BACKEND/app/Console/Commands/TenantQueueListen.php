<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TenantQueueListen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tenant-queue-listen';

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
        Tenant::all()->each(function (Tenant $tenant) {
            $this->info("Starting queue:listen for tenant: {$tenant->id}");
            $newConnectionName = str_replace(".", "_", $tenant->domain_name);
            $databaseName = str_replace(".", "_", $tenant->domain_name);
            establishDatabaseConnect($newConnectionName, $databaseName);
            DB::reconnect($newConnectionName);
            Artisan::call('queue:listen');
            $this->info("Finished queue:listen for tenant: {$tenant->id}");
        });
    }
}
