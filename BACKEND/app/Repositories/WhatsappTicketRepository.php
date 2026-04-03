<?php

namespace App\Repositories;

use App\Models\EmailConfigure;
use App\Models\MailDetail;
use App\Models\MailTemplate;
use App\Models\MessageAttachment;
use App\Models\SupportTicket;
use App\Models\SupportTicketNotification;
use App\Models\Users;
use App\Services\Chat360Service;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WhatsappTicketRepository
{
    protected $chat360Service;

    public function __construct(Chat360Service $chat360Service)
    {
        $this->chat360Service = $chat360Service;
    }

    public function createWhatsAppTicket($request)
    {
        try {
            generateLogs("info", "\nInitiating WhatsApp ticket creation for room_id: " . $request->room_id);
            DB::beginTransaction();

            if (!$request->room_id) {
                generateLogs("error", "\nValidation failed: room_id is missing");
                return response()->json([
                    'status' => 'error',
                    'message' => 'room_id is required'
                ], 422);
            }

            generateLogs("info", "\nFetching chat history from Chat360 for room_id: " . $request->room_id);
            $chatHistory = $this->chat360Service->getChatHistory($request->room_id);

            if (!$chatHistory || !isset($chatHistory['messages'])) {
                generateLogs("warning", "\nNo chat history found for room_id: " . $request->room_id . " Data: " . json_encode($chatHistory));
                return response()->json([
                    'status' => 'error',
                    'message' => 'No chat history found'
                ], 404);
            }

            $messages = $chatHistory['messages'];
            generateLogs("debug", "\nChat messages retrieved. Count: " . count($messages));

            $phoneNumber = $chatHistory['variables']['@user_number']
                ?? $chatHistory['variables']['@phone']
                ?? null;

            generateLogs("info", "\nExtracted phone number: " . $phoneNumber);

            $description = $this->buildDescriptionFromChat360($messages);

            $firstUserMessage = collect($messages)->firstWhere('user', 'end_user');
            // didnt get name
            $customerName_whatsapp = $firstUserMessage['first_name']
                ?? $firstUserMessage['last_name']
                ?? 'WhatsApp User';
            generateLogs("info", "Name - " . json_encode($customerName_whatsapp));
            $customerName = $request->name ?? "";

            $ticketNumber = now()->format('ymdHis');
            generateLogs("info", "\nPreparing to create SupportTicket: " . $ticketNumber . " for " . $customerName);

            $ticket = SupportTicket::create([
                'support_ticket_id'    => $ticketNumber,
                'subject'          => "WhatsApp Support Ticket ($ticketNumber)",
                'description'      => $description,
                'to_emails'            => $request->email ?? null,
                'contact_number'     => $phoneNumber,

                // CONFIG DEFAULTS
                'product_id'       => config('omni.product_id'),
                'department_id'    => config('omni.department_id'),
                'account_id'       => config('omni.account_id'),
                'category_id'      => config('omni.category_id'),
                'incident_id'      => config('omni.incident_id'),
                'campaign_id'      => config('omni.campaign_id'),
                'inbound_group_id' => config('omni.inbound_group_id'),
                'default_type'     => config('omni.default_type'),
                'default_priority' => config('omni.default_priority'),
                'default_status'   => config('omni.default_status'),
                'contact_id' => 0,
                'assign_to_id'     => 0,
                'departments'      => '',
                'source'           => 'whatsapp',
            ]);

            generateLogs("info", "\nSupportTicket created with ID: " . $ticket->id);

            $this->createTicketNotification(
                $ticket,
                "Ticket Created",
                $customerName . " has created the ticket",
                null,
            );

            generateLogs("debug", "\nSupportTicketNotification record created");


            DB::commit();
            generateLogs("info", "\nDatabase transaction committed successfully for Ticket ID: " . $ticket->id);

            $attachment_urls = $this->storeAttachments($ticket, $messages);
            // $attachment_urls = [];

            generateLogs("info", "\nSending emails to user and admin");
            $this->sendEmails($ticket, $request->email, $attachment_urls, $request);

            return response()->json([
                'status' => 'success',
                'ticket' => $ticket
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            generateLogs("error", "\nException occurred during ticket creation: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());

            logger()->error('WHATSAPP_TICKET_CREATE', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Ticket creation failed'
            ], 500);
        }
    }

    private function buildDescriptionFromChat360(array $messages): string
    {
        generateLogs("debug", "\nBuilding description from chat history");
        $desc = "WhatsApp Conversation Log\n\n";

        foreach ($messages as $msg) {

            $sender = $msg['user'] ?? null;

            if (!in_array($sender, ['bot', 'end_user'])) {
                continue;
            }

            if ($sender === 'bot') {
                $messageText = $msg['data']['questionText']
                    ?? ($msg['data']['messages'][0] ?? null);
            } else {
                $messageText = $msg['post_data']
                    ?? $msg['message']
                    ?? null;
            }

            if (!$messageText) continue;

            if (is_array($messageText)) {
                $messageText = implode(', ', $messageText);
            }

            $messageText = trim(strip_tags($messageText), "[]\"");

            $timestamp = isset($msg['timestamp_int'])
                ? Carbon::createFromTimestamp((float) $msg['timestamp_int'])->format('Y-m-d H:i')
                : Carbon::now()->format('Y-m-d H:i');

            $label = $sender === 'bot' ? 'Bot' : 'User';

            $desc .= "[{$timestamp}] {$label}: {$messageText}\n";
        }

        generateLogs("debug", "\nDescription build complete");
        return $desc;
    }

    private function storeAttachments($ticket, $messages): array
    {
        generateLogs("info", "\nStoring attachments for ticket ID: " . $ticket->id);
        $paths = [];

        foreach ($messages as $msg) {
            if (isset($msg['attachments']) && is_array($msg['attachments'])) {
                foreach ($msg['attachments'] as $attachment) {
                    // Ensure we have a path to store
                    $filePath = $attachment['file_path'] ?? ($attachment['url'] ?? null);
                    if (!$filePath) continue;

                    generateLogs("debug", "\nCreating attachment record for: " . $filePath);

                    MessageAttachment::create([
                        'support_ticket_id' => $ticket->id,
                        'file_path'         => $filePath,
                        'file_type'         => $attachment['file_type'] ?? 'unknown',
                    ]);

                    $paths[] = $filePath;
                }
            }
        }
        return $paths;
    }

    private function sendEmails($ticket, $userEmail = null, array $attachment_urls = [], $request = null)
    {
        try {
            $emailConfig = EmailConfigure::where('type', 'gmail')
                ->whereNotNull('smtp_host')
                ->whereNotNull('smtp_port')
                ->first();

            if (!$emailConfig) {
                generateLogs("error", "Email configuration not found or incomplete.");
                return;
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $emailConfig->smtp_host,
                'mail.mailers.smtp.port' => $emailConfig->smtp_port,
                'mail.mailers.smtp.username' => $emailConfig->smtp_user,
                'mail.mailers.smtp.password' => $emailConfig->smtp_password,
                'mail.mailers.smtp.encryption' => ($emailConfig->smtp_port == 587) ? 'tls' : 'ssl',
                'mail.from.address' => $emailConfig->sender_email,
                'mail.from.name' => $emailConfig->sender_name,
            ]);

            $mailTemplate = MailTemplate::where('type', 'create_ticket_customer')
                ->where('status', 'Active')
                ->where('mail_for', 'request')
                ->first();

            $agentMailTemplate = MailTemplate::where('type', 'created_ticket')
                ->where('status', 'Active')
                ->where('mail_for', 'agent')
                ->first();

            if ($agentMailTemplate) {
                $agentMailTemplate['subject'] = str_replace(
                    '{ticket_number}',
                    $ticket->support_ticket_id,
                    $agentMailTemplate['subject']
                );
            }

            $domain = $request ? $request->attributes->get('tenant_domain') : "";
            $userInfo = (object) ['domain' => $domain];
            $uin = getTenantUin($userInfo);

            $adminMail = $emailConfig->mailTo ?? Users::where('role_id', 1)->value('email');

            MailDetail::create($this->buildMailData(
                $ticket,
                $attachment_urls,
                $ticket->support_ticket_id
            ));

            if ($userEmail && $mailTemplate) {
                $customerSubject = str_replace("{ticket_number}", $ticket->support_ticket_id, $mailTemplate->subject);

                sendProcessedEmail(
                    to: $userEmail,
                    subject: $customerSubject,
                    bodyTemplate: $mailTemplate->body ?? $ticket->description,
                    mailTemplateId: $mailTemplate->id,
                    mailAttachments: $attachment_urls,
                    ticket: $ticket,
                    senderEmail: $emailConfig->sender_email,
                    assignToId: 0,
                    module: "Create Support Ticket",
                    newConnectionName: null,
                    uin: $uin ?? "",
                );
            }

            if ($agentMailTemplate) {
                $agentSubject = str_replace("{ticket_number}", $ticket->support_ticket_id, $agentMailTemplate->subject);

                sendProcessedEmail(
                    to: $adminMail,
                    subject: $agentSubject,
                    bodyTemplate: $agentMailTemplate->body ?? '',
                    mailTemplateId: $agentMailTemplate->id,
                    mailAttachments: $attachment_urls,
                    ticket: $ticket,
                    senderEmail: $emailConfig->sender_email,
                    assignToId: 1,
                    module: "Create Support Ticket",
                    newConnectionName: null,
                    // uin: $uin ?? "",
                );
            }
        } catch (\Throwable $th) {
            generateLogs("error", "Error in sendEmails: " . $th->getMessage());
        }
    }

    private function createTicketNotification(
        $supportticket,
        $type,
        $comment,
        // $connection,
        // $domain,
        $statusChange = null,
    ) {
        generateLogs("info", "Creating Support Ticket Notification");
        $notification = new SupportTicketNotification();

        $notification->fill([
            'type' => $statusChange ?? $type,
            'status' => $supportticket->default_status,
            'comment' => $comment,
            'support_ticket_id' => $supportticket->id,
            'date' => gmdate('Y-m-d H:i:s'),
            'created_by' => 0,
            'icon' => $statusChange
                ? getIconforNotification($statusChange)
                : getIconforNotification($type),
            'color' => $statusChange
                ? getColorforNotification($statusChange)
                : getColorforNotification($type),
        ]);

        $notification->save();
        generateLogs("info", "Support ticket notification created");

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

    private function buildMailData($ticket, array $attachment_urls, string $supportTicketId): array
    {
        return [
            'default_type' => $ticket->default_type ?? '',
            'user_id' => 0,
            'priority' => $ticket->default_priority ?? '',
            'status' => $ticket->default_status ?? '',
            'to' => $ticket->to_emails ?? '',
            'cc' => $ticket->cc ?? '',
            'bcc' => $ticket->bcc ?? '',
            'body' => $ticket->description ?? '',
            'attachment' => json_encode($attachment_urls),
            'supportticket_id' => $supportTicketId,
            'date' => gmdate('Y-m-d H:i:s'),
        ];
    }
}
