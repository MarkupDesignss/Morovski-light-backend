<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class PaymentSuccessNotification extends Notification
{
    use Queueable, SerializesModels;

    protected $membership;

    public function __construct($membership = null)
    {
        $this->membership = $membership;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.payment_successful'))
            ->line(__('messages.payment_successful_msg'))

            ->when($this->membership, function ($mail) {
                return $mail->line(__('messages.membership_name', [
                    'plan' => $this->membership->name
                ]));
            })

            ->line(__('messages.membership_active_now'))
            ->line(__('messages.thank_you_service'));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'title' => __('messages.payment_successful'),
            'message' => __('messages.payment_membership_active'),

            'membership' => $this->membership->name ?? null,
            'membership_id' => $this->membership->id ?? null,
        ];
    }
}
