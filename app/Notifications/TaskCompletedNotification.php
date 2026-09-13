<?php

declare(strict_types=1);

namespace App\Notifications;

class TaskCompletedNotification extends TaskAlertNotification
{
    public function alertType(): string
    {
        return 'task_completed';
    }

    public function alertTitle(): string
    {
        return 'Task completed';
    }

    protected function messageFor(object $notifiable): string
    {
        return $this->body !== ''
            ? $this->body
            : '"'.$this->task->title.'" was marked completed.';
    }
}
