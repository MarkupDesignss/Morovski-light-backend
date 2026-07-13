<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMembershipRequestNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $membership;

    public function __construct($user, $membership = null)
    {
        $this->user = $user;
        $this->membership = $membership;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Membership Request')
            ->line($this->user->name . ' has requested a membership.')
            ->when($this->membership, function ($mail) {
                return $mail->line('Requested Plan: ' . $this->membership->name);
            })
            ->line('Please review and take action.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Membership Request',
            'message' => $this->user->name . ' requested membership approval.',
            'user_id' => $this->user->id,
            'membership' => $this->membership?->name,
        ];
    }
}
