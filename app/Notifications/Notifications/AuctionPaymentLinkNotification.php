<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return ['mail', 'database']; // dono jayega
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('You won the auction 🎉')
            ->line('Congratulations! You won the auction.')
            ->action('Pay Now', $this->url)
            ->line('Please complete payment quickly.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'You won the auction',
            'order_id' => $this->order->id,
            'payment_link' => $this->url
        ];
    }
}
