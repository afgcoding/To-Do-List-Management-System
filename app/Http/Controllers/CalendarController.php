<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\StoreCalendarTaskRequest;
use App\Http\Requests\UpdateCalendarDueDateRequest;
use App\Models\RecurringTask;
use App\Models\Task;
use App\Models\User;
use App\Support\CalendarDateFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $actor = $request->user();
        $monthView = CalendarDateFormatter::monthView(
            $request->filled('year') ? $request->integer('year') : null,
            $request->filled('month') ? $request->integer('month') : null,
        );
        $days = $monthView['days'];
        $rangeStart = Carbon::parse($days->first()['iso'])->startOfDay();
        $rangeEnd = Carbon::parse($days->last()['iso'])->endOfDay();

        $tasks = Task::query()
            ->visibleTo($actor)
            ->with(['assignedUsers:id,name,avatar'])
            ->withCount([
                'subtasks',
                'subtasks as completed_subtasks_count' => fn (Builder $query) => $query->where('is_completed', true),
            ])
            ->where(function ($query) use ($rangeStart, $rangeEnd): void {
                $query->whereNull('due_date')
                    ->orWhereBetween('due_date', [$rangeStart, $rangeEnd]);
            })
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $recurringSchedules = RecurringTask::query()
            ->with(['task:id,title,status,priority'])
            ->where('is_active', true)
            ->whereHas('task', fn ($query) => $query->visibleTo($actor))
            ->orderBy('next_recurring_date')
            ->get();

        return view('calendar.index', [
            'pageTitle' => 'Calendar',
            'monthLabel' => $monthView['title'],
            'days' => $days,
            'previousUrl' => route('calendar.index', [
                'year' => $monthView['previousYear'],
                'month' => $monthView['previousMonth'],
            ]),
            'nextUrl' => route('calendar.index', [
                'year' => $monthView['nextYear'],
                'month' => $monthView['nextMonth'],
            ]),
            'calendarTasks' => $tasks->map(fn (Task $task): array => $this->calendarTaskPayload($task, $actor))->values(),
            'recurringSchedules' => $recurringSchedules,
            'calendarUsers' => User::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'canCreateTask' => $actor->can('create', Task::class),
        ]);
    }

    public function store(StoreCalendarTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $assignedUsers = $validated['assigned_users'] ?? [];
        unset($validated['assigned_users']);

        $task = Task::query()->create([
            ...$validated,
            'status' => TaskStatus::Todo,
            'creator_id' => $request->user()->id,
        ]);
        $task->syncAssignedUsers($assignedUsers);
        $task->load(['assignedUsers:id,name,avatar'])->loadCount([
            'subtasks',
            'subtasks as completed_subtasks_count' => fn (Builder $query) => $query->where('is_completed', true),
        ]);

        return response()->json([
            'ok' => true,
            'task' => $this->calendarTaskPayload($task, $request->user()),
        ]);
    }

    public function updateDueDate(UpdateCalendarDueDateRequest $request, Task $task): JsonResponse
    {
        $due = Carbon::parse($request->validated('due_date'))->startOfDay();

        if ($task->due_date !== null) {
            $due->setTimeFrom($task->due_date);
        }

        $task->due_date = $due;
        $task->save();
        $task->refresh();

        return response()->json([
            'ok' => true,
            'due_date' => $task->due_date?->toDateString(),
            'formatted' => format_date($task->due_date),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function calendarTaskPayload(Task $task, User $actor): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => Str::limit(trim((string) $task->description), 240) ?: 'No description provided.',
            'due' => $task->due_date?->toDateString(),
            'dueLabel' => format_date($task->due_date) ?? 'No due date',
            'start' => $task->start_date?->toDateString(),
            'startLabel' => format_date($task->start_date) ?? '—',
            'priority' => $task->priority->value,
            'priorityLabel' => $task->priority->label(),
            'status' => $task->status->value,
            'statusLabel' => $task->status->label(),
            'progress' => $task->progress,
            'subtasksCount' => (int) ($task->subtasks_count ?? 0),
            'completedSubtasks' => (int) ($task->completed_subtasks_count ?? 0),
            'assignees' => $task->assignedUsers
                ->map(fn ($user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                ])
                ->values(),
            'canMove' => $actor->can('update', $task),
            'canUpdate' => $actor->can('update', $task),
            'canUpdateStatus' => $actor->can('updateStatus', $task),
            'canUpdatePriority' => $actor->can('updatePriority', $task),
            'url' => route('tasks.show', $task),
        ];
    }
}
