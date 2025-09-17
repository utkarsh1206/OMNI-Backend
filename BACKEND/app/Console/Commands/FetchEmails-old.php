<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Mail\SupportTicketMail;
use Illuminate\Support\Facades\Mail;

//use Webklex\IMAP\Facades\IMAP;

ini_set('memory_limit', '1024M'); // or another value like '2048M'

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch';
    protected $description = 'Fetch unread emails from an IMAP server and store them in the database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ini_set('memory_limit', '1024M'); // or another value like '2048M'

        // Fetch IMAP settings from the .env file

        $emailConfig = DB::table('ha11.email_configures')
            ->where('type', 'gmail')
            ->first();
        $host = $emailConfig->imap_host;
        $port = $emailConfig->imap_port;
        $username = $emailConfig->imap_user;
        $password = $emailConfig->imap_password;
        $default_type = $emailConfig->default_type;
        $default_status = $emailConfig->default_status;
        $encryption = 'ssl';
        $smtpHost = $emailConfig->smtp_host;
        $smtpPort = $emailConfig->smtp_port;
        $smtpUser = $emailConfig->smtp_user;
        $smtpPassword = $emailConfig->smtp_password;
        $sender_name = $emailConfig->sender_name;
        $sender_email = $emailConfig->sender_email;


        // Configure the IMAP client with the environment variables
        $client = Client::make([
            'host'       => $host,
            'port'       => $port,
            'encryption' => $encryption,
            'username'   => $username,
            'password'   => $password,
            'protocol'   => 'imap',
            /*'options' => [
                'fetch_order' => 'desc',
            ]*/
        ]);

        // Connect to the server
        $client->connect();

        // Get the inbox folder
        $folder = $client->getFolder('INBOX', 'UNSEEN');

        // Fetch only unread emails
        //$messages = $folder->messages()->whereSeen(false)->get();
        /*
        $messages = $folder->messages()->whereSeen(false)   // Sort by the most recent email
        ->limit(1)                  // Limit to 1 email (the latest one)
        ->get();
        */
        $messages = $folder->messages()->unseen()->get();

        config([
            'mail.mailers.smtp.host' => $smtpHost,
            'mail.mailers.smtp.port' => $smtpPort,
            'mail.mailers.smtp.username' => $smtpUser,
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.mailers.smtp.encryption' => ($smtpPort == 587) ? 'TLS' : 'SSL',
            'mail.from.address' =>  $sender_email,
            'mail.from.name' => $sender_name,
        ]);
        // Process each unread email
        foreach ($messages as $message) {
            $from = $message->getFrom();
            $emailAddress = $from[0]->mail;
            $subject = $message->getSubject();
            $subject_final =  $subject[0];
           
            $attachment_urls = [];
            if ($message->hasAttachments()) {
                // Loop through attachments
                foreach ($message->getAttachments() as $attachment) {
                    $filename1 = $attachment->getName();
                    $filename = now()->format('Y_m_d_H_i_s') . '_' . $filename1;
                    $fileContent = $attachment->getContent();
                    $path = 'support_ticket/' . $filename;
                    $attachment_urls[] = $path;
                    // Use Storage facade to save the file in the public disk
                    Storage::disk('public')->put($path, $fileContent);
                }
            }
            // Create a new email record
            $messageTextBody = $message->getTextBody();  // Example message body
            // Insert record manually
            $lastInsertId = DB::table('ha11.supportticket')->insertGetId([
                'product_id' => $emailConfig->product_id,
                'campaign_id' => $emailConfig->campaign_id,
                'inbound_group_id' => $emailConfig->inbound_id,
                'contact_id' => 0,
                'assign_to_id' => 0,
                'default_type' => $default_type,
                'default_priority' => 'High',
                'default_status' => $default_status,
                'subject' => $subject_final,
                'departments' => '',
                'to_emails' => $emailAddress,
                'description' => ($messageTextBody) ? $messageTextBody : "",
                'attachment' => json_encode($attachment_urls),
                'created_at' => now(), // Optional if using timestamps
                'updated_at' => now(), // Optional if using timestamps
            ]);
            $datePrefix = now()->format('dmy'); // Get the current date in ddmmyy format
            $supportTicketId = $datePrefix . $lastInsertId;

            // Step 3: Update the `support_ticket_id` column with the generated value
            DB::table('ha11.supportticket')
                ->where('id', $lastInsertId)
                ->update(['support_ticket_id' => $supportTicketId]);
            $message->setFlag('\\Seen');
            //$message->markAsRead();

            $ticket = (object) [
                'ticket_no' => $supportTicketId,
                'priority' => 'High',
                'status' => $default_status,
                'subject' =>  $subject_final,
                'body' => $messageTextBody
            ];
            // Send the email using the Mailable
            Mail::to($emailAddress)->send(new SupportTicketMail($ticket));
            // Set SEEN flag
            /*
            $supportticket = SupportTicket::create([
                'product_id' => 1,
                'campaign_id' => 1,
                'inbound_group_id' => 1,
                'contact_id' => 1,
                'assign_to_id' => 1,
                'default_type' => 'Help',
                'default_priority' =>'High',
                'default_status' => 'Open',
                'subject' => $subject_final,
                'departments' => '',
                'to_emails' => $emailAddress,
                'description' => $message->getTextBody(),
                'attachment' => '',
                'status' => 'Active'
            ]);
            */
            /*   Email::create([
                'subject' => $message->getSubject(),
                'from' => $message->getFrom(),
                'body' => $message->getTextBody(),
                'received_at' => $message->getDate(),
            ]);
            */

            // Optionally mark the email as read after fetching it
            // $message->markAsRead();
        }

        $this->info('Unread emails have been fetched and saved to the database.');
    }
}
