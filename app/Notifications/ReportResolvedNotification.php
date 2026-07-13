<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Report;
use Illuminate\Support\Facades\App;

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
        App::setLocale($notifiable->language ?? 'en');
        $notes = $this->report->resolution_notes ?? __('messages.not_available');

        return (new MailMessage)
            ->subject(__('messages.report_update'))
            ->line(__('messages.report_reviewed'))
            ->line(__('messages.report_status', [
                'status' => $this->report->status
            ]))
            ->line(__('messages.admin_notes', [
                'notes' => $notes
            ]))
            ->action(__('messages.view_details'), url('/account-layout/notifications'))
            ->line(__('messages.contact_support'));
    }

    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'type' => 'report_resolved',

            'title' => __('messages.report_update'),
            'message' => __('messages.report_reviewed'),

            'report_id' => $this->report->id,
            'status' => $this->report->status,
            'notes' => $this->report->resolution_notes ?? __('messages.not_available'),
        ];
    }
}
