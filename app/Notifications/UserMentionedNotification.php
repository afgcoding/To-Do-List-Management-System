<?php

declare(strict_types=1);

namespace App\Notifications;

class UserMentionedNotification extends TaskAlertNotification
{
    public function alertType(): string
    {
        return 'user_mentioned';
    }

    public function alertTitle(): string
    {
        return 'You were mentioned';
    }

    protected function messageFor(object $notifiable): string
    {
        $actor = $this->actor?->name ?? 'Someone';

        return $this->body !== ''
            ? $this->body
            : $actor.' mentioned you on "'.$this->task->title.'".';
    }
}
