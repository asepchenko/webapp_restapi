<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DocumentMail extends Mailable
{
    use Queueable, SerializesModels;
  
    public $body;
   
    public function __construct($body)
    {
        $this->body = $body;
    }

    public function build()
    {
        return $this->subject('Dokumen STT Balik')
                    ->view('emails.document');
    }
}
