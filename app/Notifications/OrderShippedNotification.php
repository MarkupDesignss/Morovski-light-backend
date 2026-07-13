<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class OrderShippedNotification extends Notification
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
            ->subject(__('messages.order_shipped'))
            ->line(__('messages.order_shipped_msg', [
                'order_no' => $this->order->order_number
            ]))
            ->line(__('messages.track_order_dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'type' => 'order_shipped',

            'title' => __('messages.order_shipped'),
            'message' => __('messages.order_shipped_short'),

            'order_id' => $this->order->id,

            'item_image' => optional($this->order->item->images->first())->image_path
                ? asset(optional($this->order->item->images->first())->image_path)
                : null,

            'item_title' => $this->order->item->title, // accessor handles EN/DE ✔
            'item_desc' => $this->order->item->description,

            'order_number' => $this->order->order_number,
        ];
    }   
}