<?php

declare(strict_types=1);

namespace App\Notifications;

class CommentAddedNotification extends TaskAlertNotification
{
    public function alertType(): string
    {
        return 'comment_added';
    }

    public function alertTitle(): string
    {
        return 'New comment';
    }

    protected function messageFor(object $notifiable): string
    {
        $actor = $this->actor?->name ?? 'Someone';

        return $this->body !== ''
            ? $this->body
            : $actor.' commented on "'.$this->task->title.'".';
    }
}
