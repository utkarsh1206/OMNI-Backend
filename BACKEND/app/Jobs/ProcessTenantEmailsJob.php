<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Webklex\IMAP\Facades\Client;
use App\Models\EmailConfigure;
use App\Models\MailDetail;
use App\Models\SupportTicket;
use App\Models\SupportTicketNotification;
use App\Models\Account;
use App\Models\Category;
use App\Models\Detail;
use App\Models\MailTemplate;
use App\Models\Users;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTenantEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;
    protected $commandRunId;
    protected $jobRunId;

    public function __construct($tenant, $commandRunId)
    {
        $this->tenant       = $tenant;
        $this->commandRunId = $commandRunId;
        $this->jobRunId     = uniqid('job_', true);
    }

    public function handle()
    {
        $tenant       = $this->tenant;
        $connection   = str_replace(".", "_", $tenant->domain_name);
        $databaseName = str_replace(".", "_", $tenant->domain_name);

        generateLogs(
            "info",
            "Starting ProcessTenantEmailsJob" . json_encode([
                'tenant_id'      => $tenant->id,
                'connection'     => $connection,
            ])
        );

        try {
            establishDatabaseConnect($connection, $databaseName);
            DB::reconnect($connection);

            if (!Schema::connection($connection)->hasTable('email_configures')) {
                generateLogs(
                    "warning",
                    "email_configures table missing" . json_encode([
                        'command_run_id' => $this->commandRunId,
                        'job_run_id'     => $this->jobRunId,
                        'tenant_id'      => $tenant->id,
                    ])
                );
                return;
            }

            $emailConfig = EmailConfigure::on($connection)
                ->where('type', 'gmail')
                ->first();

            if (!$emailConfig) {
                generateLogs(
                    "warning",
                    "No email configuration found" . json_encode([
                        'command_run_id' => $this->commandRunId,
                        'job_run_id'     => $this->jobRunId,
                        'tenant_id'      => $tenant->id,
                    ])
                );
                return;
            }

            $client = Client::make([
                'host'       => $emailConfig->imap_host,
                'port'       => $emailConfig->imap_port,
                'encryption' => 'ssl',
                'username'   => $emailConfig->imap_user,
                'password'   => $emailConfig->imap_password,
                'protocol'   => 'imap',
            ]);

            $client->connect();
            $folder   = $client->getFolder('INBOX', 'UNSEEN');

            $messages = $folder->messages()->unseen()->get();   // ->limit(20)->

            generateLogs(
                "info",
                "Fetched unseen emails" . json_encode([
                    'tenant_id'      => $tenant->id,
                    'count'          => $messages->count(),
                ])
            );

            // Configure SMTP dynamically
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $emailConfig->smtp_host,
                'mail.mailers.smtp.port' => $emailConfig->smtp_port,
                'mail.mailers.smtp.username' => $emailConfig->smtp_user,
                'mail.mailers.smtp.password' => $emailConfig->smtp_password,
                'mail.mailers.smtp.encryption' => ($emailConfig->smtp_port == 587) ? 'TLS' : 'SSL',
                'mail.from.address' => $emailConfig->sender_email,
                'mail.from.name' => $emailConfig->sender_name,
            ]);

            foreach ($messages as $message) {
                $this->storeEmailAsTicket($message, $emailConfig, $connection, $databaseName);
            }

            generateLogs(
                "info",
                "Finished processing tenant emails" . json_encode([
                    'command_run_id' => $this->commandRunId,
                    'job_run_id'     => $this->jobRunId,
                    'tenant_id'      => $tenant->id,
                ])
            );
        } catch (Exception $e) {
            generateLogs(
                "error",
                "Error processing tenant emails" . json_encode([
                    'command_run_id' => $this->commandRunId,
                    'job_run_id'     => $this->jobRunId,
                    'tenant_id'      => $tenant->id,
                    'error'          => $e->getMessage(),
                ])
            );
        }
    }

    private function storeEmailAsTicket($message, $emailConfig, $connection, $databaseName)
    {
        $from          = $message->getFrom();
        $emailAddress  = $from[0]->mail ?? '';
        $name          = $from[0]->personal ?? '';
        $subject       = $message->getSubject();
        $subjectFinal  = is_array($subject) ? $subject[0] : $subject;

        // Save attachments
        $attachment_urls = [];
        if ($message->hasAttachments()) {
            foreach ($message->getAttachments() as $attachment) {
                $filename1 = $attachment->getName();
                $filename  = now()->format('Y_m_d_H_i_s') . '_' . $filename1;
                $path      = 'support_ticket/' . $filename;
                Storage::disk('public')->put($path, $attachment->getContent());
                $attachment_urls[] = $path;
            }
        }

        $messageTextBody = $message->getTextBody();

        establishDatabaseConnect($connection, $databaseName);
        DB::reconnect($connection);

        $account = Account::on($connection)->where('product_id', $emailConfig->product_id)->first();
        $category = Category::on($connection)->where('department_id', $emailConfig->department_id)->first();

        // Save ticket
        $ticket = new SupportTicket();
        $ticket->setConnection($connection);
        $ticket->fill([
            'product_id'       => $emailConfig->product_id,
            'department_id'    => $emailConfig->department_id,
            'account_id'       => $account ? $account->id : 0,
            'category_id'      => $category ? $category->id : 0,
            'campaign_id'      => $emailConfig->campaign_id,
            'inbound_group_id' => $emailConfig->inbound_id,
            'contact_id'       => 0,
            'assign_to_id'     => 0,
            'default_type'     => $emailConfig->default_type,
            'default_priority' => 1,
            'default_status'   => $emailConfig->default_status,
            'subject'          => $subjectFinal,
            'departments'      => '',
            'to_emails'        => $emailAddress,
            'description'      => $messageTextBody ?? '',
            'attachment'       => json_encode($attachment_urls),
            'incident_id'      => 1,
            'campaign_id'      => 0,
        ]);
        $ticket->save();

        // Generate ticket number
        $datePrefix = gmdate('ymdhis');
        $ticket->support_ticket_id = $datePrefix . $ticket->id;
        $ticket->save();

        $this->createTicketNotification(
            $ticket,
            "Ticket Created",
            $name . " has created the ticket",
            $connection
        );

        generateLogs(
            "info",
            "Created support ticket" . json_encode([
                'command_run_id' => $this->commandRunId,
                'job_run_id'     => $this->jobRunId,
                'tenant_id'      => $this->tenant->id,
                'ticket_id'      => $ticket->id,
                'ticket_no'      => $ticket->support_ticket_id,
                'subject'        => $subjectFinal,
                'attachments'    => count($attachment_urls),
            ])
        );

        // Mark email as read
        $message->setFlag('Seen');

        $uin = Detail::where('domain', $connection)->value('uin');

        establishDatabaseConnect($connection, $databaseName);
        DB::reconnect($connection);

        try {
            $mailTemplate = MailTemplate::on($connection)
                ->where('type', 'create_ticket_customer')
                ->where('status', 'Active')
                ->where('mail_for', 'request')
                ->first();

            $agentMailTemplate = MailTemplate::on($connection)
                ->where('type', 'created_ticket')
                ->where('status', 'Active')->where('mail_for', 'agent')->first();

            if ($agentMailTemplate) {
                $agentMailTemplate['subject'] = str_replace(
                    '{ticket_number}',
                    $ticket->support_ticket_id,
                    $agentMailTemplate['subject']
                );
            }
        } catch (\Throwable $th) {
            generateLogs("error", "Error fetching mail template: " . $th->getMessage());
        }

        $adminMail = $emailConfig?->mailTo ?? Users::on($connection)->where('role_id', 1)->value('email');

        $customerSubject = str_replace(
            "{ticket_number}",
            $ticket->support_ticket_id,
            $mailTemplate->subject
        );

        generateLogs("info", "Sending mail to customer");

        MailDetail::on($connection)->create($this->buildMailData(
            $ticket,
            $attachment_urls,
            $ticket->support_ticket_id,
        ));

        sendProcessedEmail(
            to: $emailAddress,
            cc: [],
            bcc: [],
            subject: $customerSubject,
            bodyTemplate: $mailTemplate['body'] ?? $messageTextBody,
            mailTemplateId: $mailTemplate ? $mailTemplate->id : 0,
            mailAttachments: $attachment_urls,
            ticket: $ticket,
            senderEmail: $emailConfig->sender_email,
            assignToId: 0,
            module: "Create Support Ticket",
            newConnectionName: $connection,
            uin: $uin ?? "",
        );

        // ADMIN MAIL
        sendProcessedEmail(
            to: $adminMail,
            subject: $agentMailTemplate['subject'],
            bodyTemplate: $agentMailTemplate['body'] ?? '',
            mailTemplateId: $agentMailTemplate->id ?? 0,
            mailAttachments: $attachment_urls,
            ticket: $ticket,
            senderEmail: $emailConfig->sender_email,
            assignToId: 1,
            module: "Create Support Ticket",
            newConnectionName: $connection,
        );

        establishMasterConnection($connection, "omnichannel", "mysql");

        generateLogs(
            "info",
            "Sent confirmation mail" . json_encode([
                'command_run_id' => $this->commandRunId,
                'job_run_id'     => $this->jobRunId,
                'tenant_id'      => $this->tenant->id,
                'to'             => $emailAddress,
                'ticket_no'      => $ticket->support_ticket_id,
            ])
        );
    }

    private function createTicketNotification($supportticket, $type, $comment, $connection, $statusChange = null)
    {
        getDbConnection($connection, true);
        // Create notification instance
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

        // Save using the assigned connection
        $notification->save();

        establishMasterConnection($connection, "omnichannel", "mysql");

        // Return the formatted response
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
            'user_id' => 0,  // To be modified once clear user context is available
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

//DONE(MOSTLY) - check the values of data that is being stored in the support ticket

// check how will the queue be restarted...

// Incident ID configuration in the email_configures page
