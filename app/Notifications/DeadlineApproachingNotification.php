<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;

class DeadlineApproachingNotification extends TaskAlertNotification
{
    public function __construct(
        Task $task,
        public string $window = 'day',
        ?User $actor = null,
        string $body = '',
    ) {
        parent::__construct($task, $actor, $body);
    }

    public function alertType(): string
    {
        return 'deadline_approaching';
    }

    public function alertTitle(): string
    {
        return match ($this->window) {
            'hour' => 'Due in 1 hour',
            'custom' => 'Deadline reminder',
            default => '1 day remaining',
        };
    }

    protected function messageFor(object $notifiable): string
    {
        return $this->body !== ''
            ? $this->body
            : match ($this->window) {
                'hour' => '"'.$this->task->title.'" is due within the next hour.',
                'custom' => '"'.$this->task->title.'" is approaching its due date.',
                default => '"'.$this->task->title.'" is due within 24 hours.',
            };
    }
}
