<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class BusinessStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $status;

    public function __construct($user, $status)
    {
        $this->user = $user;
        $this->status = $status;
    }

    public function build()
    {
        return $this->subject('Business Account Status Update')
            ->view('emails.business_status');
    }
}
