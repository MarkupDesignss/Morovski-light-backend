<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

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
        // Set user language
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.auction_won_pay'))
            ->line(__('messages.congrats_auction_won'))
            ->line(__('messages.order_number', [
                'order_no' => $this->order->order_number
            ]))
            ->action(__('messages.pay_now'), $this->paymentUrl)
            ->line(__('messages.complete_payment_confirm_order'));
    }

    public function toArray($notifiable)
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'message' => __('messages.auction_won_pay'),
            'order_number' => $this->order->order_number,
            'payment_url' => $this->paymentUrl
        ];
    }
}
