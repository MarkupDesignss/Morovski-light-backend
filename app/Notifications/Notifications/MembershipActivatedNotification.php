<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return (new MailMessage)
            ->subject('Membership Activated')
            ->line('Your membership has been activated successfully.')
            ->when($this->membership, function ($mail) {
                return $mail->line('Plan: ' . $this->membership->name);
            })
            ->line('Enjoy your benefits!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Membership Activated',
            'message' => 'Your membership is now active.',
            'membership' => $this->membership->name,
            'membership_id' => $this->membership->id,
        ];
    }
}
