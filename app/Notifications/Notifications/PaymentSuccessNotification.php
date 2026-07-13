<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

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
        return (new MailMessage)
            ->subject('Payment Successful')
            ->line('Your payment has been successfully completed.')
            ->when($this->membership, function ($mail) {
                return $mail->line('Membership: ' . $this->membership->name);
            })
            ->line('Your membership is now active.' .$this->membership->name)
            ->line('Thank you for using our service.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment Successful',
            'message' => 'Your payment was successful and membership is active.',
            'membership' => $this->membership->name,
            'membership_id' => $this->membership->id,
        ];
    }
}
