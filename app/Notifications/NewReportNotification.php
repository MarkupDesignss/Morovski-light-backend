<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Report;
use Illuminate\Support\Facades\App;

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
        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->subject(__('messages.new_report_submitted'))
            ->line(__('messages.report_submitted'))
            ->line(__('messages.reason', [
                'reason' => $this->report->reason
            ]))
            ->line(__('messages.description', [
                'description' => $this->report->description
            ]))
            ->action(__('messages.view_report'), url('/admin/reports/' . $this->report->id))
            ->line(__('messages.review_asap'));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'type' => 'new_report',

            'title' => __('messages.new_report_submitted'),
            'message' => __('messages.report_submitted'),

            'report_id' => $this->report->id,
            'reason' => $this->report->reason,
            'reporter_id' => $this->report->reporter_id,
        ];
    }
}
