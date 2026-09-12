<?php

use App\Enums\RecurrenceType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\RecurringTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a task with a recurring schedule', function () {
    $creator = User::factory()->manager()->create();
    $assignee = User::factory()->create();

    $this->actingAs($creator)->post(route('tasks.store'), [
        'title' => 'Weekly standup notes',
        'description' => 'Capture blockers',
        'priority' => TaskPriority::Medium->value,
        'status' => TaskStatus::Todo->value,
        'assigned_users' => [$assignee->id],
        'is_recurring' => '1',
        'recurrence_type' => RecurrenceType::Weekly->value,
        'repeat_interval' => 2,
        'next_recurring_date' => now()->addDay()->toDateString(),
    ])->assertRedirect();

    $task = Task::query()->where('title', 'Weekly standup notes')->first();

    expect($task)->not->toBeNull()
        ->and($task->recurringTask)->not->toBeNull()
        ->and($task->recurringTask->recurrence_type)->toBe(RecurrenceType::Weekly)
        ->and($task->recurringTask->repeat_interval)->toBe(2)
        ->and($task->recurringTask->is_active)->toBeTrue();
});

it('duplicates due recurring tasks and advances the next run date', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $template = Task::factory()->for($creator, 'creator')->create([
        'title' => 'Invoice sweep',
        'description' => 'Review unpaid invoices',
        'priority' => TaskPriority::High,
        'status' => TaskStatus::InProgress,
        'start_date' => now()->subDays(3),
        'due_date' => now()->addDays(4),
    ]);
    $template->assignedUsers()->sync([$assignee->id]);

    $schedule = RecurringTask::factory()->for($template, 'task')->create([
        'recurrence_type' => RecurrenceType::Weekly,
        'repeat_interval' => 1,
        'next_recurring_date' => now()->toDateString(),
        'is_active' => true,
    ]);

    $this->artisan('tasks:process-recurring')->assertSuccessful();

    $copies = Task::query()->where('title', 'Invoice sweep')->orderBy('id')->get();

    expect($copies)->toHaveCount(2);

    $occurrence = $copies->last();

    expect($occurrence->id)->not->toBe($template->id)
        ->and($occurrence->status)->toBe(TaskStatus::Todo)
        ->and($occurrence->completed_at)->toBeNull()
        ->and($occurrence->assignedUsers->pluck('id')->all())->toContain($assignee->id)
        ->and($occurrence->recurringTask)->toBeNull();

    $this->assertDatabaseHas('activity_logs', [
        'task_id' => $occurrence->id,
        'action' => 'created_task',
        'description' => 'created automatically from recurring task schedule',
    ]);

    expect($schedule->fresh()->next_recurring_date->toDateString())
        ->toBe(now()->addWeek()->toDateString());
});

it('ignores paused recurring schedules', function () {
    $template = Task::factory()->create(['title' => 'Paused digest']);

    RecurringTask::factory()->for($template, 'task')->paused()->create([
        'next_recurring_date' => now()->toDateString(),
    ]);

    $this->artisan('tasks:process-recurring')->assertSuccessful();

    expect(Task::query()->where('title', 'Paused digest')->count())->toBe(1);
});

it('renders the recurring tasks index', function () {
    $task = Task::factory()->create(['title' => 'Board sync']);
    RecurringTask::factory()->for($task, 'task')->create([
        'recurrence_type' => RecurrenceType::Monthly,
        'repeat_interval' => 1,
    ]);

    $this->get(route('recurring-tasks.index'))
        ->assertOk()
        ->assertSee('Recurring Tasks')
        ->assertSee('Board sync')
        ->assertSee('Every 1 Month');
});
