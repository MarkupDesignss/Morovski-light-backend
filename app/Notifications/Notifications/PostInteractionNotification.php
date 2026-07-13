<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

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
        return [
            'type' => $this->type,
            'message' => $this->getMessage(),
    
            // IDs (already useful)
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
                'title' => $this->post->title,
                'link' => $this->post->link,
            ],
        ];
    }
    
    private function getMessage()
    {
        switch ($this->type) {
            case 'like':
                return "{$this->user->full_name} liked your post: {$this->post->title}";
            case 'comment':
                return "{$this->user->full_name} commented on your post: {$this->post->title}";
            case 'bookmark':
                return "{$this->user->full_name} saved your post: {$this->post->title}";
            default:
                return "New activity on your post: {$this->post->title}";
        }
    }
}