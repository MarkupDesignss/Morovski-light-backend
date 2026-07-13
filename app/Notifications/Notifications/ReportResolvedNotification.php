<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Report;

class ReportResolvedNotification extends Notification
{
    use Queueable;

    public $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📢 Report Update')
            ->line('A report on your content has been reviewed.')
            ->line('Status: ' . ucfirst(str_replace('_', ' ', $this->report->status)))
            ->line('Admin Notes: ' . ($this->report->resolution_notes ?? 'N/A'))
            ->action('View Details', 'https://www.markupdesigns.net/sport-gems-web/account-layout/notifications')
            ->line('If you have questions, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report_resolved',
            'report_id' => $this->report->id,
            'status' => $this->report->status,
            'notes' => $this->report->resolution_notes,
        ];
    }
}
