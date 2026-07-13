<?php

namespace App\Mail;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuctionOwnerMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $auction;

    /**
     * Create a new message instance.
     */
    public function __construct(Auction $auction)
    {
        $this->auction = $auction;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your auction has ended',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.auction_owner',
            with: [
                'auction' => $this->auction,
                'item' => $this->auction->item,
                'winner' => $this->auction->winnerUser,
                'amount' => $this->auction->current_bid,
            ],
        );
    }
}
