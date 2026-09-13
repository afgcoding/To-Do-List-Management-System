<?php

declare(strict_types=1);

namespace App\Notifications;

class TaskAssignedNotification extends TaskAlertNotification
{
    public function alertType(): string
    {
        return 'task_assigned';
    }

    public function alertTitle(): string
    {
        return 'Task assigned';
    }

    protected function messageFor(object $notifiable): string
    {
        return $this->body !== ''
            ? $this->body
            : 'You were assigned to "'.$this->task->title.'".';
    }
}
