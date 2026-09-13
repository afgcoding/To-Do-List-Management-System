<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskReminderDispatch;
use App\Notifications\DeadlineApproachingNotification;
use App\Notifications\TaskAlertNotification;
use App\Notifications\TaskOverdueNotification;
use App\Support\TaskNotifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

#[Signature('tasks:send-reminders')]
#[Description('Send approaching-deadline and overdue task reminders')]
class SendTaskReminders extends Command
{
    public function handle(): int
    {
        $sent = 0;
        $now = now();

        Task::query()
            ->with(['assignedUsers', 'creator'])
            ->whereNotNull('due_date')
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
            ->where(function (Builder $query) use ($now): void {
                $query->where('due_date', '<', $now)
                    ->orWhere('due_date', '<=', $now->copy()->addDay());
            })
            ->orderBy('id')
            ->each(function (Task $task) use (&$sent, $now): void {
                $sent += $this->dispatchForTask($task, $now);
            });

        $this->info("Sent {$sent} task reminder".($sent === 1 ? '' : 's').'.');

        return self::SUCCESS;
    }

    private function dispatchForTask(Task $task, Carbon $now): int
    {
        $count = 0;
        $due = $task->due_date;

        if ($due === null) {
            return 0;
        }

        if ($due->lt($now)) {
            return $this->sendOnce($task, 'overdue', new TaskOverdueNotification($task)) ? 1 : 0;
        }

        $customMinutes = config('notifications.custom_minutes_before');

        if (is_numeric($customMinutes) && (int) $customMinutes > 0 && $due->lte($now->copy()->addMinutes((int) $customMinutes))) {
            $count += $this->sendOnce($task, 'custom', new DeadlineApproachingNotification($task, 'custom')) ? 1 : 0;
        }

        if ($due->lte($now->copy()->addHour())) {
            return $count + ($this->sendOnce($task, 'hour', new DeadlineApproachingNotification($task, 'hour')) ? 1 : 0);
        }

        if ($due->lte($now->copy()->addDay())) {
            $count += $this->sendOnce($task, 'day', new DeadlineApproachingNotification($task, 'day')) ? 1 : 0;
        }

        return $count;
    }

    private function sendOnce(Task $task, string $type, TaskAlertNotification $notification): bool
    {
        if (TaskReminderDispatch::query()->where('task_id', $task->id)->where('reminder_type', $type)->exists()) {
            return false;
        }

        $recipients = TaskNotifier::reminderRecipients($task);

        if ($recipients->isEmpty()) {
            return false;
        }

        TaskNotifier::send($recipients, $notification);

        TaskReminderDispatch::query()->create([
            'task_id' => $task->id,
            'reminder_type' => $type,
            'sent_at' => now(),
        ]);

        return true;
    }
}
