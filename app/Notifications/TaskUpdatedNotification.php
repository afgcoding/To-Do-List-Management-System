<?php

declare(strict_types=1);

namespace App\Notifications;

class TaskUpdatedNotification extends TaskAlertNotification
{
    public function alertType(): string
    {
        return 'task_updated';
    }

    public function alertTitle(): string
    {
        return 'Task updated';
    }

    protected function messageFor(object $notifiable): string
    {
        return $this->body !== ''
            ? $this->body
            : 'Priority, dates, or title changed on "'.$this->task->title.'".';
    }
}
