<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Support\Facades\App;

class PostInteractionNotification extends Notification
{
    use Queueable;
    protected $type;
    protected $user;
    protected $post;

    public function __construct($type, $user, $post)
    {
        $this->type = $type;
        $this->user = $user;
        $this->post = $post;
    }

    public function via($notifiable)
    {
        return ['database']; // store in DB
    }


    public function toDatabase($notifiable)
    {
        App::setLocale($notifiable->language ?? 'en');

        return [
            'type' => $this->type,

            'title' => __('messages.post_activity'),
            'message' => $this->getMessage(),

            // IDs
            'user_id' => $this->user->id,
            'post_id' => $this->post->id,

            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->full_name,
                'email' => $this->user->email,
                'profile_picture' => $this->user->profile_picture,
            ],

            'post' => [
                'id' => $this->post->id,
                'title' => $this->post->title, // accessor handles EN/DE ✔
                'link' => $this->post->link,
            ],
        ];
    }

    private function getMessage()
    {
        $user = $this->user->full_name;
        $post = $this->post->title;

        switch ($this->type) {
            case 'like':
                return __('messages.post_liked_by_user', [
                    'user' => $user,
                    'post' => $post
                ]);

            case 'comment':
                return __('messages.post_commented_by_user', [
                    'user' => $user,
                    'post' => $post
                ]);

            case 'bookmark':
                return __('messages.post_saved_by_user', [
                    'user' => $user,
                    'post' => $post
                ]);

            default:
                return __('messages.post_activity_default', [
                    'post' => $post
                ]);
        }
    }
}
