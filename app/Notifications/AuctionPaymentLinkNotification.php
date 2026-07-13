<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class AuctionPaymentLinkNotification extends Notification
{
    public $order, $url;

    public function __construct($order, $url)
    {
        $this->order = $order;
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('messages.auction_won'))
            ->line(__('messages.congrats_auction_won'))
            ->line(__('messages.order_number', ['order_no' => $this->order->id]))
            ->action(__('messages.pay_now'), $this->url)
            ->line(__('messages.complete_payment_quickly'));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => __('messages.auction_won'),
            'order_id' => $this->order->id,
            'payment_link' => $this->url
        ];
    }
}
