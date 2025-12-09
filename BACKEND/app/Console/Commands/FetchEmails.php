<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Jobs\ProcessTenantEmailsJob;
use App\Models\Detail;

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch {uin?}';
    protected $description = 'Dispatch jobs to fetch unread emails per tenant';

    public function handle()
    {
        $commandRunId = uniqid('cmd_', true);

        $uin = $this->argument('uin');

        if (!$uin) {
            generateScheduleLogs("info", "No UIN provided");
            return;
        }

        generateScheduleLogs(
            "info",
            "Running Fetch Email command " . json_encode([
                'command_run_id' => $commandRunId,
                'uin' => $uin,
            ])
        );

        $detail = Detail::select('domain')->where('uin', $uin)->first();

        if (!$detail) {
            generateScheduleLogs("info", "No Tenant found for the provided UIN");
            return;
        }

        $tenant = Tenant::select('id', 'domain_name')->where('domain_name', $detail->domain)->first();

        if (!$tenant) {
            generateScheduleLogs("info", "Tenant Not found");
            return;
        }

        generateScheduleLogs(
            "info",
            "Dispatching ProcessTenantEmailsJob" . json_encode([
                'command_run_id' => $commandRunId,
                'tenant_id'      => $tenant->id,
                'domain_name'    => $tenant->domain_name,
            ])
        );

        ProcessTenantEmailsJob::dispatch($tenant, $commandRunId);

        generateScheduleLogs(
            "info",
            "FetchEmails Command finished dispatching jobs" . json_encode([
                'command_run_id' => $commandRunId,
            ])
        );
    }
}
