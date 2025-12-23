<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\CallBack;
use Exception;

class ProcessCallBacks extends Command
{
    protected $signature = 'app:process-callbacks {uin?}';

    protected $description = 'Process call backs and update custom lead tables directly';

    public function handle()
    {
        $uin = $this->argument('uin');
        generateScheduleLogs('info', "Process call backs and update custom lead tables directly started for $uin ");
        getDbConnection($uin, true);
        $this->updateCustomLead();
        // Tenant::orderBy('id')  // Ensures consistent ordering by the ID column
        //     ->chunkById(100, function ($tenants) {
        //         foreach ($tenants as $tenant) {
        //             $newConnectionName = str_replace(".", "_", $tenant->domain_name);
        //             $databaseName = str_replace(".", "_", $tenant->domain_name);
        //             establishDatabaseConnect($newConnectionName, $databaseName);
        //             DB::reconnect($newConnectionName);
        //             $this->updateCustomLead($newConnectionName);
        //         }
        //     });
        generateScheduleLogs('info', "Process call backs and update custom lead tables directly completed for $uin ");
        return Command::SUCCESS;
    }

    public function updateCustomLead()
    {
        try {
            $now = Carbon::now()->format('Y-m-d H:i:00');

            $callbacks = CallBack::select('call_back_time', 'lead_id', 'list_id')
                ->where('call_back_time', $now)
                ->get();

            foreach ($callbacks as $callback) {
                $listId = $callback->list_id;
                $leadId = $callback->lead_id;
                $callbackTime = $callback->call_back_time;

                $this->info("Processing lead_id: $leadId from list_id: $listId");

                // Find the corresponding custom lead table
                $tableName = "custom_lead_{$listId}";

                if (Schema::hasTable($tableName)) {
                    $updated = DB::table($tableName)
                        ->where('id', $leadId)
                        ->update(['updated_at' => $callbackTime]);

                    if ($updated) {
                        $this->info("Updated lead_id $leadId in $tableName with updated_at = $callbackTime");
                    } else {
                        $this->warn("Lead ID $leadId not found in $tableName.");
                    }
                } else {
                    $this->warn("Table $tableName does not exist.");
                }
            }
        } catch (Exception $e) {
            generateLogs('error', 'Error Updating Custom Leads : ', $e->getMessage());
            exit;
        }
    }
}
