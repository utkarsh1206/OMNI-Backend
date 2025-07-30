<?php

namespace App\Repositories;

use App\Models\MailDetail;
use App\Http\Response\CustomApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use App\Models\EmailConfigure;
use App\Models\SupportTicketNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;

class MailDetailRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function index(Request $request)
    {
        try {
            $supportticketId = $request->input('supportticket_id');
            $mailDetails = MailDetail::where('supportticket_id', $supportticketId)->orderBy('id', 'desc')->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $mailDetails, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating failed MailDetail List: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        // Helper function to handle file upload and return attachment URLs
        $handleAttachments = function ($files) {
            $attachmentUrls = [];
            $mailAttachmentUrls = [];

            // Handle multiple files
            foreach ($files as $attachment) {
                $filename = now()->format('Y_m_d_H_i_s') . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                $path = $attachment->storeAs('support_ticket', $filename, 'public');
                $attachmentUrls[] = url('storage/' . $path);
                $mailAttachmentUrls[] = [
                    'path' => storage_path('app/public/' . $path),
                    'name' => $filename,
                    'mime' => $attachment->getMimeType(),
                ];
            }

            return [$attachmentUrls, $mailAttachmentUrls];
        };

        // If 'attachment' exists in the request, handle it
        $attachmentUrls = [];
        $mailAttachmentUrls = [];
        if ($request->hasFile('attachment')) {
            $files = $request->file('attachment');
            // Ensure files is an array, whether single or multiple files
            $files = is_array($files) ? $files : [$files];
            [$attachmentUrls, $mailAttachmentUrls] = $handleAttachments($files);
        }

        // Store the mail detail
        $mailDetail = MailDetail::create([
            'supportticket_id' => $request->supportticket_id,
            // 'date' => gmdate('Y-m-d H:i:s'),
            'date' => date('Y-m-d H:i:s'),
            // 'user_id' => $request->user_id,
            "user_id" => auth()->user()->id,
            'type' => $request->default_type,
            'priority' => $request->default_priority,
            'status' => $request->default_status,
            'to' => $request->to,
            'cc' => $request->cc,
            'bcc' => $request->bcc,
            'body' => $request->body,
            'attachment' => json_encode($attachmentUrls),
        ]);

        // Store the notification for the support ticket
        SupportTicketNotification::create([
            'type' => $request->default_status ?? "",
            'comment' => "",
            'support_ticket_id' => $request->id,
            'date' => gmdate('Y-m-d H:i:s'),
            'created_by' => auth()->user()->id,
            'status' => '1',
            'comment' => "Ticket Replied",
            'icon' => 'cilSend',
            'color' => 'info',
        ]);

        // Get email configuration settings
        // $emailConfig = EmailConfigure::where('type', 'gmail')->first();
        // generateLogs('info', 'Email configuration fetched ' . json_encode($emailConfig));
        // // Update mail configuration dynamically based on the stored settings
        // config([
        //     'mail.default' => 'smtp',
        //     'mail.mailers.smtp.host' => $emailConfig->smtp_host,
        //     'mail.mailers.smtp.port' => $emailConfig->smtp_port,
        //     'mail.mailers.smtp.username' => $emailConfig->smtp_user,
        //     'mail.mailers.smtp.password' => $emailConfig->smtp_password,
        //     'mail.mailers.smtp.encryption' => $emailConfig->smtp_port == 587 ? 'TLS' : 'SSL',
        //     'mail.from.address' => $emailConfig->sender_email,
        //     'mail.from.name' => $emailConfig->sender_name,
        // ]);
        // generateLogs('info', 'Mail configuration updated dynamically');

        setEmailConfig();

        // Attempt to send the email
        try {
            generateLogs('info', 'Sending email to: ' . $request->to);
            Mail::to($request->to)->send(new SendEmail($request->subject, $request->body, $mailAttachmentUrls));
        } catch (Exception $e) {
            generateLogs('error', 'Error in sending email: ' . $e->getMessage());
            logMessage("error", $e->getMessage(), [
                'error' => $e->getMessage(),
                'recipient' => $request->to,
                'subject' => $request->subject,
            ]);
            return $this->apiResponse->handleAndResponseException($e);
        }
        generateLogs('info', 'Email sent successfully to: ' . $request->to);
        // Return a successful response
        return $this->apiResponse->getResponseStructure(
            trans('message.success'),
            $mailDetail,
            trans('message.email_sent_success'),
            ResponseHTTP::HTTP_CREATED
        );
    }
}
