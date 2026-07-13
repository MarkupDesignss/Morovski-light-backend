<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

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
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.offer_accepted'))
            ->line(__('messages.offer_accepted_by_user', [
                'user' => $this->seller->name
            ]));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'type' => 'offer_accepted',

            'title' => __('messages.offer_accepted'),
            'message' => __('messages.offer_accepted_by_user', [
                'user' => $this->seller->name
            ]),

            'offer_id' => $this->offer->id,

            'item_image' => optional($this->offer->item->images->first())->image_path
                ? asset(optional($this->offer->item->images->first())->image_path)
                : null,

            'item_id' => $this->offer->item->id,
            'item_title' => $this->offer->item->title, // accessor handles EN/DE ✔
            'item_desc' => $this->offer->item->description,

            'seller_name' => $this->seller->name,
        ];
    }
}
