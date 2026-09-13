<?php

declare(strict_types=1);

namespace App\Notifications;

class TaskReassignedNotification extends TaskAlertNotification
{
    public function alertType(): string
    {
        return 'task_reassigned';
    }

    public function alertTitle(): string
    {
        return 'Task reassigned';
    }

    protected function messageFor(object $notifiable): string
    {
        return $this->body !== ''
            ? $this->body
            : 'Assignment changed on "'.$this->task->title.'".';
    }
}
