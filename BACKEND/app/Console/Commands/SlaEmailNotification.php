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
use App\Mail\SendEmail;
use App\Models\EmailNotification;

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
                    $supportTickets = SupportTicket::on($newConnectionName)
                        ->where(function ($query) {
                            $query->whereNull('is_notified')
                                ->orWhere('is_notified', '!=', 4);
                        })
                        ->whereNull('actual_resolved_time')
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
            $toEmails = $tickets->assignedUser->email ?? '';
            $mailTemplate = MailTemplate::on($newConnectionName)->where("type", "sla_email")->first();
            $subject =  $mailTemplate->subject ?? '';
            $body =  $mailTemplate->body ?? '';
            $subject = str_replace("{ticket_number}", $tickets->support_ticket_id, $subject);
            $body = str_replace("{ticket_number}", $tickets->support_ticket_id, $body);
            Mail::to($toEmails)->send(new SendEmail($subject, $body));

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
        $emailConfig = EmailConfigure::on($newConnectionName)->where('type', 'custom')->first();
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
            $mailTemplate = MailTemplate::on($newConnectionName)->where("type", "sla_email")->first();
            $subject =  $mailTemplate->subject ?? '';
            $body =  $mailTemplate->body ?? '';
            $subject = str_replace("{ticket_number}", $tickets->support_ticket_id, $subject);
            $body = str_replace("{ticket_number}", $tickets->support_ticket_id, $body);
            Mail::to($toEmails)->send(new SendEmail($subject, $body));
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
