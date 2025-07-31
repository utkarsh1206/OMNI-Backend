<?php

namespace App\Console\Commands;

use App\Models\Detail;
use App\Models\LiveAgents;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanEndOfDayData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-end-of-day-data {uin?}';

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
        generateScheduleLogs('info', 'Cleaning end of day data for all tenants started.');
        // Tenant::whereNull('deleted_at')->orderBy('id')
        //     ->chunkById(100, function ($tenants) {
        //         foreach ($tenants as $tenant) {
        //             $newConnectionName = str_replace(".", "_", $tenant->domain_name);
        //             $databaseName = str_replace(".", "_", $tenant->domain_name);
        //             establishDatabaseConnect($newConnectionName, $databaseName);
        //             DB::reconnect($newConnectionName);

        //             LiveAgents::on($newConnectionName)->truncate();
        //         }
        //     });

        getDbConnection($this->argument("uin"), true);
        LiveAgents::truncate();
        generateScheduleLogs('info', 'Cleaning end of day data for all tenants completed.');
    }
}
