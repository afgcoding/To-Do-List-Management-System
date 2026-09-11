<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskPriorityRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TaskController extends Controller
{
    // List tasks with search, filters, sorting, and dashboard stats.
    public function index(Request $request): View
    {
        // Eager-load relations and subtask counts to avoid N+1 queries.
        $tasks = Task::query()
            ->with(['creator', 'assignedUsers', 'category', 'department', 'tags'])
            ->withCount([
                'subtasks',
                'subtasks as completed_subtasks_count' => fn (Builder $query) => $query->where('is_completed', true),
            ])
            // Filter tasks by status, priority, assignee, category, and date ranges.
            ->search($request->string('search')->toString() ?: null)
            ->statusIs($request->string('status')->toString() ?: null)
            ->priorityIs($request->string('priority')->toString() ?: null)
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->assignedTo($request->filled('assigned_user_id') ? $request->integer('assigned_user_id') : null)
            ->dueBetween(
                $request->string('due_from')->toString() ?: null,
                $request->string('due_to')->toString() ?: null,
            )
            ->sortedBy(
                $request->string('sort_by', 'created_at')->toString(),
                $request->string('sort_order', 'desc')->toString(),
            )
            ->paginate(12)
            ->withQueryString();

        // Dashboard counters: total, in progress, completed, overdue.
        $stats = Task::query()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN due_date IS NOT NULL AND due_date < ? AND status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) as overdue", [now()->startOfDay()])
            ->first();

        return view('tasks.index', [
            'tasks' => $tasks,
            'categories' => Category::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'layout' => $request->string('layout', 'list')->toString() === 'grid' ? 'grid' : 'list',
            'stats' => [
                'total' => (int) ($stats->total ?? 0),
                'in_progress' => (int) ($stats->in_progress ?? 0),
                'completed' => (int) ($stats->completed ?? 0),
                'overdue' => (int) ($stats->overdue ?? 0),
            ],
        ]);
    }

    // Show the create-task form with departments, categories, tags, and users.
    public function create(): View
    {
        return view('tasks.create', $this->formCatalog());
    }

    // Persist a new task (Completed status is not set from this form).
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = $this->persistTask($request);

        return redirect()->route('tasks.show', $task)->with('success', 'Task created successfully!');
    }

    // Task detail page: relations plus subtask counts for the progress bar.
    public function show(Task $task): View
    {
        $task->load([
            'creator',
            'department',
            'category',
            'assignedUsers',
            'tags',
            'subtasks.assignedUser',
            'comments' => fn ($query) => $query->with(['user', 'attachments'])->oldest(),
            'attachments.user',
            'activityLogs.user',
        ])->loadCount([
            'subtasks',
            'subtasks as completed_subtasks_count' => fn (Builder $query) => $query->where('is_completed', true),
        ]);

        $users = User::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('tasks.show', [
            'task' => $task,
            'users' => $users,
            'currentUserId' => auth()->id() ?? User::query()->orderBy('id')->value('id'),
            'mentionNames' => $users->pluck('name')
                ->merge($task->comments->pluck('user.name'))
                ->filter()
                ->unique()
                ->values(),
        ]);
    }

    // Edit form, including currently assigned users and tags.
    public function edit(Task $task): View
    {
        $task->load(['assignedUsers', 'tags']);
        $catalog = $this->formCatalog();
        $catalog['departments'] = Department::query()->orderBy('name')->get();

        return view('tasks.edit', [
            ...$catalog,
            'task' => $task,
            'assignedUserIds' => $task->assignedUsers->pluck('id')->all(),
            'selectedTagIds' => $task->tags->pluck('id')->all(),
        ]);
    }

    // Save task edits; Completed remains automated by subtasks.
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->persistTask($request, $task);

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated successfully!');
    }

    // Quick status change. Completed is rejected by validation.
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): RedirectResponse
    {
        $status = TaskStatus::from($request->validated('status'));

        $task->update([
            'status' => $status,
            'completed_at' => $status === TaskStatus::Completed ? now() : null,
        ]);

        return back()->with('success', 'Task status updated.');
    }

    // Quick priority change from the show-page sidebar.
    public function updatePriority(UpdateTaskPriorityRequest $request, Task $task): RedirectResponse
    {
        $task->update([
            'priority' => $request->validated('priority'),
        ]);

        return back()->with('success', 'Task priority updated.');
    }

    // Delete a task and return to the index.
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }

    /**
     * Shared dropdown data for create/edit forms.
     *
     * @return array{departments: Collection<int, Department>, categories: Collection<int, Category>, tags: Collection<int, Tag>, users: Collection<int, User>}
     */
    private function formCatalog(): array
    {
        return [
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
            'users' => User::query()->where('status', 'active')->orderBy('name')->get(),
        ];
    }

    // Create or update a task, then sync assignees and tags.
    private function persistTask(StoreTaskRequest|UpdateTaskRequest $request, ?Task $task = null): Task
    {
        $validated = $request->validated();
        $assignedUsers = $validated['assigned_users'] ?? [];
        $tags = $validated['tags'] ?? [];
        unset($validated['assigned_users'], $validated['tags']);

        // Never accept Completed from the form; subtasks own that status.
        if (array_key_exists('status', $validated)) {
            if ($validated['status'] === null || $validated['status'] === TaskStatus::Completed || $validated['status'] === TaskStatus::Completed->value) {
                unset($validated['status'], $validated['completed_at']);
            } else {
                $validated['completed_at'] = null;
            }
        }

        if ($task === null) {
            $creatorId = auth()->id() ?? User::query()->orderBy('id')->value('id');

            if ($creatorId === null) {
                abort(422, 'A user is required before a task can be created.');
            }

            $validated['creator_id'] = $creatorId;
            $task = Task::query()->create($validated);
        } else {
            $task->update($validated);
        }

        $task->syncAssignedUsers($assignedUsers);
        $task->tags()->sync($tags);

        return $task;
    }
}
