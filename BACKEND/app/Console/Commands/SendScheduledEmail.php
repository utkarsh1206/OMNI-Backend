<?php

namespace App\Console\Commands;

use App\Jobs\SendScheduledEmailJob;
use App\Models\EmailSchedule;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendScheduledEmail extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:send-scheduled-email';

    /**
     * The console command description.
     */
    protected $description = 'Send scheduled report emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now(); // ONE immutable reference

        Tenant::orderBy('id')
            ->chunkById(100, function ($tenants) use ($now) {

                foreach ($tenants as $tenant) {
                    try {
                        generateScheduleLogs(
                            'info',
                            'Send scheduled email started' .
                                json_encode(['tenant' => $tenant->domain_name])
                        );

                        $connectionName = str_replace('.', '_', $tenant->domain_name);
                        $databaseName   = $connectionName;

                        establishDatabaseConnect($connectionName, $databaseName);
                        DB::reconnect($connectionName);

                        EmailSchedule::on($connectionName)
                            ->where('is_active', 1)
                            ->get()
                            ->each(function ($schedule) use ($now, $connectionName) {

                                if (!$this->shouldRun($schedule, $now)) {
                                    return;
                                }

                                generateScheduleLogs(
                                    'info',
                                    'Dispatching SendScheduledEmailJob' .
                                        json_encode([
                                            'schedule_id' => $schedule->id,
                                            'connection'  => $connectionName
                                        ])
                                );

                                // Dispatch job with ID + connection (SAFE)
                                dispatch(new SendScheduledEmailJob(
                                    $schedule->id,
                                    $connectionName
                                ));

                                // Update using explicit connection
                                EmailSchedule::on($connectionName)
                                    ->where('id', $schedule->id)
                                    ->update([
                                        'last_executed_at' => $now
                                    ]);
                            });
                    } catch (Exception $e) {
                        generateScheduleLogs(
                            'error',
                            'Skipping tenant due to error',
                            json_encode([
                                'tenant' => $tenant->domain_name,
                                'error'  => $e->getMessage()
                            ])
                        );
                    }
                }
            });
    }

    /**
     * Determine whether the schedule should run now
     */
    private function shouldRun($schedule, $now)
    {
        if (app()->environment(['local', 'testing'])) {
            return true;
        }
        
        $nowTz = $now->copy()->setTimezone($schedule->timezone);

        // Compare only H:i (ignore seconds)
        if ($nowTz->format('H:i') !== substr($schedule->scheduled_time, 0, 5)) {
            return false;
        }

        // Prevent multiple runs in same minute
        if ($schedule->last_executed_at) {
            $lastRun = \Carbon\Carbon::parse($schedule->last_executed_at)
                ->setTimezone($schedule->timezone);

            if ($lastRun->format('Y-m-d H:i') === $nowTz->format('Y-m-d H:i')) {
                return false;
            }
        }

        if ($schedule->frequency === 'weekly') {
            return strtolower($nowTz->format('D')) === strtolower($schedule->day_of_week);
        }

        return true;
    }
}
