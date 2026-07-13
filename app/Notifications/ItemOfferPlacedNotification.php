<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class ItemOfferPlacedNotification extends Notification
{
    use Queueable;

    protected $item;
    protected $buyer;
    protected $offer;

    public function __construct($item, $buyer, $offer)
    {
        $this->item = $item;
        $this->buyer = $buyer;
        $this->offer = $offer;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }


    public function toMail(object $notifiable): MailMessage
    {
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.new_offer_received'))
            ->line(__('messages.offer_placed_by_user', [
                'user' => $this->buyer->name
            ]))
            ->line(__('messages.item_name', [
                'item' => $this->item->title
            ]))
            ->line(__('messages.offer_price', [
                'price' => $this->offer->offer_price
            ]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'offer_placed',
            'offer_id' => $this->offer->id,
            'item_id' => $this->item->id,
            'item_image' => $this->item->images->first()->image_path ?? null,
            'item_title' => $this->item->title,
            'item_desc' => $this->item->description,
            'buyer_id' => $this->buyer->id,
            'buyer_name' => $this->buyer->name,
            'offer_price' => $this->offer->offer_price,
        ];
    }
}
