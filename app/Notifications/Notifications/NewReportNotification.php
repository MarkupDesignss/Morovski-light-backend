<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Report;

class NewReportNotification extends Notification
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
            ->subject('🚨 New Report Submitted')
            ->line('A new report has been submitted by a user.')
            ->line('Reason: ' . $this->report->reason)
            ->line('Description: ' . $this->report->description)
            ->action('View Report', url('/admin/reports/' . $this->report->id))
            ->line('Please review it as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_report',
            'report_id' => $this->report->id,
            'reason' => $this->report->reason,
            'reporter_id' => $this->report->reporter_id,
        ];
    }
}
