<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class BackInStockMail extends Mailable
{
    public $user;
    public $item;

    public function __construct($user, $item)
    {
        $this->user = $user;
        $this->item = $item;
    }

    public function build()
    {
        return $this->subject('Item Back In Stock')
            ->view('emails.back-in-stock');
    }
}
