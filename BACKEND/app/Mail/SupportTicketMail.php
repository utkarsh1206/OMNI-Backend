<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportTicketMail extends Mailable
{
    use SerializesModels;

    public $ticket;

    // Constructor to pass the ticket data
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        return $this->subject('New Support Ticket Created')
                    ->view('emails.support_ticket'); // Blade view file
    }
}
