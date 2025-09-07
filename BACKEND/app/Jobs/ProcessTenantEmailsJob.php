<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Webklex\IMAP\Facades\Client;
use App\Models\EmailConfigure;
use App\Models\SupportTicket;
use App\Mail\SupportTicketMail;
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

        generateLogs("info", "Starting ProcessTenantEmailsJob", [
            'command_run_id' => $this->commandRunId,
            'job_run_id'     => $this->jobRunId,
            'tenant_id'      => $tenant->id,
            'domain_name'    => $tenant->domain_name,
            'connection'     => $connection,
        ]);

        try {
            establishDatabaseConnect($connection, $databaseName);
            DB::reconnect($connection);

            if (!Schema::connection($connection)->hasTable('email_configures')) {
                generateLogs("warning", "email_configures table missing", [
                    'command_run_id' => $this->commandRunId,
                    'job_run_id'     => $this->jobRunId,
                    'tenant_id'      => $tenant->id,
                ]);
                return;
            }

            $emailConfig = EmailConfigure::on($connection)
                ->where('type', 'gmail')
                ->first();

            if (!$emailConfig) {
                generateLogs("warning", "No email configuration found", [
                    'command_run_id' => $this->commandRunId,
                    'job_run_id'     => $this->jobRunId,
                    'tenant_id'      => $tenant->id,
                ]);
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
            $messages = $folder->messages()->unseen()->limit(20)->get();

            generateLogs("info", "Fetched unseen emails", [
                'command_run_id' => $this->commandRunId,
                'job_run_id'     => $this->jobRunId,
                'tenant_id'      => $tenant->id,
                'count'          => $messages->count(),
            ]);

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
                $this->storeEmailAsTicket($message, $emailConfig, $connection);
            }

            generateLogs("info", "Finished processing tenant emails", [
                'command_run_id' => $this->commandRunId,
                'job_run_id'     => $this->jobRunId,
                'tenant_id'      => $tenant->id,
            ]);
        } catch (Exception $e) {
            generateLogs("error", "Error processing tenant emails", [
                'command_run_id' => $this->commandRunId,
                'job_run_id'     => $this->jobRunId,
                'tenant_id'      => $tenant->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    private function storeEmailAsTicket($message, $emailConfig, $connection)
    {
        $from          = $message->getFrom();
        $emailAddress  = $from[0]->mail ?? '';
        $subject       = $message->getSubject();
        $subject_final = is_array($subject) ? $subject[0] : $subject;

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

        // Save ticket
        $ticket = new SupportTicket();
        $ticket->setConnection($connection);
        $ticket->fill([
            'product_id'       => $emailConfig->product_id,
            'campaign_id'      => $emailConfig->campaign_id,
            'inbound_group_id' => $emailConfig->inbound_id,
            'contact_id'       => 0,
            'assign_to_id'     => 0,
            'default_type'     => $emailConfig->default_type,
            'default_priority' => 'High',
            'default_status'   => $emailConfig->default_status,
            'subject'          => $subject_final,
            'departments'      => '',
            'to_emails'        => $emailAddress,
            'description'      => $messageTextBody ?? '',
            'attachment'       => json_encode($attachment_urls),
        ]);
        $ticket->save();

        // Generate ticket number
        $datePrefix = now()->format('dmy');
        $ticket->support_ticket_id = $datePrefix . $ticket->id;
        $ticket->save();

        generateLogs("info", "Created support ticket", [
            'command_run_id' => $this->commandRunId,
            'job_run_id'     => $this->jobRunId,
            'tenant_id'      => $this->tenant->id,
            'ticket_id'      => $ticket->id,
            'ticket_no'      => $ticket->support_ticket_id,
            'subject'        => $subject_final,
            'attachments'    => count($attachment_urls),
        ]);

        // Mark email as read
        $message->setFlag('\\Seen');

        // Send confirmation mail
        $mailData = (object)[
            'ticket_no' => $ticket->support_ticket_id,
            'priority'  => 'High',
            'status'    => $emailConfig->default_status,
            'subject'   => $subject_final,
            'body'      => $messageTextBody,
        ];
        Mail::to($emailAddress)->send(new SupportTicketMail($mailData));

        generateLogs("info", "Sent confirmation mail", [
            'command_run_id' => $this->commandRunId,
            'job_run_id'     => $this->jobRunId,
            'tenant_id'      => $this->tenant->id,
            'to'             => $emailAddress,
            'ticket_no'      => $ticket->support_ticket_id,
        ]);
    }
}
