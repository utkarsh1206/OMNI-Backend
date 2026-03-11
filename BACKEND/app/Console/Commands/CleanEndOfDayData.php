<?php

namespace App\Console\Commands;

use App\Models\LiveAgents;
use App\Events\AgentForceLogout;
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
    protected $description = 'Cleans live agent data and notifies frontend to logout.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uin = $this->argument('uin');

        if (!$uin) {
            $this->error('UIN is required.');
            return;
        }

        generateScheduleLogs('info', "Cleaning end of day data for tenant $uin started.");

        $tenantDomain = getDomainByUin($uin);

        if (!$tenantDomain) {
            generateScheduleLogs('error', "Could not find domain for UIN: $uin. Aborting cleanup.");
            $this->error("Domain not found for UIN $uin");
            return;
        }

        getDbConnection($uin, true);

        // LiveAgents::chunk(100, function ($agents) use ($tenantDomain) {
        //     foreach ($agents as $agent) {
        //         broadcast(new AgentForceLogout($agent->user_id, $tenantDomain));

        //         generateScheduleLogs(
        //             'info',
        //             "Broadcasted logout for User ID: {$agent->user_id} on domain: {$tenantDomain}"
        //         );
        //     }
        // });
        // LiveAgents::query()->delete();

        // generateScheduleLogs('info', "Cleaning end of day data for tenant $uin completed.");
        // $this->info("Successfully logged out agents and cleaned table for $uin.");

        $agentIds = LiveAgents::pluck('user_id');

        if ($agentIds->isNotEmpty()) {
            foreach ($agentIds as $userId) {
                broadcast(new AgentForceLogout($userId, $tenantDomain));
            }

            generateScheduleLogs('info', "Broadcasted logout to " . $agentIds->count() . " agents.");
        }
        LiveAgents::truncate();

        generateScheduleLogs('info', "Cleaning end of day data for tenant $uin completed. Table truncated.");
        $this->info("Successfully reset LiveAgents for $uin.");
    }
}
