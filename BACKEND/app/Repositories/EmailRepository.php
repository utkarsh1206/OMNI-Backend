<?php

namespace App\Repositories;

use App\Models\EmailConfigure;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Mail\SendEmail;

class EmailRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function index($validated)
    {
        // Retrieve SMTP settings from the database
        $emailConfig = EmailConfigure::where('id', $validated['configure_id'])->first();
        $smtpHost = $emailConfig->smtp_host;
        $smtpPort = $emailConfig->smtp_port;
        $smtpUser = $emailConfig->smtp_user;
        $smtpPassword = $emailConfig->smtp_password;
        $sender_name = $emailConfig->sender_name;
        $sender_email = $emailConfig->sender_email;

        // Configure SMTP dynamically
        config([
            'mail.mailers.smtp.host' =>  $smtpHost,
            'mail.mailers.smtp.port' => $smtpPort,
            'mail.mailers.smtp.username' => $smtpUser,
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
            'mail.from.address' => $sender_email,
            'mail.from.name' => $sender_name,
        ]);

        try {
            // Attempt to send the email
            Mail::to($validated['to'])->send(new SendEmail($validated['subject'], $validated['content'], ""));
            // Log success (optional)
            generateLogs('info', 'Email sent successfully: ', "Email sent successfully to {$validated['to']} with subject: {$validated['subject']}");
            return $this->apiResponse->getResponseStructure(trans('message.success'), [], trans('message.email_sent_success'));
        } catch (Exception $e) {
            // Return a response indicating failure
            generateLogs('error', 'Error in generating Email  List: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
