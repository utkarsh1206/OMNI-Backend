<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\LiveAgents;
use Exception;

class UpdateLiveAgentOnNetworkLost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-live-agent-on-network-lost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the live agent from the database if network is lost';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Tenant::orderBy('id') 
            ->chunkById(100, function ($tenants) {
                foreach ($tenants as $tenant) {
                    $newConnectionName = str_replace(".", "_", $tenant->domain_name);
                    getDbConnection($tenant->domain_name);
                    DB::reconnect($newConnectionName);
                    $this->updateLiveAgents($newConnectionName);
                }
            });
        return Command::SUCCESS;
    }

    public function updateLiveAgents($newConnectionName)
    {
        try {
            $thresholdTime = Carbon::now()->subHour(); 

            $staleAgents = LiveAgents::on($newConnectionName)
                ->where('status_last_updated_at', '<', $thresholdTime)
                ->get();

            $count = $staleAgents->count();

            if ($count > 0) {
                foreach ($staleAgents as $agent) {
                    $this->info("Deleting LiveAgent ID {$agent->id} - Last updated at: {$agent->status_last_updated_at}");
                    $agent->delete();
                }

                $this->info("Deleted $count LiveAgent(s) inactive for more than 1 hour.");
            } else {
                $this->info("No inactive LiveAgents found for deletion.");
            }
        } catch (Exception $e) {
            generateLogs('error', 'Error deleting inactive LiveAgents: ', $e->getMessage());
        }
    }
}
