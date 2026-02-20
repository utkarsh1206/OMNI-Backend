<?php

namespace App\Jobs;

use App\Mail\ScheduledReportMail;
use App\Models\EmailSchedule;
use App\Services\ReportDataService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendScheduledEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public function __construct(
        public EmailSchedule $schedule,
        public string $connectionName,
        public array $payload
    ) {
        $this->schedule = $schedule;
        $this->connectionName = $connectionName;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        setEmailConfig($this->connectionName);

        // Important for multi-database setups
        $schedule = $this->schedule->setConnection($this->connectionName);

        $dataResponse = app(ReportDataService::class)->generate(
            $this->connectionName,
            $schedule->report_name,
            $this->payload
        );

        $data = $dataResponse->getData(true);
        generateScheduleLogs(
            'info',
            'Report data generated' .
                json_encode([
                    'data' => $data
                ])
        );

        try {
            Mail::to($schedule->to_emails ?? [])
                ->cc($schedule->cc_emails ?? [])
                ->bcc($schedule->bcc_emails ?? [])
                ->queue(new ScheduledReportMail($schedule, $data));
            generateScheduleLogs(
                'info',
                'Scheduled email sent successfully', json_encode($schedule)
            );
        } catch (\Throwable $e) {
            generateScheduleLogs(
                'error',
                'Scheduled email failed: ' . $e->getMessage(),
            );
            throw $e;
        }
    }
}
