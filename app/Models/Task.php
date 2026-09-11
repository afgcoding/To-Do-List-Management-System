<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tag');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function recurringTask(): HasOne
    {
        return $this->hasOne(RecurringTask::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Completion percentage: (completed subtasks / total subtasks) × 100.
     *
     * @return Attribute<int, never>
     */
    protected function progress(): Attribute
    {
        return Attribute::get(function (): int {
            $total = (int) ($this->subtasks_count ?? $this->subtasks->count());

            if ($total === 0) {
                return 0;
            }

            $completed = (int) ($this->completed_subtasks_count ?? $this->subtasks->where('is_completed', true)->count());

            return (int) round(($completed / $total) * 100);
        });
    }

    /**
     * True when the due date is before today and the task is not done or cancelled.
     *
     * @return Attribute<bool, never>
     */
    protected function isOverdue(): Attribute
    {
        return Attribute::get(function (): bool {
            if ($this->due_date === null) {
                return false;
            }

            $status = $this->status instanceof TaskStatus
                ? $this->status
                : TaskStatus::from((string) $this->status);

            return $this->due_date->lt(now()->startOfDay())
                && ! in_array($status, [TaskStatus::Completed, TaskStatus::Cancelled], true);
        });
    }

    // Search by title or description keyword.
    #[Scope]
    protected function search(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder->where('title', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%');
        });
    }

    // Filter by status, including the special "overdue" value.
    #[Scope]
    protected function statusIs(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        if ($status === 'overdue') {
            return $query->overdue();
        }

        return $query->where('status', $status);
    }

    // Filter by priority.
    #[Scope]
    protected function priorityIs(Builder $query, ?string $priority): Builder
    {
        if (blank($priority)) {
            return $query;
        }

        return $query->where('priority', $priority);
    }

    // Past-due tasks that are not completed or cancelled.
    #[Scope]
    protected function overdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value]);
    }

    // Filter by an assigned user.
    #[Scope]
    protected function assignedTo(Builder $query, ?int $userId): Builder
    {
        if ($userId === null) {
            return $query;
        }

        return $query->whereHas('assignedUsers', function (Builder $builder) use ($userId): void {
            $builder->where('users.id', $userId);
        });
    }

    // Filter by due-date range.
    #[Scope]
    protected function dueBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if (filled($from)) {
            $query->whereDate('due_date', '>=', $from);
        }

        if (filled($to)) {
            $query->whereDate('due_date', '<=', $to);
        }

        return $query;
    }

    // Sort by due date, priority, created date, or status (allow-listed).
    #[Scope]
    protected function sortedBy(Builder $query, string $column, string $direction): Builder
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';
        $allowed = ['due_date', 'priority', 'created_at', 'status'];

        if (! in_array($column, $allowed, true)) {
            return $query->orderByDesc('created_at')->orderByDesc('id');
        }

        if ($column === 'priority') {
            return $query->orderByRaw("
                CASE priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                    ELSE 5
                END {$direction}
            ")->orderByDesc('id');
        }

        return $query->orderBy($column, $direction)->orderByDesc('id');
    }

    // Auto-calculate completion from subtasks and set parent task status.
    public function syncCompletionFromSubtasks(): void
    {
        $total = $this->subtasks()->count();

        if ($total === 0) {
            // No subtasks: a Completed task falls back to In Progress.
            if ($this->status === TaskStatus::Completed) {
                $this->forceFill([
                    'status' => TaskStatus::InProgress,
                    'completed_at' => null,
                ])->save();
            }

            return;
        }

        $allCompleted = ! $this->subtasks()->where('is_completed', false)->exists();

        if ($allCompleted) {
            // 100% complete → mark the parent Completed.
            $this->forceFill([
                'status' => TaskStatus::Completed,
                'completed_at' => $this->completed_at ?? now(),
            ])->save();

            return;
        }

        // Any incomplete subtask: if the parent was Completed, revert to In Progress.
        if ($this->status === TaskStatus::Completed) {
            $this->forceFill([
                'status' => TaskStatus::InProgress,
                'completed_at' => null,
            ])->save();
        }
    }
}
