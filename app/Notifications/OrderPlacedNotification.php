<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;


class OrderPlacedNotification extends Notification
{
    use Queueable;
    protected $order;
    protected $type;

    public function __construct($order, $type)
    {
        $this->order = $order;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database'];
    }


    public function toDatabase($notifiable)
    {
        App::setLocale($notifiable->language ?? 'en');

        $buyerName = $this->order->buyer->full_name ?? __('messages.someone');
        $sellerName = $this->order->seller->full_name ?? __('messages.seller');

        if ($this->type === 'buyer') {
            return [
                'title' => __('messages.order_confirmed'),

                'message' => __('messages.order_confirmed_msg', [
                    'user' => $buyerName,
                    'order_no' => $this->order->order_number
                ]),

                'order_number' => $this->order->order_number,
            ];
        }

        return [
            'title' => __('messages.new_order_received'),

            'message' => __('messages.new_order_received_msg', [
                'seller' => $sellerName,
                'buyer' => $buyerName,
                'order_no' => $this->order->order_number
            ]),

            'order_number' => $this->order->order_number,
        ];
    }
}
