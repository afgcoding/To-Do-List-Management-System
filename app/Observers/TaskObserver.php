<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Task;
use Carbon\CarbonInterface;

class TaskObserver
{
    // New tasks always get a created_task entry (factory, HTTP, or jobs).
    public function created(Task $task): void
    {
        ActivityLog::record(
            $task->id,
            'created_task',
            $task->createdFromRecurring
                ? 'created automatically from recurring task schedule'
                : 'created this task',
        );
    }

    // Attribute-level diffs fire after save; pivot assignee changes are logged in Task::syncAssignedUsers().
    public function updated(Task $task): void
    {
        if ($task->wasChanged('status')) {
            $from = $this->statusLabel($task->getOriginal('status'));
            $to = $this->statusLabel($task->status);

            ActivityLog::record(
                $task->id,
                'changed_status',
                "changed status from {$from} to {$to}",
            );
        }

        if ($task->wasChanged('priority')) {
            $from = $this->priorityLabel($task->getOriginal('priority'));
            $to = $this->priorityLabel($task->priority);

            ActivityLog::record(
                $task->id,
                'changed_priority',
                "changed priority from {$from} to {$to}",
            );
        }

        if ($task->wasChanged('due_date')) {
            $due = $task->due_date instanceof CarbonInterface
                ? $task->due_date->format('M d, Y')
                : 'none';

            ActivityLog::record(
                $task->id,
                'changed_deadline',
                "changed due date to {$due}",
            );
        }
    }

    private function statusLabel(mixed $value): string
    {
        if ($value instanceof TaskStatus) {
            return $value->label();
        }

        if (is_string($value) && $value !== '') {
            return TaskStatus::from($value)->label();
        }

        return 'Unknown';
    }

    private function priorityLabel(mixed $value): string
    {
        if ($value instanceof TaskPriority) {
            return $value->label();
        }

        if (is_string($value) && $value !== '') {
            return TaskPriority::from($value)->label();
        }

        return 'Unknown';
    }
}
