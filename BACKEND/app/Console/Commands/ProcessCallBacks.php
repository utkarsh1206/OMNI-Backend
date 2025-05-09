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
    protected $signature = 'app:process-callbacks';
 
    protected $description = 'Process call backs and update custom lead tables directly';

    public function handle()
    {
        Tenant::orderBy('id')  // Ensures consistent ordering by the ID column
            ->chunkById(100, function ($tenants) {
                foreach ($tenants as $tenant) {
                    $newConnectionName = str_replace(".", "_", $tenant->domain_name);
                    $databaseName = str_replace(".", "_", $tenant->domain_name);
                    $this->establishDatabaseConnect($newConnectionName, $databaseName);
                    DB::reconnect($newConnectionName);
                    $this->updateCustomLead($newConnectionName);
                }
            });
        return Command::SUCCESS;
    }

    public function updateCustomLead($newConnectionName)
    {
        try {
            $now = Carbon::now()->format('Y-m-d H:i:00');

            $callbacks = CallBack::on($newConnectionName)
                ->select('call_back_time', 'lead_id', 'list_id')
                ->where('call_back_time', $now)
                ->get();

            foreach ($callbacks as $callback) {
                $listId = $callback->list_id;
                $leadId = $callback->lead_id;
                $callbackTime = $callback->call_back_time;

                $this->info("Processing lead_id: $leadId from list_id: $listId");

                // Find the corresponding custom lead table
                $tableName = "custom_lead_{$listId}";

                if (Schema::connection($newConnectionName)->hasTable($tableName)) {
                    $updated = DB::connection($newConnectionName)
                        ->table($tableName)
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

    protected function establishDatabaseConnect($connectionName, $databaseName)
    {
        // Placeholder — replace with your actual logic
        config([
            "database.connections.$connectionName" => [
                'driver' => 'mysql',
                'host' => env('DB_HOST'),
                'port' => env('DB_PORT'),
                'database' => $databaseName,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]
        ]);
    }
}
