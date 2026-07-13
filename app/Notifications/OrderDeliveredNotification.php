<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class OrderDeliveredNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.order_delivered'))
            ->line(__('messages.order_confirmed_by_buyer', [
                'order_no' => $this->order->order_number
            ]))
            ->line(__('messages.payout_processed_soon'));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'type' => 'order_delivered',

            'title' => __('messages.order_delivered'),
            'message' => __('messages.order_delivered_success'),

            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,

            'item_image' => optional($this->order->item->images->first())->image_path
                ? asset(optional($this->order->item->images->first())->image_path)
                : null,

            'item_title' => $this->order->item->title, // accessor handles EN/DE ✔
            'item_desc' => $this->order->item->description,

            'order_number' => $this->order->order_number,
        ];
    }
}
