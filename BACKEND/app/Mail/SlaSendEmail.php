<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SlaSendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    // public $attach;
    public $ticket;
    public $type;

    // Constructor to accept dynamic content
    public function __construct($subject, $content, $ticket, $type)
    {
        $this->subject = $subject;
        $this->content = $content;
        // $this->attach = $attach;
        $this->ticket = $ticket;
        $this->type = $type;
    }

    // Build the email
    public function build()
    {
        $email = $this->view('emails.sla_email')
            ->with([
                'content' => $this->content,
            ])
            ->subject($this->subject);
        // Add attachments
        // if (count($this->attach) > 0) {
        //     foreach ($this->attach as $attachment) {
        //         if (file_exists($attachment['path'])) {
        //             $email->attach($attachment['path'], [
        //                 'as' => $attachment['name'],
        //                 'mime' => $attachment['mime']
        //             ]);
        //         }
        //     }
        // }


        return $email;
    }
}
