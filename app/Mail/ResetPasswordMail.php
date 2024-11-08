<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
  
    public $body;
   
    public function __construct($body)
    {
        $this->body = $body;
    }

    public function build()
    {
        return $this->subject('Reset Password')
                    ->view('emails.reset-password');
    }
}
