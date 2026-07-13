<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ShareItemLinkMail extends Mailable
{
    public $links;

    public function __construct($links)
    {
        $this->links = $links;
    }

    public function build()
    {
        return $this->subject('Shared Product Links')
            ->view('emails.share-item-links');
    }
}