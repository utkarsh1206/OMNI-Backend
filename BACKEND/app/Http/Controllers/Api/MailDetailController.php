<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MailDetail;
use App\Models\EmailConfigure;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Log;
use Exception;



class MailDetailController extends Controller
{
    public function index(Request $request)
    {
        // Validate the incoming request to ensure 'supportticket_id' is provided
        $request->validate([
            'supportticket_id' => 'required|integer', // Assuming you have a support_tickets table
        ]);
    
        // Get the supportticket_id from the request body
        $supportticketId = $request->input('supportticket_id');
    
        // Filter the records by supportticket_id
        $mailDetails = MailDetail::where('supportticket_id', $supportticketId)->orderBy('id', 'desc')->get();
    
        // Return the records as a JSON response
        return response()->json($mailDetails);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'user_id' => 'required|integer|exists:users,id', // assuming you have a users table
            'default_type' => 'required|string',
            'default_priority' => 'required|string',
            'default_status' => 'required|string',
            'to' => 'required|email',
            'cc' => 'nullable|email',
            'bcc' => 'nullable|email',
            'body' => 'required|string',
        ]);
        $attachment_urls = [];
        $mail_attachment_urls = [];
        if ($request->hasFile('attachment')) {
            $files = $request->file('attachment');
            
            if (is_array($files)) {
                foreach ($files as $attachment) {
                    $filename = now()->format('Y_m_d_H_i_s') . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $path = $attachment->storeAs('support_ticket', $filename, 'public');
                    $attachment_urls[] = url('storage/' . $path);
                    $mail_attachment_urls[] = [
                        'path' => storage_path('app/public/' . $path),
                        'name' => $filename,
                        'mime' => $attachment->getMimeType()
                    ];
                }
            } else {
                $filename = now()->format('Y_m_d_H_i_s') . '_' . uniqid() . '.' . $files->getClientOriginalExtension();
                $path = $files->storeAs('support_ticket', $filename, 'public');
                $attachment_urls[] = url('storage/' . $path);
                $mail_attachment_urls[] = [
                    'path' => storage_path('app/public/' . $path),
                    'name' => $filename,
                    'mime' => $files->getMimeType()
                ];
            }
        }
        $mailDetail = MailDetail::create([
            'supportticket_id'=>$request->supportticket_id,
            'date' =>gmdate('Y-m-d H:i:s'),
            'user_id' => $request->user_id,
            'type' => $request->default_type,
            'priority' => $request->default_priority,
            'status' => $request->default_status,
            'to' => $request->to,
            'cc' => $request->cc,
            'bcc' => $request->bcc,
            'body' => $request->body,
            'attachment' => json_encode($attachment_urls),
        ]);
        $emailConfig = EmailConfigure::where('type', 'gmail')->first();
        $smtpHost = $emailConfig->smtp_host;
        $smtpPort = $emailConfig->smtp_port;
        $smtpUser = $emailConfig->smtp_user;
        $smtpPassword = $emailConfig->smtp_password;
        $sender_name = $emailConfig->sender_name;
        $sender_email = $emailConfig->sender_email;
  
        config([
            'mail.mailers.smtp.host' => $smtpHost,
            'mail.mailers.smtp.port' => $smtpPort,
            'mail.mailers.smtp.username' => $smtpUser,
            'mail.mailers.smtp.password' =>$smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort==587)?'TLS':'SSL',
            'mail.from.address' =>  $sender_email,
            'mail.from.name' => $sender_name,
        ]);
        // Attempt to send the email
        try {
            Mail::to($request->to)->send(new SendEmail("Test support mail sent", $request->body,$mail_attachment_urls));
        } catch (Exception $e) {
            // Log the error message if email fails to send
            Log::error('Failed to send email', [
                'error' => $e->getMessage(),
                'recipient' =>$request->to_emails,
                'subject' => $request->subject,
            ]);

            // Return a response indicating failure
            return response()->json(['message' => 'Failed to send email', 'error' => $e->getMessage()], 500);
        }

        // Return the created record with a success message
        return response()->json([
            'message' => 'Mail sent successfully.',
            'data' => $mailDetail
        ], 201);
    }
}
