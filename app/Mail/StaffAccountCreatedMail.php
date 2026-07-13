<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $plainPassword;
    public $accountType;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $plainPassword, $accountType)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
        $this->accountType = $accountType;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Your {$this->accountType} Account Credentials")
                    ->view('emails.staff-account-created');
    }
}