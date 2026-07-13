<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionWinnerPaymentNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $paymentUrl;

    public function __construct($order, $paymentUrl)
    {
        $this->order = $order;
        $this->paymentUrl = $paymentUrl;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // BOTH
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('You Won the Auction!')
            ->line('Congratulations! You have won the auction.')
            ->line('Order Number: ' . $this->order->order_number)
            ->action('Pay Now', $this->paymentUrl)
            ->line('Complete your payment to confirm your order.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'You won the auction! Complete your payment.',
            'order_number' => $this->order->order_number,
            'payment_url' => $this->paymentUrl
        ];
    }
}
