<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RecurrenceType;
use App\Enums\TaskStatus;
use Database\Factories\RecurringTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTask extends Model
{
    /** @use HasFactory<RecurringTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'task_id',
        'recurrence_type',
        'repeat_interval',
        'next_recurring_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'recurrence_type' => RecurrenceType::class,
            'repeat_interval' => 'integer',
            'next_recurring_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function frequencyLabel(): string
    {
        $interval = max(1, $this->repeat_interval);
        $type = $this->recurrence_type instanceof RecurrenceType
            ? $this->recurrence_type
            : RecurrenceType::from((string) $this->recurrence_type);

        $unit = match ($type) {
            RecurrenceType::Daily => $interval === 1 ? 'Day' : 'Days',
            RecurrenceType::Weekly => $interval === 1 ? 'Week' : 'Weeks',
            RecurrenceType::Monthly => $interval === 1 ? 'Month' : 'Months',
            RecurrenceType::Yearly => $interval === 1 ? 'Year' : 'Years',
        };

        return "Every {$interval} {$unit}";
    }

    // Clone the template task as a fresh To Do occurrence for today.
    public function spawnOccurrence(): Task
    {
        $template = $this->task;

        if ($template === null) {
            throw new \RuntimeException('Recurring schedule is missing its base task.');
        }

        $today = now()->startOfDay();
        $dueDate = null;

        if ($template->due_date !== null && $template->start_date !== null) {
            $duration = $template->start_date->copy()->startOfDay()
                ->diffInDays($template->due_date->copy()->startOfDay());
            $dueDate = $today->copy()->addDays((int) $duration);
        } elseif ($template->due_date !== null) {
            $dueDate = $today->copy();
        }

        $occurrence = $template->replicate([
            'status',
            'completed_at',
            'start_date',
            'due_date',
        ]);
        $occurrence->status = TaskStatus::Todo;
        $occurrence->completed_at = null;
        $occurrence->start_date = $today;
        $occurrence->due_date = $dueDate;
        $occurrence->createdFromRecurring = true;
        $occurrence->save();

        $assigneeIds = $template->assignedUsers()->pluck('users.id')->all();
        $occurrence->assignedUsers()->sync($assigneeIds);

        return $occurrence;
    }

    public function advanceNextDate(): void
    {
        $interval = max(1, $this->repeat_interval);
        $next = $this->next_recurring_date?->copy() ?? now();
        $type = $this->recurrence_type instanceof RecurrenceType
            ? $this->recurrence_type
            : RecurrenceType::from((string) $this->recurrence_type);

        $next = match ($type) {
            RecurrenceType::Daily => $next->addDays($interval),
            RecurrenceType::Weekly => $next->addWeeks($interval),
            RecurrenceType::Monthly => $next->addMonths($interval),
            RecurrenceType::Yearly => $next->addYears($interval),
        };

        $this->update(['next_recurring_date' => $next]);
    }
}
