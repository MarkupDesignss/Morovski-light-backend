<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OfferWithdrawnNotification extends Notification
{
    use Queueable;

    protected $item;
    protected $buyer;
    protected $offerPrice;

    public function __construct($item, $buyer, $offerPrice)
    {
        $this->item = $item;
        $this->buyer = $buyer;
        $this->offerPrice = $offerPrice;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Offer Withdrawn')
            ->line($this->buyer->name . ' has withdrawn their offer.')
            ->line('Item: ' . $this->item->title)
            ->line('Offer Price: ' . $this->offerPrice);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer_withdrawn',
            'item_id' => $this->item->id,
            'item_image' => optional($this->offer->item->images->first())->image_path 
                    ? asset(optional($this->offer->item->images->first())->image_path)
                    : null,
            // 'item_image' => optional($this->item->images->first())->image_path ?? null,
            'item_id' => $this->item->id,
            'item_title' => $this->item->title,
            'item_desc' => $this->item->description,
            'buyer_id' => $this->buyer->id,
            'buyer_name' => $this->buyer->name,
            'offer_price' => $this->offerPrice,
        ];
    }
}