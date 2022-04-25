<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
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
    public function __construct($name,$email,$message,$user,$reply, $back=false)
    {
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
        $this->back = $back;
        $this->reply = $reply;

        if($this->back){
            $this->subject = 'Potwierdzenie wysłania wiadomości';
        }
        else{
            $this->subject = empty($user) ? 'Wiadomość od gościa!' : 'Wiadomość od użytkownika!';
        }

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->back){
            return $this->markdown('emails.contact_back')->with([
                'name'=>$this->name,
                'email'=>$this->email,
                'message'=>$this->message,
                'subject'=>$this->subject,
            ])->subject($this->subject)->replyTo($this->reply);
        }

        return $this->markdown('emails.contact')->with([
            'name'=>$this->name,
            'email'=>$this->email,
            'message'=>$this->message,
            'subject'=>$this->subject,
        ])->subject($this->subject)->replyTo($this->reply);

    }
}
