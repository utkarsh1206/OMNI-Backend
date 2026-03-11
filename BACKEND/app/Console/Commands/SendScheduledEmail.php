<?php

namespace App\Console\Commands;

use App\Jobs\SendScheduledEmailJob;
use App\Models\EmailSchedule;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $now = now(env('APP_CSVTIMEZONE'));

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

                        if (!Schema::connection($connectionName)->hasTable('email_schedules')) {
                            generateScheduleLogs(
                                'info',
                                'email_schedules table not found',
                                json_encode(['tenant' => $connectionName])
                            );

                            continue;
                        }

                        $schedules = EmailSchedule::on($connectionName)
                            ->where('is_active', 1)
                            ->get();

                        $schedules->each(function ($schedule) use ($now, $connectionName) {
                            $nowTz = now(env('APP_CSVTIMEZONE'));

                            $scheduledDateTime = $nowTz->copy()
                                ->setTimeFromTimeString($schedule->scheduled_time);

                            // Not yet reached scheduled time
                            if ($nowTz->lt($scheduledDateTime)) {
                                return;
                            }

                            // Prevent duplicate execution
                            if ($schedule->last_executed_at) {
                                $lastRun = Carbon::parse($schedule->last_executed_at, env('APP_CSVTIMEZONE'));

                                if ($lastRun->gte($scheduledDateTime)) {
                                    return;
                                }
                            }

                            /*Date range calculation*/
                            $startDate = null;
                            $endDate   = null;
                            if ($schedule->frequency === 'daily') {
                                $startDate = $nowTz->copy()
                                    ->setTimeFromTimeString($schedule->data_start_time);

                                $endDate = $nowTz->copy()
                                    ->setTimeFromTimeString($schedule->data_end_time);
                            }

                            if ($schedule->frequency === 'weekly') {
                                if (strtolower($nowTz->format('D')) !== strtolower($schedule->day_of_week)) {
                                    return;
                                }

                                $endTime = Carbon::parse($schedule->data_end_time)
                                    ->format('H:i:s');

                                $startDate = $nowTz->copy()
                                    ->subDays(7)
                                    ->setTimeFromTimeString($endTime);

                                $endDate = $nowTz->copy()
                                    ->setTimeFromTimeString($endTime);
                            }

                            if ($schedule->frequency === 'monthly') {
                                if (!$schedule->monthly_date) {
                                    return;
                                }

                                // Adjust for months with fewer days
                                $targetDay = min(
                                    $schedule->monthly_date,
                                    $nowTz->daysInMonth
                                );

                                if ($nowTz->day != $targetDay) {
                                    return;
                                }

                                $endTime = Carbon::parse($schedule->data_end_time)
                                    ->format('H:i:s');

                                $startDate = $nowTz->copy()
                                    ->subMonth()
                                    ->setTimeFromTimeString($endTime);

                                $endDate = $nowTz->copy()
                                    ->setTimeFromTimeString($endTime);
                            }

                            if (!$startDate || !$endDate) {
                                generateScheduleLogs('error', "StartDate or Enddate not found");
                                return;
                            }

                            $payload = [
                                'startDate' => $startDate->format('Y-m-d H:i:s'),
                                'endDate'   => $endDate->format('Y-m-d H:i:s'),
                                'page'      => 'all',
                                'selected_columns' => array_values($schedule->selected_columns ?? [])
                            ];

                            generateScheduleLogs(
                                'info',
                                'payload ',
                                json_encode($payload)
                            );

                            // Dispatch job with ID + connection (SAFE)
                            dispatch(new SendScheduledEmailJob(
                                $schedule,
                                $connectionName,
                                $payload
                            ));

                            /* Update last executed */
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
}
