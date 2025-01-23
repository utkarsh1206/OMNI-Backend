<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supportticket;
use Carbon\Carbon;
use App\Models\Tenants;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\EmailConfigure;
use App\Models\MailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Mail\SendEmail;

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
        $allTenants = Tenants::where("is_delete", "No")
        ->orderBy('id')
        ->chunkById(100, function ($tenants) {
            foreach ($tenants as $tenant) {
                $newConnectionName = str_replace(".", "_", $tenant->domain_name);
                $databaseName = str_replace(".", "_", $tenant->domain_name);
                $this->establishDatabaseConnect($newConnectionName, $databaseName);
                DB::reconnect($newConnectionName);
                // Fetch support tickets for each tenant
                $supportTickets = Supportticket::on($newConnectionName)
                    ->get();
                if ($supportTickets->count()) {
                    foreach ($supportTickets as $ticket) {
                        $currentTime = Carbon::now();
                        if ($currentTime->diffInSeconds($ticket->first_response_time) == 0) {
                            $this->notifyViaEmail($newConnectionName,$ticket);
                        }
                        if ($currentTime->diffInSeconds($ticket->alert_before_response_notification_time) == 0) {
                            $this->notifyViaEmail($newConnectionName,$ticket);
                        }
                        if ($currentTime->diffInSeconds($ticket->alert_before_resolve_notification_time) == 0) {
                            $this->notifyViaEmail($newConnectionName,$ticket);
                        }
                        if ($currentTime->diffInSeconds($ticket->resolved_time) == 0) {
                            $this->notifyViaEmail($newConnectionName,$ticket);
                            $adminUsers = User::on($newConnectionName)
                            ->where("type","=",0)->first();
                            $this->notifyAdminViaEmail($newConnectionName,$ticket,$adminUsers);
                        }
                     }
                    
                    $this->info("Tenant: {$tenant->domain_name} - Notified " . $supportTickets->count());
                }
            }
        });
    }

    public function notifyViaEmail($newConnectionName,$tickets)
    {
        $emailConfig = EmailConfigure::on($newConnectionName)->where('type', 'custom')->first();
        $smtpHost = $emailConfig->smtp_host ?? '';
        $smtpPort = $emailConfig->smtp_port ?? '';
        $smtpUser = $emailConfig->smtp_user ?? '';
        $smtpPassword = $emailConfig->smtp_password ?? '';
        $sender_name = $emailConfig->sender_name ?? '';
        $sender_email = $emailConfig->sender_email ?? '';
        config([
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
                $mailTemplate = MailTemplate::on($newConnectionName)->where("type","sla_email")->first();
                $subject =  $mailTemplate->subject ?? '';
                $body =  $mailTemplate->body ?? '';
                $subject = str_replace("{ticket_number}",$tickets->support_ticket_id,$subject);
                $body = str_replace("{ticket_number}",$tickets->support_ticket_id,$body);
                Mail::to($toEmails)->send(new SendEmail($subject, $body));
            } catch (Exception $e) {
                echo  $e->getMessage();
                exit;
            }
    }

    public function notifyAdminViaEmail($newConnectionName,$tickets,$adminUser)
    {
        $emailConfig = EmailConfigure::on($newConnectionName)->where('type', 'custom')->first();
        $smtpHost = $emailConfig->smtp_host ?? '';
        $smtpPort = $emailConfig->smtp_port ?? '';
        $smtpUser = $emailConfig->smtp_user ?? '';
        $smtpPassword = $emailConfig->smtp_password ?? '';
        $sender_name = $emailConfig->sender_name ?? '';
        $sender_email = $emailConfig->sender_email ?? '';
        config([
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
                $mailTemplate = MailTemplate::on($newConnectionName)->where("type","sla_email")->first();
                $subject =  $mailTemplate->subject ?? '';
                $body =  $mailTemplate->body ?? '';
                $subject = str_replace("{ticket_number}",$tickets->support_ticket_id,$subject);
                $body = str_replace("{ticket_number}",$tickets->support_ticket_id,$body);
                Mail::to($toEmails)->send(new SendEmail($subject, $body));
            } catch (Exception $e) {
                echo  $e->getMessage();
                exit;
            }
    }

    public function establishDatabaseConnect($newConnectionName,$databaseName)
    {
        Config::set("database.connections.$newConnectionName", [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => $databaseName,
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);
    }
}
