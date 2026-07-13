<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;
    protected $order;
    protected $type;

    public function __construct($order, $type)
    {
        $this->order = $order;
        $this->type = $type; // buyer या seller
    }

    public function via($notifiable)
    {
        return ['database']; // future me mail भी add कर सकते हो
    }

    public function toDatabase($notifiable)
    {
        $buyerName = $this->order->buyer->full_name ?? 'Someone';
        $sellerName = $this->order->seller->full_name ?? 'Seller';

        if ($this->type === 'buyer') {
            return [
                'title' => 'Order Confirmed',
                'message' => "Hi {$buyerName}, your order #{$this->order->order_number} has been successfully placed.",
                'order_number' => $this->order->order_number,
            ];
        }

        return [
            'title' => 'New Order Received',
            'message' => "Hi {$sellerName}, your product has been purchased by {$buyerName}. Order #{$this->order->order_number}.",
            'order_number' => $this->order->order_number,
        ];
    }
}
