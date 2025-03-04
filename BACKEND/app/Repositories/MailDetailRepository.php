<?php

namespace App\Repositories;
use App\Models\MailDetail;
use App\Http\Response\CustomApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use App\Models\EmailConfigure;
use App\Models\SupportTicketnotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Log;

class MailDetailRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function index(Request $request)
    {
        $supportticketId = $request->input('supportticket_id');
        $mailDetails = MailDetail::where('supportticket_id', $supportticketId)->orderBy('id', 'desc')->get();
        return $this->apiResponse->getResponseStructure(trans('message.success'), $mailDetails, trans('message.data_fetch'));
    }

    public function store(Request $request)
    {
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
        $notification = SupportTicketNotification::create([
            'type' => isset($request->default_status) ? $request->default_status : "",
            'comment' => "",
            'support_ticket_id' => $request->supportticket_id,
            'date' =>gmdate('Y-m-d H:i:s'),
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
            return $this->apiResponse->handleAndResponseException($e);
        }

        return $this->apiResponse->getResponseStructure(trans('message.success'), $mailDetail, trans('message.email_sent_success'), ResponseHTTP::HTTP_CREATED);
    }

   

}
