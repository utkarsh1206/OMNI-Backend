<?php

namespace App\Jobs;

use App\Models\EmailSchedule;
use App\Services\ReportDataService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class SendScheduledEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        public int $scheduleId,
        public string $connectionName
    ) {
        $this->scheduleId = $scheduleId;
        $this->connectionName = $connectionName;

        generateScheduleLogs(
            'info',
            'Email Scheduled Job Created test'.
            json_encode([
                'schedule_id' => $this->scheduleId,
                'connection'  => $this->connectionName
            ])
        );
    }

    public function handle(): void
    {
        $scheduleId = $this->scheduleId;
        generateScheduleLogs(
            'info',
            'Inside JObs Handle start' . $scheduleId
        );

        $schedule = EmailSchedule::on($this->connectionName)
            ->findOrFail($this->scheduleId);

            generateScheduleLogs(
                'info',
                'Get Scheduled data' . json_encode(['schedule' => $schedule])
            );

        $data = app(ReportDataService::class)->generate(
            $this->connectionName,
            $schedule->report_name,
            $schedule->selected_columns,
            $schedule->data_start_time,
            $schedule->data_end_time
        );

        generateScheduleLogs(
            'info',
            'Report data generated'. 
            json_encode([
                'data' => $data
            ])
        );

        // 🔔 Enable when ready
        // Mail::to($schedule->to_emails)->send(new ScheduledReportMail($schedule, $data));
    }
}
