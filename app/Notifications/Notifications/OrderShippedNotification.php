<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return (new MailMessage)
            ->subject('Order Shipped')
            ->line('Your order #' . $this->order->order_number . ' has been shipped.')
            ->line('You can track or view your order in your dashboard.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_shipped',
            'order_id' => $this->order->id,
            'item_image' => optional($this->order->item->images->first())->image_path 
                            ? asset(optional($this->order->item->images->first())->image_path)
                            : null,
            'item_image' => $this->order->item->images->first() ?? null,
            'item_title' => $this->order->item->title,
            'item_desc' => $this->order->item->description,
            'order_number' => $this->order->order_number,
            'message' => 'Your order has been shipped.'
        ];
    }
}
