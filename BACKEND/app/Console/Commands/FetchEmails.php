<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Jobs\ProcessTenantEmailsJob;

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch';
    protected $description = 'Dispatch jobs to fetch unread emails per tenant';

    public function handle()
    {
        $commandRunId = uniqid('cmd_', true);

        generateLogs("info", "FetchEmails Command started" .json_encode([
            'command_run_id' => $commandRunId,
        ]));

        Tenant::orderBy('id')->chunkById(100, function ($tenants) use ($commandRunId) {
            foreach ($tenants as $tenant) {
                generateLogs("info", "Dispatching ProcessTenantEmailsJob" .json_encode([
                    'command_run_id' => $commandRunId,
                    'tenant_id'      => $tenant->id,
                    'domain_name'    => $tenant->domain_name,
                ]));

                ProcessTenantEmailsJob::dispatch($tenant, $commandRunId);
            }
        });

        generateLogs("info", "FetchEmails Command finished dispatching jobs" .json_encode([
            'command_run_id' => $commandRunId,
        ]));
    }
}
