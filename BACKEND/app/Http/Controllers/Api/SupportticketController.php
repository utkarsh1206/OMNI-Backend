<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TenantsResource;
use App\Models\Supportticket;
use App\Models\MailDetail;
use App\Models\EmailConfigure;
use Illuminate\Support\Facades\Validator;
use App\Models\SupportTicketnotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Mail\SendEmail;
use Tymon\JWTAuth\Facades\JWTAuth;

class SupportticketController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');

        $token = $request->bearerToken();
        if ($token != "") {
            $payload = JWTAuth::setToken($token)->getPayload();
            $roleId = $payload['role_id'];
            $userId = $payload['id'];
        }

        // Start the query for tenants
        $query = supportticket::query()->where('is_delete', 'No');

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
            'departments' => isset($request->departments) ? $request->departments : "",
            'to_emails' => isset($request->to_emails) ? $request->to_emails : "",
            'cc' => isset($request->cc) ? $request->cc : "",
            'bcc' => isset($request->bcc) ? $request->bcc : "",
            'description' => isset($request->description) ? $request->description : "",
            'attachment' => json_encode($attachment_urls),
        ]);

        $datePrefix = now()->format('dmy'); // Get the current date in ddmmyy format
        $supportTicketId = $datePrefix . $supportticket->id; // Use the newly inserted ID
        $mail_details = MailDetail::create([
            'default_type' => isset($request->default_type) ? $request->default_type : "",
            'priority' => isset($request->default_priority) ? $request->default_priority : "",
            'status' => isset($request->default_status) ? $request->default_status : "",
            'to' => isset($request->to_emails) ? $request->to_emails : "",
            'cc' => isset($request->cc) ? $request->cc : "",
            'bcc' => isset($request->bcc) ? $request->bcc : "",
            'body' => isset($request->description) ? $request->description : "",
            'attachment' => json_encode($attachment_urls),
            'supportticket_id' => $supportTicketId,
            'date' => gmdate('Y-m-d H:i:s')
        ]);

        $supportticket->update(['support_ticket_id' => $supportTicketId]);
        $notification = SupportTicketNotification::create([
            'type' => isset($request->default_type) ? $request->default_type : "",
            'comment' => "",
            'support_ticket_id' => $supportTicketId,
            'date' => gmdate('Y-m-d H:i:s'),
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
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
            'mail.from.address' =>  $sender_email,
            'mail.from.name' => $sender_name,
        ]);
        // Attempt to send the email
        try {
            Mail::to($request->to_emails)->send(new SendEmail($request->subject, $request->description, $mail_attachment_urls));
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
            'departments' => isset($request->departments) ? $request->departments : "",
            'to_emails' => isset($request->to_emails) ? $request->to_emails : "",
            'cc' => isset($request->cc) ? $request->cc : "",
            'bcc' => isset($request->bcc) ? $request->bcc : "",
            'description' => isset($request->description) ? $request->description : "",
        ];
        if (!empty($attachment_urls)) {
            $updateData['attachment'] = $attachment_urls;
        }
        $supportticket->update($updateData);
        $notification = SupportTicketNotification::create([
            'type' => isset($request->default_status) ? $request->default_status : "",
            'comment' => "Edit Support Ticket",
            'support_ticket_id' => $supportticket->id,
            'date' => gmdate('Y-m-d H:i:s'),
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
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
            'mail.from.address' =>  $sender_email,
            'mail.from.name' => $sender_name,
        ]);
        // Attempt to send the email
        try {
            Mail::to($request->to_emails)->send(new SendEmail($request->subject, $request->description, $mail_attachment_urls));
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
        $type = "";
        $comment = "";
        if (isset($request->default_status)) {
            $type = $request->default_status;
            $comment = "Change ticket status to: " . $request->default_status;
            $updateData = [
                'default_status' => isset($request->default_status) ? $request->default_status : "",
            ];
        }
        if (isset($request->default_priority)) {
            $type = $request->default_priority;
            $comment = "Change ticket priority to: " . $request->default_priority;
            $updateData = [
                'default_priority' => isset($request->default_priority) ? $request->default_priority : "",
            ];
        }
        if (isset($request->default_type)) {
            $type = $request->default_type;
            $comment = "Change ticket type to: " . $request->default_type;
            $updateData = [
                'default_type' => isset($request->default_type) ? $request->default_type : "",
            ];
        }
        $supportticket->update($updateData);
        $notification = SupportTicketNotification::create([
            'type' => $type,
            'comment' =>  $comment,
            'support_ticket_id' => $supportticket->id,
            'date' => gmdate('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'message' => 'Support Ticket Status Updated Successfully',
            'data' => new TenantsResource($supportticket)
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
}
