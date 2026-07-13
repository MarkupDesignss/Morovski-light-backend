<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

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
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.new_membership_request'))
            ->line(__('messages.membership_requested_by_user', [
                'user' => $this->user->name
            ]))

            ->when($this->membership, function ($mail) {
                return $mail->line(__('messages.requested_plan', [
                    'plan' => $this->membership->name
                ]));
            })

            ->line(__('messages.review_and_take_action'));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'title' => __('messages.new_membership_request'),

            'message' => __('messages.membership_requested_by_user', [
                'user' => $this->user->name
            ]),

            'user_id' => $this->user->id,
            'membership' => $this->membership?->name,
        ];
    }
}
