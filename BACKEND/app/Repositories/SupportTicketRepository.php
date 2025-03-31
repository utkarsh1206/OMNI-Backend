<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Resources\TenantsResource;
use App\Http\Response\CustomApiResponse;
use App\Models\SupportTicket;
use App\Models\MailDetail;
use App\Models\EmailConfigure;
use App\Models\SupportTicketNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Mail\SendEmail;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use App\Models\MailTemplate;

class SupportTicketRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
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
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $supportticket = $query->paginate($perPage);
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

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

            if ($page === config('omni.ALL_PAGE')) {
                $supportticket = $query->get();
            } else {
                $supportticket = $query->paginate($perPage);
            }
            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $supportticket, trans('message.data_fetch'));
        } catch (Exception $ex) {
            Log::error("Error fetching Support Tickets: " . $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $apiUser = auth()->user();

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

            $supportTicket = SupportTicket::create([
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
            $supportTicketId = $datePrefix . $supportTicket->id; // Use the newly inserted ID
            MailDetail::create([
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

            $supportTicket->update(['support_ticket_id' => $supportTicketId]);
            $comment = $apiUser->first_name . " " . $apiUser->last_name . " has created the ticket";
            $this->createTicket($supportTicket, "Ticket Created", $comment, $apiUser);
            $this->sendSms($apiUser, "Ticket Created", $apiUser->mobile, $supportTicket);
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

            Mail::to($request->to_emails)->send(new SendEmail($request->subject, $request->description, $mail_attachment_urls));

            logEmail([
                "user_id" => $request->assign_to_id,
                "email_template_id" => 0,
                "from_email" => $sender_email,
                "module_name" => "Support Ticket",
                "entity_id" => $supportTicket->id,
                "status" => "sent",
            ]);

            $newSupportTicket = new TenantsResource($supportTicket);
            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $newSupportTicket, trans('message.support_ticket_create_msg'));
        } catch (Exception $ex) {
            Log::error("Error occurred while creating  Support Tickets: " . $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, SupportTicket $supportTicket)
    {
        try {
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

            $supportTicket->update($updateData);
            $this->createTicket($supportTicket, isset($request->default_status) ? $request->default_status : "", "Edit Support Ticket", $apiUser);
            $this->sendSms($apiUser, "Ticket Created", $apiUser->mobile, $supportTicket);
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

            Mail::to($request->to_emails)->send(new SendEmail($request->subject, $request->description, $mail_attachment_urls));
            logEmail([
                "user_id" => $request->assign_to_id,
                "email_template_id" => 0,
                "from_email" => $sender_email,
                "module_name" => "Support Ticket",
                "entity_id" => $supportTicket->id,
                "status" => "sent",
            ]);

            $updatedSupportTicket = new TenantsResource($supportTicket);

            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $updatedSupportTicket, trans('message.support_ticket_update_msg'));
        } catch (Exception $ex) {
            Log::error("Error occurred while updating  Support Tickets: " . $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($supportTicket)
    {
        try {
            $supportTicket->delete();
            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $supportTicket, trans('message.support_ticket_delete_msg'));
        } catch (Exception $ex) {
            Log::error("Error occurred while deleting  Support Tickets: " . $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show(SupportTicket $supportTicket)
    {
        try {
            $response = new TenantsResource($supportTicket);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $response, trans('message.data_fetch'));
        } catch (Exception $ex) {
            Log::error("Error fetching SLA setting: " . $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
        return $this->apiResponse->getResponseStructure(trans('message.success'), $tenant, trans('message.data_fetch'));
    }

    public function statusUpdate(Request $request, Supportticket $supportTicket)
    {
        try {
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

            $supportTicket->update($updateData);
            $ticketResponse = $this->createTicket($supportTicket, $type, $comment, $apiUser, $statusInfo);
            if ($request->default_status == "Resolved") {
                // $this->sendSms($apiUser, "Ticket Resolved", $apiUser->mobile, $supportticket);
                $toEmails = $supportTicket->assignedUser->email ?? '';
                setEmailConfig();
                $mailTemplate = MailTemplate::where("type", "agent_solve_the_ticket")->first();
                $subject =  $mailTemplate->subject ?? '';
                $body =  $mailTemplate->body ?? '';
                $body = str_replace("{ticket_number}", $supportTicket->support_ticket_id, $body);
                $body = str_replace("{{customer_name}}", $supportTicket->assignedUser->first_name, $body);
                $subject = str_replace("{ticket_number}", $supportTicket->support_ticket_id, $subject);
                Mail::to($toEmails)->send(new SendEmail($subject, $body));
            }

            $statusUpdateData = array_merge(
                (new TenantsResource($supportTicket))->toArray($request),
                $ticketResponse
            );
            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $statusUpdateData, trans('message.data_fetch'));
        } catch (Exception $ex) {
            Log::error("Error fetching Support Tickets: " . $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function createTicket($supportticket, $type, $comment, $apiUser, $statusChange = null)
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

    private function getColorforNotification($type)
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

    private function getIconforNotification($type)
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

    private function sendSms($apiUser, $type, $phone, $supportticket)
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
