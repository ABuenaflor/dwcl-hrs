<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * In-app notification shown in the bell menu. Every event in the system
 * (new application, status change, ranking hand-off, account approval)
 * is expressed as a title, a message and a link.
 */
class Alert extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public string $tone = 'info',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'tone' => $this->tone,
        ];
    }
}
