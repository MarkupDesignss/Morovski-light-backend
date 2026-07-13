<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return (new MailMessage)
            ->subject('Order Delivered')
            ->line('Order #' . $this->order->order_number . ' has been confirmed by buyer.')
            ->line('Your payout will be processed soon.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_delivered',
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'item_image' => optional($this->order->item->images->first())->image_path 
                    ? asset(optional($this->order->item->images->first())->image_path)
                    : null,
            // 'item_image' => $this->order->item->images->first() ?? null,
            'item_title' => $this->order->item->title,
            'item_desc' => $this->order->item->description,
            'order_number' => $this->order->order_number,
            'message' => 'Order has been successfully delivered.'
        ];
    }
}