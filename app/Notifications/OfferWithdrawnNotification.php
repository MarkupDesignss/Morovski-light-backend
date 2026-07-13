<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;

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
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.offer_withdrawn'))
            ->line(__('messages.offer_withdrawn_by_user', [
                'user' => $this->buyer->name
            ]))
            ->line(__('messages.item_name', [
                'item' => $this->item->title
            ]))
            ->line(__('messages.offer_price', [
                'price' => $this->offerPrice
            ]));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'type' => 'offer_withdrawn',

            'title' => __('messages.offer_withdrawn'),
            'message' => __('messages.offer_withdrawn_by_user', [
                'user' => $this->buyer->name
            ]),

            'item_id' => $this->item->id,

            'item_image' => optional($this->item->images->first())->image_path
                ? asset(optional($this->item->images->first())->image_path)
                : null,

            'item_title' => $this->item->title, // accessor handles EN/DE ✔
            'item_desc' => $this->item->description,

            'buyer_id' => $this->buyer->id,
            'buyer_name' => $this->buyer->name,

            'offer_price' => $this->offerPrice,
        ];
    }
}
