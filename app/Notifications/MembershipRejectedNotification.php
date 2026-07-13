<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class MembershipRejectedNotification extends Notification
{
    protected $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.membership_request_rejected'))
            ->line(__('messages.request_rejected'))
            ->line(__('messages.reason', [
                'reason' => $this->reason
            ]));
    }

    public function toArray($notifiable)
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'title' => __('messages.request_rejected'),
            'message' => $this->reason,
        ];
    }
}
