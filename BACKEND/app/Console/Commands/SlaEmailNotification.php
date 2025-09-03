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
                        ->whereNotNull('resolved_time')
                        ->get();
                    if ($supportTickets->count()) {

                        foreach ($supportTickets as $ticket) {

                            $currentTime = Carbon::now();

                            if ($ticket->default_status != 2 && $ticket->default_status != 3) {
                                if (
                                    $currentTime > $ticket->alert_before_response_notification_time
                                    && $ticket->response_time == null && $ticket->is_notified == null
                                    &&  $ticket->is_notified != 1
                                ) {
                                    $this->notifyViaEmail($newConnectionName, $ticket, "alert_before_response_email");
                                    $ticket->is_notified = 1;
                                }

                                if (
                                    $currentTime > $ticket->first_response_time
                                    && $ticket->response_time == null &&
                                    ($ticket->is_notified == 1 || $ticket->is_notified == null)
                                    &&  $ticket->is_notified != 2
                                ) {
                                    $this->notifyViaEmail($newConnectionName, $ticket, "first_response_email");
                                    $ticket->is_notified = 2;
                                }

                                if (
                                    $currentTime > $ticket->alert_before_resolve_notification_time
                                    && $ticket->is_notified != 3
                                ) {
                                    $this->notifyViaEmail($newConnectionName, $ticket, "alert_before_resolve_email");
                                    $ticket->is_notified = 3;
                                }

                                if ($currentTime > $ticket->resolved_time) {
                                    $this->notifyViaEmail($newConnectionName, $ticket, "resolve_time_email");
                                    $ticket->is_notified = 4;
                                    $adminUsers = User::on($newConnectionName)
                                        ->where("type", "=", 0)->first();
                                    $ticket->is_escalated = "Yes";
                                    // $ticket->save();
                                    $this->notifyAdminViaEmail(
                                        $newConnectionName,
                                        $ticket,
                                        $adminUsers,
                                        "resolve_time_email"
                                    );
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

    public function notifyViaEmail($newConnectionName, $tickets, $type)
    {
        generateScheduleLogs('info', 'tickets ' . json_encode($tickets));
        $emailConfig = EmailConfigure::on($newConnectionName)->where('type', 'gmail')->first();
        $smtpHost = $emailConfig->smtp_host ?? '';
        $smtpPort = $emailConfig->smtp_port ?? '';
        $smtpUser = $emailConfig->smtp_user ?? '';
        $smtpPassword = $emailConfig->smtp_password ?? '';
        $sender_name = $emailConfig->sender_name ?? '';
        $sender_email = $emailConfig->sender_email ?? '';
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $smtpHost,
            'mail.mailers.smtp.port' => $smtpPort,
            'mail.mailers.smtp.username' => $smtpUser,
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
            'mail.from.address' =>  $sender_email,
            'mail.from.name' => $sender_name,
        ]);
        try {
            $toEmails = $tickets->user_email ?? '';
            $mailTemplate = MailTemplate::on($newConnectionName)
                ->where("type", $type)
                ->where('status', 'Active')
                ->where('mail_for', 'agent')
                ->first();
            $tickets_subject = "SLA Reminder: For Ticket $tickets->support_ticket_id"; 
            $subject =  $mailTemplate->subject ?? $tickets_subject;
            $body =  $mailTemplate->body ?? '';
            $subject = str_replace("{ticket_number}", $tickets->support_ticket_id, $subject);
            $body = str_replace(
                [
                    "{assigned_user}",
                    "{ticket_number}",
                    "{ticket_subject}",
                    "{ticket_created}",
                    "{first_response_due}",
                    "{resolve_time_due}"
                ],
                [
                    $tickets->assigned_agent_name ?? 'Agent',
                    $tickets->support_ticket_id,
                    $tickets->subject,
                    optional($tickets->created_at)->format('d M Y, H:i:s A T'),
                    optional($tickets->first_response_time)->format('d M Y, H:i:s A T'),
                    optional($tickets->resolved_time)->format('d M Y, H:i:s A T'),
                ],
                $body
            );
            Mail::to($toEmails)->send(new SlaSendEmail($subject, $body, $tickets, $type));

            $notification = new EmailNotification();
            $notification->setConnection($newConnectionName);
            $notification->fill([
                "type" => $type,
                "ticket_id" => $tickets->id
            ]);
            $notification->save();
            // logEmail([
            //     "user_id" => $tickets->assignedUser->id,
            //     "email_template_id" => $mailTemplate->id,
            //     "from_email" => $sender_email,
            //     "module_name" => "Support Ticket",
            //     "entity_id" => $tickets->id,
            //     "status" => "sent",
            // ], $newConnectionName);
        } catch (Exception $e) {
            echo  $e->getMessage();
            exit;
        }
    }

    public function notifyAdminViaEmail($newConnectionName, $tickets, $adminUser, $type)
    {
        generateScheduleLogs('info', 'adminUser ' . json_encode($adminUser));
        $emailConfig = EmailConfigure::on($newConnectionName)
            ->where('type', 'custom')
            ->first();
        $smtpHost = $emailConfig->smtp_host ?? '';
        $smtpPort = $emailConfig->smtp_port ?? '';
        $smtpUser = $emailConfig->smtp_user ?? '';
        $smtpPassword = $emailConfig->smtp_password ?? '';
        $sender_name = $emailConfig->sender_name ?? '';
        $sender_email = $emailConfig->sender_email ?? '';
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $smtpHost,
            'mail.mailers.smtp.port' => $smtpPort,
            'mail.mailers.smtp.username' => $smtpUser,
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
            'mail.from.address' =>  $sender_email,
            'mail.from.name' => $sender_name,
        ]);
        try {
            $toEmails = $adminUser->email ?? '';
            $mailTemplate = MailTemplate::on($newConnectionName)->where("type", 'resolve_time_email_admin')->where('status', 'Active')->where('mail_for', 'admin')->first();
            
            $tickets_subject = "SLA Reminder: For Ticket $tickets->support_ticket_id";
            $subject =  $mailTemplate->subject ?? $tickets_subject;
            $body =  $mailTemplate->body ?? '';
            $subject = str_replace("{ticket_number}", $tickets->support_ticket_id, $subject);
            $body = str_replace(
                [
                    "{admin_user}",
                    "{assigned_user}",
                    "{ticket_number}",
                    "{ticket_subject}",
                    "{ticket_created}",
                    "{first_response_due}",
                    "{resolve_time_due}"
                ],
                [
                    $adminUser->username ?? 'Admin',
                    $tickets->assigned_agent_name ?? 'Agent',
                    $tickets->support_ticket_id,
                    $tickets->subject,
                    optional($tickets->created_at)->format('d M Y, H:i:s A T'),
                    optional($tickets->first_response_time)->format('d M Y, H:i:s A T'),
                    optional($tickets->resolved_time)->format('d M Y, H:i:s A T'),
                ],
                $body
            );
            Mail::to($toEmails)->send(new SlaSendEmail($subject, $body, $tickets, $type));
            $notification =  EmailNotification::on($newConnectionName);
            $notification = new EmailNotification();
            $notification->setConnection($newConnectionName);
            $notification->fill([
                "type" => $type,
                "ticket_id" => $tickets->id
            ]);
            $notification->save();
            // logEmail([
            //     "user_id" => $adminUser->id,
            //     "email_template_id" => $mailTemplate->id,
            //     "from_email" => $sender_email,
            //     "module_name" => "Support Ticket",
            //     "entity_id" => $tickets->id,
            //     "status" => "sent",
            // ], $newConnectionName);
        } catch (Exception $e) {
            echo  $e->getMessage();
            exit;
        }
    }
}
