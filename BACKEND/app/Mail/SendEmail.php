<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $attach;

    // Constructor to accept dynamic content
    public function __construct($subject, $content,$attach)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->attach = $attach;
    }

    // Build the email
    public function build()
    {
        $email = $this->view('emails.send') // This is the view that will be used to render the email
        ->with([
            'content' => $this->content,
        ])
        ->subject($this->subject);
        // Add attachments
        foreach ($this->attach as $attachment) {
            if (file_exists($attachment['path'])) {
                $email->attach($attachment['path'], [
                    'as' => $attachment['name'], 
                    'mime' => $attachment['mime']
                ]);
            }
        }

        return $email;
    }
}
