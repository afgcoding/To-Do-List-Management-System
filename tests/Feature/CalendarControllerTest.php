<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\RecurringTask;
use App\Models\Task;
use App\Models\User;

it('renders the task calendar for signed-in users', function () {
    $user = User::factory()->manager()->create();
    $task = Task::factory()->create([
        'title' => 'Calendar card',
        'due_date' => now()->startOfMonth()->addDays(2),
        'status' => TaskStatus::Todo,
    ]);

    $this->actingAs($user)
        ->get(route('calendar.index', [
            'year' => now()->year,
            'month' => now()->month,
        ]))
        ->assertOk()
        ->assertSee('Calendar')
        ->assertSee('Back to Dashboard')
        ->assertDontSee('breadcrumb-header', false)
        ->assertSee('Calendar card')
        ->assertSee((string) $task->id, false)
        ->assertSee('Unscheduled')
        ->assertSee('Recurring Schedules')
        ->assertSee('calendar-sidebar-tabs', false)
        ->assertSee('lg:col-span-1', false)
        ->assertSee(':draggable="task.canMove"', false)
        ->assertSee('View Full Details')
        ->assertSee('calendar-preview', false)
        ->assertSee('calendar-day-add', false)
        ->assertSee('x-teleport="body"', false)
        ->assertSee('nozha-calendar-popover', false)
        ->assertSee('nozha-calendar-popover-backdrop', false)
        ->assertSee('closeAll()', false)
        ->assertSee('Cancel')
        ->assertSee('getBoundingClientRect()', false)
        ->assertSee('rect.left - 290', false)
        ->assertSee('position: fixed', false)
        ->assertSee('z-index: 9999', false)
        ->assertSee('pointer-events: auto', false)
        ->assertSee('eventClick', false)
        ->assertSee('eventMouseEnter', false)
        ->assertSee('eventMouseLeave', false)
        ->assertSee('canUpdatePriority', false);
});

it('opens in-progress calendar tasks from hover and other statuses from click', function () {
    $user = User::factory()->manager()->create();
    Task::factory()->create([
        'title' => 'Hover progress task',
        'description' => 'Progress preview body',
        'due_date' => now()->startOfMonth()->addDays(3),
        'status' => TaskStatus::InProgress,
    ]);

    $this->actingAs($user)
        ->get(route('calendar.index', [
            'year' => now()->year,
            'month' => now()->month,
        ]))
        ->assertOk()
        ->assertSee('Hover progress task')
        ->assertSee('Progress preview body')
        ->assertSee("task.status === 'in_progress'", false)
        ->assertSee('@mouseenter="eventMouseEnter(task, $event)"', false)
        ->assertSee('@mouseleave="eventMouseLeave()"', false)
        ->assertSee('eventClick(task, $event)', false)
        ->assertSee('is-keyboard', false)
        ->assertSee('keyboardOpen', false)
        ->assertSee('jsEvent.preventDefault()', false)
        ->assertSee('jsEvent.stopPropagation()', false);
});

it('lists active recurring templates in the calendar sidebar', function () {
    $user = User::factory()->manager()->create();
    $active = Task::factory()->create(['title' => 'Weekly invoice template']);
    RecurringTask::factory()->for($active, 'task')->create(['is_active' => true]);
    $paused = Task::factory()->create([
        'title' => 'Paused archive template',
        'due_date' => now()->addYear(),
    ]);
    RecurringTask::factory()->for($paused, 'task')->paused()->create();

    $this->actingAs($user)
        ->get(route('calendar.index'))
        ->assertOk()
        ->assertSee('Weekly invoice template')
        ->assertDontSee('Paused archive template');
});

it('creates a calendar task for a clicked date via ajax', function () {
    $user = User::factory()->manager()->create();
    $assignee = User::factory()->create();
    $due = now()->toDateString();

    $this->actingAs($user)
        ->postJson(route('calendar.tasks.store'), [
            'title' => 'Quick calendar add',
            'due_date' => $due,
            'priority' => TaskPriority::High->value,
            'assigned_users' => [$assignee->id],
        ])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('task.title', 'Quick calendar add')
        ->assertJsonPath('task.due', $due)
        ->assertJsonPath('task.priority', TaskPriority::High->value)
        ->assertJsonPath('task.assignees.0.id', $assignee->id);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Quick calendar add',
        'creator_id' => $user->id,
    ]);
});

it('validates a calendar quick-create payload', function () {
    $user = User::factory()->manager()->create();

    $this->actingAs($user)
        ->postJson(route('calendar.tasks.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'due_date', 'priority']);
});

it('persists a due date when an unscheduled task is dropped on the calendar', function () {
    $user = User::factory()->manager()->create();
    $task = Task::factory()->create([
        'start_date' => now()->subDay(),
        'due_date' => null,
        'status' => TaskStatus::Todo,
    ]);
    $due = now()->toDateString();

    $this->actingAs($user)
        ->patchJson(route('calendar.tasks.due-date', $task), [
            'due_date' => $due,
        ])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('due_date', $due);

    expect($task->fresh()->due_date)->not->toBeNull()
        ->and($task->fresh()->due_date->toDateString())->toBe($due);
});

it('updates a deadline through the calendar ajax endpoint', function () {
    $user = User::factory()->manager()->create();
    $task = Task::factory()->create([
        'start_date' => now()->subDay(),
        'due_date' => now()->addDays(3),
        'status' => TaskStatus::Todo,
    ]);
    $next = now()->addDays(8)->toDateString();

    $this->actingAs($user)
        ->patchJson(route('calendar.tasks.due-date', $task), [
            'due_date' => $next,
        ])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('due_date', $next);

    expect($task->fresh()->due_date->toDateString())->toBe($next);
});

it('rejects a calendar drop before the task start date', function () {
    $user = User::factory()->manager()->create();
    $task = Task::factory()->create([
        'start_date' => now()->addDays(5),
        'due_date' => now()->addDays(6),
        'status' => TaskStatus::Todo,
    ]);

    $this->actingAs($user)
        ->patchJson(route('calendar.tasks.due-date', $task), [
            'due_date' => now()->toDateString(),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('due_date');
});

it('forbids employees from rescheduling tasks they cannot edit', function () {
    $employee = User::factory()->employee()->create();
    $task = Task::factory()->create([
        'due_date' => now()->addDays(2),
        'status' => TaskStatus::Todo,
    ]);
    $task->assignedUsers()->sync([$employee->id]);
    $original = $task->due_date->toDateString();

    $this->actingAs($employee)
        ->patchJson(route('calendar.tasks.due-date', $task), [
            'due_date' => now()->addDays(9)->toDateString(),
        ])
        ->assertForbidden();

    expect($task->fresh()->due_date->toDateString())->toBe($original);
});
