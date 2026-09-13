<?php

use App\Enums\TaskStatus;
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
        ->assertSee('Calendar card')
        ->assertSee((string) $task->id, false);
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
