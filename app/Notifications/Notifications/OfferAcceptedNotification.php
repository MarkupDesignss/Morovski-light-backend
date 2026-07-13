<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferAcceptedNotification extends Notification
{
    use Queueable;

    protected $offer;
    protected $seller;

    public function __construct($offer, $seller)
    {
        $this->offer = $offer;
        $this->seller = $seller;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Offer Accepted')
            ->line('Your offer has been accepted by ' . $this->seller->name);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer_accepted',
            'offer_id' => $this->offer->id,
            'item_image' => optional($this->offer->item->images->first())->image_path 
                    ? asset(optional($this->offer->item->images->first())->image_path)
                    : null,
            // 'item_image' => optional($this->offer->item->images->first())->image_path  ?? null,
            'item_id' => $this->offer->item->id,
            'item_title' => $this->offer->item->title,
            'item_desc' => $this->offer->item->description,
            'seller_name' => $this->seller->name,
        ];
    }
}
