<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $reply;
    public $message;
    public $subject;
    public $back;

    /**
     * Create a new message instance.
     *
     * @param $name
     * @param $email
     * @param $message
     * @param $user
     * @param bool $back
     */
    public function __construct($email,$message,$subject)
    {
        $this->email = $email;
        $this->message = $message;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.admin_message')->with([
            'email'=>$this->email,
            'message'=>$this->message,
            'subject'=>$this->subject,
        ])->subject($this->subject);

    }
}
