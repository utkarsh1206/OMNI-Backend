<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SupportTicket;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\EmailConfigure;
use App\Models\MailTemplate;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Mail\SlaSendEmail;
use App\Models\EmailNotification;
use App\Models\SupportTicketNotification;
use Illuminate\Support\Facades\Schema;

class SlaEmailNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sla-email-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'notifiy assign user of support tickets via email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        generateScheduleLogs('info', 'SLA Email Notification started for all tenants.');
        Tenant::orderBy('id')
            ->chunkById(100, function ($tenants) {
                foreach ($tenants as $tenant) {
                    $newConnectionName = str_replace(".", "_", $tenant->domain_name);
                    $databaseName = str_replace(".", "_", $tenant->domain_name);
                    establishDatabaseConnect($newConnectionName, $databaseName);
                    DB::reconnect($newConnectionName);

                    // Check if support_tickets table exists
                    if (!Schema::connection($newConnectionName)->hasTable('support_tickets')) {
                        $this->warn("Tenant: {$tenant->domain_name} - support_tickets table not found, skipping...");
                        continue;
                    }

                    $supportTickets = SupportTicket::on($newConnectionName)
                        ->where(function ($query) {
                            $query->whereNull('is_notified')
                                ->orWhere('is_notified', '!=', 4);
                        })
                        ->whereNull('actual_resolved_time')
                        // ->whereNotNull('resolved_time')
                        ->get();
                    if ($supportTickets->count()) {

                        foreach ($supportTickets as $ticket) {

                            $currentTime = Carbon::now();

                            if ($ticket->default_status != 2 && $ticket->default_status != 3) {

                                $hasStatusChange = $this->checkFirstResponse($newConnectionName, $ticket->id);

                                if ($hasStatusChange > 0) {
                                    if (
                                        $ticket->alert_before_resolve_notification_time &&
                                        $currentTime > $ticket->alert_before_resolve_notification_time
                                        && $ticket->is_notified != 3
                                    ) {
                                        $this->notifyViaEmail($newConnectionName, $ticket, "alert_before_resolve_email", "SLA Alert Before Resolve");
                                        $ticket->is_notified = 3;
                                    }

                                    if ($ticket->resolved_time && $currentTime > $ticket->resolved_time && $ticket->is_notified != 4) {
                                        $this->notifyViaEmail($newConnectionName, $ticket, "resolve_time_email", "SLA Alert Resolve");
                                        $ticket->is_notified = 4;
                                        $adminUsers = User::on($newConnectionName)
                                            ->where("type", "=", 0)->first();
                                        $ticket->is_escalated = "Yes";
                                        // $ticket->save();
                                        $this->notifyAdminViaEmail(
                                            $newConnectionName,
                                            $ticket,
                                            $adminUsers,
                                            "resolve_time_email", "SLA Alert Resolve"
                                        );
                                    }
                                } else {
                                    if (
                                        $currentTime > $ticket->alert_before_response_notification_time
                                        && $ticket->response_time == null && $ticket->is_notified == null
                                        &&  $ticket->is_notified != 1
                                    ) {
                                        $this->notifyViaEmail($newConnectionName, $ticket, "alert_before_response_email", "SLA Alert Before Response");
                                        $ticket->is_notified = 1;
                                    }

                                    if (
                                        $currentTime > $ticket->first_response_time
                                        && $ticket->response_time == null &&
                                        ($ticket->is_notified == 1 || $ticket->is_notified == null)
                                        &&  $ticket->is_notified != 2
                                    ) {
                                        $this->notifyViaEmail($newConnectionName, $ticket, "first_response_email", "SLA Alert Response");
                                        $ticket->is_notified = 2;
                                    }

                                    if (
                                        $ticket->alert_before_resolve_notification_time &&
                                        $currentTime > $ticket->alert_before_resolve_notification_time
                                        && $ticket->is_notified != 3
                                    ) {
                                        $this->notifyViaEmail($newConnectionName, $ticket, "alert_before_resolve_email", "SLA Alert Before Resolve");
                                        $ticket->is_notified = 3;
                                    }

                                    if ($ticket->resolved_time && $currentTime > $ticket->resolved_time && $ticket->is_notified != 4) {
                                        $this->notifyViaEmail($newConnectionName, $ticket, "resolve_time_email", "SLA Alert Resolve");
                                        $ticket->is_notified = 4;
                                        $adminUsers = User::on($newConnectionName)
                                            ->where("type", "=", 0)->first();
                                        $ticket->is_escalated = "Yes";
                                        // $ticket->save();
                                        $this->notifyAdminViaEmail(
                                            $newConnectionName,
                                            $ticket,
                                            $adminUsers,
                                            "resolve_time_email", "SLA Alert Resolve"
                                        );
                                    }
                                }



                                $ticket->save();
                            }
                        }
                        $this->info("Tenant: {$tenant->domain_name} - Notified " . $supportTickets->count());
                    }
                }
            });
        generateScheduleLogs('info', 'SLA Email Notification completed for all tenants.');
    }

    public function notifyViaEmail($newConnectionName, $tickets, $type, $module)
    {
        $emailConfig = EmailConfigure::on($newConnectionName)->where('type', 'gmail')->first();
        setEmailConfig($newConnectionName);

        $senderEmail = $emailConfig->sender_email ?? config('mail.from.address');

        try {
            $toEmails = $tickets->user_email ?? '';

            $mailTemplate = MailTemplate::on($newConnectionName)
                ->where("type", $type)
                ->where('status', 'Active')
                ->where('mail_for', 'agent')
                ->first();

            $fallbackSubject = "SLA Reminder: For Ticket $tickets->support_ticket_id";
            $subject = str_replace(
                "{ticket_number}",
                $tickets->support_ticket_id,
                $mailTemplate->subject ?? $fallbackSubject
            );

            $bodyTemplate =  $mailTemplate->body ?? '';

            sendProcessedEmail(
                $toEmails,
                $subject,
                $bodyTemplate,
                $mailTemplate->id ?? 0,
                [], // attachments (none here)
                $tickets,
                $senderEmail,
                $tickets->assignedUser->id,
                $module,
                [], // cc
                [], // bcc
                $newConnectionName //pass connection
            );

            $notification = new EmailNotification();
            $notification->setConnection($newConnectionName);
            $notification->fill([
                "type" => $type,
                "ticket_id" => $tickets->id
            ]);
            $notification->save();
        } catch (Exception $e) {
            generateScheduleLogs('error', 'SLA mail failed: ' . $e->getMessage());
            exit;
        }
    }

    public function notifyAdminViaEmail($newConnectionName, $tickets, $adminUser, $type, $module)
    {
        $emailConfig = EmailConfigure::on($newConnectionName)->where('type', 'gmail')->first();
        setEmailConfig($newConnectionName);

        $senderEmail = $emailConfig->sender_email ?? config('mail.from.address');

        try {
            $toEmails = $adminUser->email ?? '';

            $mailTemplate = MailTemplate::on($newConnectionName)
                ->where("type", 'resolve_time_email_admin')
                ->where('status', 'Active')
                ->where('mail_for', 'admin')
                ->first();

            $fallbackSubject = "SLA Reminder: For Ticket $tickets->support_ticket_id";
            $subject = str_replace(
                "{ticket_number}",
                $tickets->support_ticket_id,
                $mailTemplate->subject ?? $fallbackSubject
            );

            $bodyTemplate = $mailTemplate->body ?? '';

            $extra = [
                "admin_user" => $adminUser->username ?? 'Admin',
            ];
            $bodyTemplate = getTemplatePlaceholders($bodyTemplate, $tickets, $extra);

            sendProcessedEmail(
                $toEmails,
                $subject,
                $bodyTemplate,
                $mailTemplate->id ?? 0,
                [], // attachments (none here)
                $tickets,
                $senderEmail,
                $adminUser->id,
                $module,
                [], // cc
                [], // bcc
                $newConnectionName //pass connection
            );

            $notification =  EmailNotification::on($newConnectionName);
            $notification = new EmailNotification();
            $notification->setConnection($newConnectionName);
            $notification->fill([
                "type" => $type,
                "ticket_id" => $tickets->id
            ]);
            $notification->save();
        } catch (Exception $e) {
            generateScheduleLogs('error', 'SLA mail failed: ' . $e->getMessage());
            exit;
        }
    }

    private function checkFirstResponse($newConnectionName, $ticketId)
    {
        return SupportTicketNotification::on($newConnectionName)
            ->where('support_ticket_id', $ticketId)
            ->where('type', 'Status Changed')
            ->exists();
    }
}
