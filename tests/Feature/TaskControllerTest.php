<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the task index with stats and task titles', function () {
    $user = User::factory()->create();

    Task::factory()->for($user, 'creator')->create(['title' => 'Ship dashboard']);
    Task::factory()->for($user, 'creator')->completed()->create();
    Task::factory()->for($user, 'creator')->overdue()->create();

    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Ship dashboard')
        ->assertSee('Total tasks')
        ->assertSee('In progress')
        ->assertSee('Completed')
        ->assertSee('Overdue');
});

it('filters tasks by title keyword', function () {
    $user = User::factory()->create();

    Task::factory()->for($user, 'creator')->create(['title' => 'Alpha report']);
    Task::factory()->for($user, 'creator')->create(['title' => 'Beta review']);

    $this->get(route('tasks.index', ['search' => 'Alpha']))
        ->assertOk()
        ->assertSee('Alpha report')
        ->assertDontSee('Beta review');
});

it('filters overdue tasks', function () {
    $user = User::factory()->create();

    Task::factory()->for($user, 'creator')->overdue()->create(['title' => 'Late invoice']);
    Task::factory()->for($user, 'creator')->create(['title' => 'On schedule']);

    $this->get(route('tasks.index', ['status' => 'overdue']))
        ->assertOk()
        ->assertSee('Late invoice')
        ->assertDontSee('On schedule');
});

it('filters tasks by assigned user', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Amina Karimi']);
    $other = User::factory()->create();

    $assigned = Task::factory()->for($creator, 'creator')->create(['title' => 'Assigned to Amina']);
    $assigned->assignedUsers()->sync([$assignee->id]);

    Task::factory()->for($creator, 'creator')->create(['title' => 'Assigned to someone else'])
        ->assignedUsers()->sync([$other->id]);

    $this->get(route('tasks.index', ['assigned_user_id' => $assignee->id]))
        ->assertOk()
        ->assertSee('Assigned to Amina')
        ->assertDontSee('Assigned to someone else');
});

it('creates a task with assignees and category', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Delivery']);

    $this->actingAs($creator)->post(route('tasks.store'), [
        'title' => 'New launch',
        'description' => 'Prep the launch',
        'priority' => TaskPriority::High->value,
        'status' => TaskStatus::Todo->value,
        'category_id' => $category->id,
        'assigned_users' => [$assignee->id],
        'start_date' => now()->toDateString(),
        'due_date' => now()->addWeek()->toDateString(),
    ])->assertRedirect();

    $task = Task::query()->where('title', 'New launch')->first();

    expect($task)->not->toBeNull()
        ->and($task->creator_id)->toBe($creator->id)
        ->and($task->category_id)->toBe($category->id)
        ->and($task->assignedUsers->pluck('id')->all())->toContain($assignee->id);
});

it('rejects a task without a title', function () {
    User::factory()->create();

    $this->from(route('tasks.create'))
        ->post(route('tasks.store'), [
            'priority' => TaskPriority::Medium->value,
            'status' => TaskStatus::Todo->value,
        ])
        ->assertRedirect(route('tasks.create'))
        ->assertSessionHasErrors('title');
});

it('rejects a due date before the start date', function () {
    User::factory()->create();

    $this->from(route('tasks.create'))
        ->post(route('tasks.store'), [
            'title' => 'Impossible dates',
            'priority' => TaskPriority::Low->value,
            'status' => TaskStatus::Todo->value,
            'start_date' => '2026-09-20',
            'due_date' => '2026-09-10',
        ])
        ->assertRedirect(route('tasks.create'))
        ->assertSessionHasErrors('due_date');
});

it('rejects manually setting a task status to completed', function () {
    $task = Task::factory()->create();

    $this->from(route('tasks.show', $task))
        ->patch(route('tasks.status.update', $task), [
            'status' => TaskStatus::Completed->value,
        ])
        ->assertRedirect(route('tasks.show', $task))
        ->assertSessionHasErrors('status');

    expect($task->fresh()->status)->toBe(TaskStatus::Todo);
});

it('does not offer completed as a manual status on the show page', function () {
    $task = Task::factory()->create();

    $this->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('Automated by Subtasks')
        ->assertDontSee('value="completed"', false);
});

it('updates task status and priority from quick actions', function () {
    $task = Task::factory()->create();

    $this->patch(route('tasks.status.update', $task), [
        'status' => TaskStatus::InProgress->value,
    ])->assertRedirect();

    $this->patch(route('tasks.priority.update', $task), [
        'priority' => TaskPriority::Urgent->value,
    ])->assertRedirect();

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress)
        ->and($task->fresh()->priority)->toBe(TaskPriority::Urgent);
});

it('deletes a task', function () {
    $task = Task::factory()->create();

    $this->delete(route('tasks.destroy', $task))
        ->assertRedirect(route('tasks.index'));

    $this->assertModelMissing($task);
});

it('shows progress from completed subtasks', function () {
    $task = Task::factory()->create();

    Subtask::factory()->for($task)->create();
    Subtask::factory()->for($task)->completed()->create();

    $task->load('subtasks');

    expect($task->progress)->toBe(50);

    $this->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('50%');
});
