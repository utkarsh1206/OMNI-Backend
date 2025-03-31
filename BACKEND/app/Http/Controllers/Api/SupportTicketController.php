<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TenantsResource;
use App\Models\SupportTicket;
use App\Models\MailDetail;
use App\Models\EmailConfigure;
use Illuminate\Support\Facades\Validator;
use App\Models\SupportTicketNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Mail\SendEmail;
use App\Services\SlaService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use App\Models\MailTemplate;
use App\Repositories\SupportTicketRepository;

class SupportTicketController extends Controller
{
    protected $slaService;

    private $slaReportRepository;

    // Constructor to inject the SlaService
    public function __construct(SlaService $slaService, SupportTicketRepository $slaReportRepository)
    {
        $this->slaService = $slaService;
        $this->slaReportRepository = $slaReportRepository;
    }

    public function index(Request $request)
    {
        return $this->slaReportRepository->list($request);
    }

    public function store(Request $request)
    {
        $apiUser = auth()->user();
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'to_emails' => 'required',
            'subject' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
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
        $supportticket = SupportTicket::create([
            'support_ticket_id' => gmdate('ymdhis'),
            'product_id' => isset($request->product_id) ? $request->product_id : 0,
            'campaign_id' => isset($request->campaign_id) ? $request->campaign_id : 0,
            'inbound_group_id' => isset($request->inbound_group_id) ? $request->inbound_group_id : 0,
            'contact_id' => isset($request->contact_id) ? $request->contact_id : 0,
            'assign_to_id' => isset($request->assign_to_id) ? $request->assign_to_id : 0,
            'default_type' => isset($request->default_type) ? $request->default_type : "",
            'default_priority' => isset($request->default_priority) ? $request->default_priority : "",
            'default_status' => isset($request->default_status) ? $request->default_status : "",
            'subject' => isset($request->subject) ? $request->subject : "",
            'department_id' => isset($request->department_id) ? $request->department_id : "",
            'to_emails' => isset($request->to_emails) ? $request->to_emails : "",
            'cc' => isset($request->cc) ? $request->cc : "",
            'bcc' => isset($request->bcc) ? $request->bcc : "",
            'description' => isset($request->description) ? $request->description : "",
            'attachment' => json_encode($attachment_urls),
            'incident_id' => isset($request->incident_id) ? $request->incident_id : 0,
            'account_id' => isset($request->account_id) ? $request->account_id : 0,
        ]);

        $datePrefix = now()->format('dmy'); // Get the current date in ddmmyy format
        $supportTicketId = $datePrefix . $supportticket->id; // Use the newly inserted ID
        $mail_details = MailDetail::create([
            'default_type' => isset($request->default_type) ? $request->default_type : "",
            'user_id' => $apiUser->id ?? '',
            'priority' => isset($request->default_priority) ? $request->default_priority : "",
            'status' => isset($request->default_status) ? $request->default_status : "",
            'to' => isset($request->to_emails) ? $request->to_emails : "",
            'cc' => isset($request->cc) ? $request->cc : "",
            'bcc' => isset($request->bcc) ? $request->bcc : "",
            'body' => isset($request->description) ? $request->description : "",
            'attachment' => json_encode($attachment_urls),
            'supportticket_id' => $supportTicketId,
            'date' => gmdate('Y-m-d H:i:s')
            // 'user_id' => isset($request->assign_to_id) ? $request->assign_to_id : 0,
        ]);

        $supportticket->update(['support_ticket_id' => $supportTicketId]);
        $comment = $apiUser->first_name . " " . $apiUser->last_name . " has created the ticket";
        $this->createTicket($supportticket, "Ticket Created", $comment, $apiUser);
        $this->sendSms($apiUser, "Ticket Created", $apiUser->mobile, $supportticket);
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
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
            'mail.from.address' =>  $sender_email,
            'mail.from.name' => $sender_name,
        ]);
        // Attempt to send the email

        try {
            Mail::to($request->to_emails)->send(new SendEmail($request->subject, $request->description, $mail_attachment_urls));
            logEmail([
                "user_id" => $request->assign_to_id,
                "email_template_id" => 0,
                "from_email" => $sender_email,
                "module_name" => "Support Ticket",
                "entity_id" => $supportticket->id,
                "status" => "sent",
            ]);
        } catch (Exception $e) {
            // Log the error message if email fails to send
            Log::error('Failed to send email', [
                'error' => $e->getMessage(),
                'recipient' => $request->to_emails,
                'subject' => $request->subject,
            ]);

            // Return a response indicating failure
            return response()->json(['message' => 'Failed to send email', 'error' => $e->getMessage()], 500);
        }
        return response()->json([
            'message' => 'Support Ticket Created Successfully',
            'data' => new TenantsResource($supportticket)
        ]);
    }
    
    public function show(SupportTicket $supportticket)
    {
        return new TenantsResource($supportticket);
    }

    public function update(Request $request, SupportTicket $supportticket)
    {
        $apiUser = auth()->user();
        $attachment_urls = [];
        $mail_attachment_urls = [];
        if ($request->hasFile('attachment')) {
            $files = $request->file('attachment');

            if (is_array($files)) {
                foreach ($files as $attachment) {
                    $filename = now()->format('Y_m_d_H_i_s') . '_' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                    $path = $attachment->storeAs('attachments', $filename, 'public');
                    $attachment_urls[] = url('storage/' . $path);
                    $mail_attachment_urls[] = [
                        'path' => storage_path('app/public/' . $path),
                        'name' => $filename,
                        'mime' => $attachment->getMimeType()
                    ];
                }
            } else {
                $filename = now()->format('Y_m_d_H_i_s') . '_' . uniqid() . '.' . $files->getClientOriginalExtension();
                $path = $files->storeAs('attachments', $filename, 'public');
                $attachment_urls[] = url('storage/' . $path);
                $mail_attachment_urls[] = [
                    'path' => storage_path('app/public/' . $path),
                    'name' => $filename,
                    'mime' => $files->getMimeType()
                ];
            }
        }

        $updateData = [
            'product_id' => isset($request->product_id) ? $request->product_id : 0,
            'campaign_id' => isset($request->campaign_id) ? $request->campaign_id : 0,
            'inbound_group_id' => isset($request->inbound_group_id) ? $request->inbound_group_id : 0,
            'contact_id' => isset($request->contact_id) ? $request->contact_id : 0,
            'assign_to_id' => isset($request->assign_to_id) ? $request->assign_to_id : 0,
            'default_type' => isset($request->default_type) ? $request->default_type : "",
            'default_priority' => isset($request->default_priority) ? $request->default_priority : "",
            'default_status' => isset($request->default_status) ? $request->default_status : "",
            'subject' => isset($request->subject) ? $request->subject : "",
            'department_id' => isset($request->department_id) ? $request->department_id : "",
            'to_emails' => isset($request->to_emails) ? $request->to_emails : "",
            'cc' => isset($request->cc) ? $request->cc : "",
            'bcc' => isset($request->bcc) ? $request->bcc : "",
            'description' => isset($request->description) ? $request->description : "",
            'incident_id' => isset($request->incident_id) ? $request->incident_id : 0,
            'account_id' => isset($request->account_id) ? $request->account_id : 0,
        ];
        if (!empty($attachment_urls)) {
            $updateData['attachment'] = $attachment_urls;
        }
        $supportticket->update($updateData);
        $this->createTicket($supportticket, isset($request->default_status) ? $request->default_status : "", "Edit Support Ticket", $apiUser);
        $this->sendSms($apiUser, "Ticket Created", $apiUser->mobile, $supportticket);
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
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
            'mail.from.address' =>  $sender_email,
            'mail.from.name' => $sender_name,
        ]);
        // Attempt to send the email
        try {
            Mail::to($request->to_emails)->send(new SendEmail($request->subject, $request->description, $mail_attachment_urls));
            logEmail([
                "user_id" => $request->assign_to_id,
                "email_template_id" => 0,
                "from_email" => $sender_email,
                "module_name" => "Support Ticket",
                "entity_id" => $supportticket->id,
                "status" => "sent",
            ]);
        } catch (Exception $e) {
            // Log the error message if email fails to send
            Log::error('Failed to send email', [
                'error' => $e->getMessage(),
                'recipient' => $request->to_emails,
                'subject' => $request->subject,
            ]);

            // Return a response indicating failure
            return response()->json(['message' => 'Failed to send email', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Support Ticket Updated Successfully',
            'data' => new TenantsResource($supportticket)
        ]);
    }

    public function statusUpdate(Request $request, SupportTicket $supportTicket)
    {
        return $this->slaReportRepository->statusUpdate($request, $supportTicket);
    }

    public function destroy(SupportTicket $supportTicket)
    {
        return $this->slaReportRepository->delete($supportTicket);
    }
    
    public function createTicket($supportticket, $type, $comment, $apiUser, $statusChange = null)
    {
        $notification = SupportTicketNotification::create([
            'type' => $statusChange ?? $type,
            'status' => $supportticket->default_status,
            'comment' => $comment,
            'support_ticket_id' => $supportticket->id,
            'date' => gmdate('Y-m-d H:i:s'),
            'created_by' => $apiUser->id ?? 0,
            'icon' =>  $statusChange ? $this->getIconforNotification($statusChange) : $this->getIconforNotification($type),
            'color' =>  $statusChange ? $this->getColorforNotification($statusChange) : $this->getColorforNotification($type),
        ]);

        return [
            'type' => $statusChange,
            'status' => $notification->status,
            'comment' => $notification->comment,
            'support_ticket_id' => $notification->support_ticket_id,
            'created_by' => $notification->created_by,
            'icon' => $notification->icon,
            'color' => $notification->color,
            'date' => $notification->date,
            'created_at' => $notification->created_at,
            'updated_at' => $notification->updated_at,
        ];
    }

    public function getColorforNotification($type)
    {
        $color =   [
            "Ticket Created" => "primary",
            "Ticket Transfer" => "info",
            "Ticket Merge" => "warning",
            "Priority Changed" => "secondary",
            "Type Changed" => "danger",
            "Ticket Close" => "success",
            "Status Changed" => "info",
        ];

        return $color[$type] ?? "secondary";
    }

    public function getIconforNotification($type)
    {
        $icon =   [
            "Ticket Created" => "cilUser",
            "Ticket Transfer" => "cilTransfer",
            "Ticket Merge" => "cilSync",
            "Priority Changed" => "cilChevronDoubleUp",
            "Type Changed" => "cilFire",
            "Ticket Close" => "cilCheck",
            "Status Changed" => "cilBolt",
        ];

        return $icon[$type] ?? "cilAsteriskCircle";
    }

    public function sendSms($apiUser, $type, $phone, $supportticket)
    {
        $smsInfo = new SmsService;

        $templateInfo = SmsTemplate::where("name", $type)->first();
        $templateName = $templateInfo->name ?? '';
        $template = $templateInfo->content ?? '';
        $template = str_replace("{#ticket ##}", $supportticket->support_ticket_id, $template);
        $template = str_replace("{#customer name#}", $apiUser->first_name . ' ' . $apiUser->last_name, $template);
        $smsInfo->sendSMS($apiUser->id, $templateName, $template, $phone);
    }
}
