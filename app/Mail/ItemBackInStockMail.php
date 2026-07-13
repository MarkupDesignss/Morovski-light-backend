<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ItemBackInStockMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $item;

    public function __construct($user, $item)
    {
        $this->user = $user;
        $this->item = $item;
    }

    public function build()
    {
        return $this->subject('Item Back in Stock')
                    ->view('emails.item-back-in-stock');
    }
}