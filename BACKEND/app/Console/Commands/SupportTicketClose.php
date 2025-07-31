<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supportticket;
use Carbon\Carbon;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\EmailConfigure;
use App\Models\MailTemplate;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Mail\SendEmail;

class SupportTicketClose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:support-ticket-close {uin?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is use to mark the support ticket from Resolved to Closed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        generateScheduleLogs('info', 'Support Ticket Close started for all tenants.');
        getDbConnection($this->argument("uin"), true);
        $this->updateSupportTicket();
        // Tenant::orderBy('id')  // Ensures consistent ordering by the ID column
        //     ->chunkById(100, function ($tenants) {
        //         foreach ($tenants as $tenant) {
        //             $newConnectionName = str_replace(".", "_", $tenant->domain_name);
        //             $databaseName = str_replace(".", "_", $tenant->domain_name);
        //             establishDatabaseConnect($newConnectionName, $databaseName);
        //             DB::reconnect($newConnectionName);
        //             // Fetch support tickets for each tenant
        //             $supportTickets = Supportticket::on($newConnectionName)
        //                 ->where("default_status", "Resolved")
        //                 ->whereDate('updated_at', '<=', Carbon::now()->subDays(7))
        //                 ->get();
        //             if ($supportTickets->count()) {
        //                 foreach ($supportTickets as $ticket) {
        //                     $ticket->default_status = 'Closed';
        //                     $ticket->save();
        //                     $this->notifyViaEmail($newConnectionName, $ticket);
        //                 }

        //                 $this->info("Tenant: {$tenant->domain_name} - Updated " . $supportTickets->count() . " tickets to 'Closed'.");
        //             }
        //         }
        //     });
        generateScheduleLogs('info', 'Support Ticket Close completed for all tenants.');
    }

    public function updateSupportTicket()
    {
        try {
            $supportTickets = Supportticket::where("default_status", "Resolved")
                ->whereDate('updated_at', '<=', Carbon::now()->subDays(7))
                ->get();
            if ($supportTickets->count()) {
                foreach ($supportTickets as $ticket) {
                    $ticket->default_status = 'Closed';
                    $ticket->save();
                    $this->notifyViaEmail($ticket);
                }

                $this->info("Tenant - Updated " . $supportTickets->count() . " tickets to 'Closed'.");
            }
        } catch (Exception $e) {
            generateLogs('error', 'Error in notifyViaEmail : ', $e->getMessage());
            exit;
        }
    }

    public function notifyViaEmail($tickets)
    {
        $emailConfig = EmailConfigure::where('type', 'custom')->first();
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

        try {
            $toEmails = "khanjan.kukadiya@stltech.in";
            $mailTemplate = MailTemplate::where("type", "closed_ticket")->first();
            $subject =  $mailTemplate->subject ?? '';
            $body =  $mailTemplate->body ?? '';
            $subject = str_replace("{ticket_number}", $tickets->support_ticket_id, $subject);
            $body = str_replace("{ticket_number}", $tickets->support_ticket_id, $body);
            Mail::to($toEmails)->send(new SendEmail($subject, $body));

            // logEmail([
            //     "user_id" => $tickets->assign_to_id,
            //     "email_template_id" => $mailTemplate->id,
            //     "from_email" => $sender_email,
            //     "module_name" => "Support Ticket",
            //     "entity_id" => $tickets->id,
            //     "status" => "sent",
            // ], $newConnectionName);
        } catch (Exception $e) {
            generateLogs('error', 'Error in notifyViaEmail : ', $e->getMessage());
            exit;
        }
    }
}
