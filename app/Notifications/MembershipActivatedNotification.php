<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class MembershipActivatedNotification extends Notification
{
    use Queueable;

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
            ->subject(__('messages.membership_activated'))
            ->line(__('messages.membership_activated_success'))

            ->when($this->membership, function ($mail) {
                return $mail->line(__('messages.plan_name', [
                    'plan' => $this->membership->name
                ]));
            })

            ->line(__('messages.enjoy_benefits'));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'title' => __('messages.membership_activated'),
            'message' => __('messages.membership_active_now'),

            'membership' => $this->membership->name ?? null,
            'membership_id' => $this->membership->id ?? null,
        ];
    }
}
