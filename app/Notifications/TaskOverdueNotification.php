<?php

declare(strict_types=1);

namespace App\Notifications;

class TaskOverdueNotification extends TaskAlertNotification
{
    public function alertType(): string
    {
        return 'task_overdue';
    }

    public function alertTitle(): string
    {
        return 'Task overdue';
    }

    protected function messageFor(object $notifiable): string
    {
        return $this->body !== ''
            ? $this->body
            : '"'.$this->task->title.'" is past its due date.';
    }
}
