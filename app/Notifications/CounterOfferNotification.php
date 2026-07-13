<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class CounterOfferNotification extends Notification
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
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.counter_offer_received'))
            ->line(__('messages.counter_offer_sent_by_user', [
                'user' => $this->seller->name
            ]))
            ->line(__('messages.counter_price', [
                'price' => $this->offer->counter_price
            ]));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'type' => 'counter_offer',

            'message' => __('messages.counter_offer_sent_by_user', [
                'user' => $this->seller->name
            ]),

            'item_image' => optional($this->offer->item->images->first())->image_path
                ? asset(optional($this->offer->item->images->first())->image_path)
                : null,

            'item_id' => $this->offer->item->id,
            'item_title' => $this->offer->item->title,
            'item_desc' => $this->offer->item->description,

            'offer_id' => $this->offer->id,
            'counter_price' => $this->offer->counter_price,

            'seller_name' => $this->seller->name,
        ];
    }
}
