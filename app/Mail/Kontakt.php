<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Kontakt extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $from, $subject, $message, $attachments)
    {
        $this->name = $name;
        $this->reply = $from;
        $this->subject = $subject;
        $this->message = $message;
        $this->attachedFiles = $attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->from($this->reply, $this->name)
            ->subject($this->subject)
            ->text('kontakt.mail')
            ->with('messageText', $this->message);

        foreach($this->attachedFiles as $attachment){
            $mail->attachData(file_get_contents($attachment->getRealPath()), $attachment->getClientOriginalName());  
        }
        return $mail;
    }
}
