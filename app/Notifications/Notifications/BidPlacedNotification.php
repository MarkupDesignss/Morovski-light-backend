<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BidPlacedNotification extends Notification
{
    use Queueable;

    protected $auction;
    protected $bidder;
    protected $amount;

    /**
     * Create a new notification instance.
     */
    public function __construct($auction, $bidder, $amount)
    {
        $this->auction = $auction;
        $this->bidder = $bidder;
        $this->amount = $amount;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database']; //  in-app notification
    }

    /**
     * Store notification in database
     */
    public function toDatabase($notifiable)
    {
        return [
             'type' => 'bid_placed',
            'auction_id' => $this->auction->id,
            'item_id' => $this->auction->item->id,
            'item_name' => $this->auction->item->title ?? null,
            'item_image' => $this->auction->item->images->first()->image_path ?? null,
            'bid_amount' => $this->amount,
            'bidder_id' => $this->bidder->id,
            'bidder_name' => $this->bidder->full_name ?? $this->bidder->name ?? 'User',
            'message' => ($this->bidder->full_name ?? 'Someone') . ' placed a bid of ₹' . $this->amount . ' on your item',
        ];
    }

    /**
     * (Optional) for API responses
     */
    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}
