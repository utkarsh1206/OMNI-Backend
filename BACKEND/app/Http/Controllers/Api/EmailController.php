<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Mail\SendEmail;
use App\Models\EmailConfigure;


class EmailController extends Controller
{
    /**
     * Send an email via the API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail(Request $request)
    {
        // Validate the incoming request (subject, content, and recipient)
        $validated = $request->validate([
            'configure_id' => 'required|int',
            'subject' => 'required|string',
            'content' => 'required|string',
            'to' => 'required|email',
        ]);

        // Configure SMTP settings dynamically
        /*
        config([
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.port' => 465,
            'mail.mailers.smtp.username' => 'teamneox123@gmail.com',
            'mail.mailers.smtp.password' => 'Admin@123',
            'mail.mailers.smtp.encryption' => 'T:S',
            'mail.from.address' => 'teamneox123@gmail.com',
            'mail.from.name' => 'Neox',
        ]);*/
        $emailConfig = EmailConfigure::where('id', $validated['configure_id'])->first();
        $smtpHost = $emailConfig->smtp_host;
        $smtpPort = $emailConfig->smtp_port;
        $smtpUser = $emailConfig->smtp_user;
        $smtpPassword = $emailConfig->smtp_password;
        $sender_name = $emailConfig->sender_name;
        $sender_email = $emailConfig->sender_email;

        config([
            'mail.mailers.smtp.host' =>  $smtpHost,
            'mail.mailers.smtp.port' => $smtpPort,
            'mail.mailers.smtp.username' => $smtpUser,
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort==587)?'TLS':'SSL',
            'mail.from.address' => $sender_email,
            'mail.from.name' =>$sender_name,
        ]);
        try {
            // Attempt to send the email
            Mail::to($validated['to'])->send(new SendEmail($validated['subject'], $validated['content'],""));

            // Log success (optional)
            Log::info("Email sent successfully to {$validated['to']} with subject: {$validated['subject']}");

            return response()->json(['message' => 'Email sent successfully']);
        } catch (Exception $e) {
            // Log the error message if email fails to send
            Log::error('Failed to send email', [
                'error' => $e->getMessage(),
                'recipient' => $validated['to'],
                'subject' => $validated['subject'],
            ]);

            // Return a response indicating failure
            return response()->json(['message' => 'Failed to send email', 'error' => $e->getMessage()], 500);
        }
    }
}
