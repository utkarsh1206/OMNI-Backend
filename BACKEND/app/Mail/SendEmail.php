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
    public $ticket;

    // Constructor to accept dynamic content
    public function __construct($subject, $content, $attach = [], $ticket = null)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->attach = $attach;
        $this->ticket = $ticket;
    }

    // Build the email
    public function build()
    {
        if (!empty($this->ticket)) {
            $email = $this->view('emails.support_ticket')
                ->with([
                    'content' => $this->content,
                ])
                ->subject($this->subject);
        } else {
            $email = $this->view('emails.send')
                ->with([
                    'content' => $this->content,
                ])
                ->subject($this->subject);
        }

        // Add attachments
        if (count($this->attach) > 0) {
            foreach ($this->attach as $attachment) {
                if (file_exists($attachment['path'])) {
                    $email->attach($attachment['path'], [
                        'as' => $attachment['name'],
                        'mime' => $attachment['mime']
                    ]);
                }
            }
        }


        return $email;
    }
}
