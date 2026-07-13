<?php

namespace App\Listeners;

use App\Events\AuctionEnded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuctionWinnerMail;
use App\Mail\AuctionOwnerMail;

class SendAuctionEndedEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AuctionEnded $event)
    {
        $auction = $event->auction;

        $winner = $auction->winnerUser;
        $owner = $auction->item->user;

        // Send mail to winner
        if ($winner) {
            Mail::to($winner->email)->send(new AuctionWinnerMail($auction));
        }

        // Send mail to auction owner
        if ($owner) {
            Mail::to($owner->email)->send(new AuctionOwnerMail($auction));
        }
    }
}
