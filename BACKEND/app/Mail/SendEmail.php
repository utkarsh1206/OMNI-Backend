<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $attach;

    // Constructor to accept dynamic content
    public function __construct($subject, $content,$attach=[])
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
        if(count($this->attach) > 0)
        {
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
