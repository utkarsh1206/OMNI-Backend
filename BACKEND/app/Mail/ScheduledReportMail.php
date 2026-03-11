<?php

namespace App\Mail;

use App\Models\EmailSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailSchedule $schedule,
        public array $data
    ) {}

    public function build()
    {
        generateScheduleLogs(
            "info",
            "inside ScheduledReportMail " . json_encode($this->data)
        );

        $fileUrl = $this->data['data']['file_url'] ?? null;

        if (!$fileUrl) {
            throw new \Exception('File URL not found in report data.');
        }

        $fileName = basename($fileUrl);

        // Generate temporary signed download URL
        $signedUrl = URL::temporarySignedRoute(
            'exports.download',
            now()->addHours(24),
            ['filename' => $fileName]
        );

        // $filePath = Storage::disk('public')
        //     ->path('exports/' . $fileName);

        return $this->subject($this->schedule->subject ?? 'Scheduled Report')
            ->view('emails.scheduled-report')
            ->with([
                'schedule' => $this->schedule,
                'data'     => $this->data,
                'downloadUrl' => $signedUrl
            ]);
            // ->attach($filePath);
    }
}
