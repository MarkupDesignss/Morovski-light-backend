<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return (new MailMessage)
            ->subject('Membership Request Rejected')
            ->line('Your request was rejected.')
            ->line('Reason: ' . $this->reason);
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Request Rejected',
            'message' => $this->reason,
        ];
    }
}
