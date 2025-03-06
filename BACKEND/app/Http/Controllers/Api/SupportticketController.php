<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TenantsResource;
use App\Models\Supportticket;
use App\Models\MailDetail;
use App\Models\EmailConfigure;
use Illuminate\Support\Facades\Validator;
use App\Models\Supportticketnotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Mail\SendEmail;
use App\Services\SlaService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\MailTemplate;

class SupportticketController extends Controller
{
    protected $slaService;

    // Constructor to inject the SlaService
    public function __construct(SlaService $slaService)
    {
        $this->slaService = $slaService;
    }

    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');

        $ticket_id = $request->input('ticket_id', '');
        $product_id = $request->input('product_id', '');
        $subject = $request->input('subject', '');
        $default_type = $request->input('default$default_type', '');
        $default_status = $request->input('default_status', '');
        $default_priority = $request->input('default_priority', '');
        $assign_to_id = $request->input('assign_to_id', '');
        $contact_id = $request->input('contact_id', '');

        $token = $request->bearerToken();
        if ($token != "") {
            $payload = JWTAuth::setToken($token)->getPayload();
            $roleId = $payload['role_id'];
            $userId = $payload['id'];
        }

        // Start the query for tenants
        $query = supportticket::query()->where('is_delete', 'No')->with('department');

        if ($roleId == 3) {
            $query->where('assign_to_id', $userId); // Filter by the logged-in user's ID
        }

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('to_emails', 'like', '%' . $searchTerm . '%')
                    ->orwhere('subject', 'like', '%' . $searchTerm . '%')
                    ->orwhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $supportticket = $query->paginate($perPage);
        $page = $request->input('page', 1);

        if (!empty($ticket_id)) {
            $query->where('ticket_id', $ticket_id);
        }
        if (!empty($product_id)) {
            $query->where('product_id', $product_id);
        }
        if (!empty($subject)) {
            $query->where('subject', $subject);
        }
        if (!empty($default_type)) {
            $query->where('default_type', $default_type);
        }
        if (!empty($default_status)) {
            $query->where('default_status', $default_status);
        }
        if (!empty($default_priority)) {
            $query->where('default_priority', $default_priority);
        }
        if (!empty($assign_to_id)) {
            $query->where('assign_to_id', $assign_to_id);
        }
        if (!empty($contact_id)) {
            $query->where('contact_id', $contact_id);
        }

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $supportticket = $query->get();
            return response()->json([
                'data' => $supportticket,
            ]);
        } else {
            // Paginate the results
            $supportticket = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $supportticket->items(),
                'meta' => [
                    'current_page' => $supportticket->currentPage(),
                    'last_page' => $supportticket->lastPage(),
                    'per_page' => $supportticket->perPage(),
                    'total' => $supportticket->total(),
                ],
            ]);
        }
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
    public function show(Supportticket $supportticket)
    {
        return new TenantsResource($supportticket);
    }
    public function update(Request $request, Supportticket $supportticket)
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
    public function statusupdate(Request $request, Supportticket $supportticket)
    {
        $apiUser = auth()->user();
        $type = "";
        $comment = "";
        $statusInfo = "";
        if (isset($request->default_status)) {
            $type = $request->default_status;
            $comment = "Change ticket status to: " . $request->default_status;
            $updateData = [
                'default_status' => isset($request->default_status) ? $request->default_status : "",
            ];
            $statusInfo = "Status Changed";
        }
        if (isset($request->default_priority)) {
            $type = $request->default_priority;
            $comment = "Change ticket priority to: " . $request->default_priority;
            $updateData = [
                'default_priority' => isset($request->default_priority) ? $request->default_priority : "",
            ];
            $statusInfo = "Priority Changed";
        }
        if (isset($request->default_type)) {
            $type = $request->default_type;
            $comment = "Change ticket type to: " . $request->default_type;
            $updateData = [
                'default_type' => isset($request->default_type) ? $request->default_type : "",
            ];
            $statusInfo = "Type Changed";
        }



        $supportticket->update($updateData);
        $ticketResponse = $this->createTicket($supportticket, $type, $comment, $apiUser, $statusInfo);
        if ($request->default_status == "Resolved") {
            // $this->sendSms($apiUser, "Ticket Resolved", $apiUser->mobile, $supportticket);
            $toEmails = $supportticket->assignedUser->email ?? '';
            setEmailConfig();
            $mailTemplate = MailTemplate::where("type", "agent_solve_the_ticket")->first();
            $subject =  $mailTemplate->subject ?? '';
            $body =  $mailTemplate->body ?? '';
            $body = str_replace("{ticket_number}", $supportticket->support_ticket_id, $body);
            $body = str_replace("{{customer_name}}", $supportticket->assignedUser->first_name, $body);
            $subject = str_replace("{ticket_number}", $supportticket->support_ticket_id, $subject);
            $res =  Mail::to($toEmails)->send(new SendEmail($subject, $body));
        }

        return response()->json([
            'message' => 'Support Ticket Status Updated Successfully',
            // 'data' => new TenantsResource($supportticket),
            'data' => array_merge(
                (new TenantsResource($supportticket))->toArray($request),
                $ticketResponse
            ),
        ]);
    }
    public function destroy(Supportticket $supportticket)
    {
        $supportticket->is_delete = 'Yes';
        $supportticket->save();
        return response()->json([
            'message' => 'Support Ticket Deleted Successfully',
        ]);
    }

    public function createTicket($supportticket, $type, $comment, $apiUser, $statusChange = NULL)
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
