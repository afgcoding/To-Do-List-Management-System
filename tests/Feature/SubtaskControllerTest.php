<?php

use App\Enums\TaskStatus;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a subtask on a task', function () {
    $task = Task::factory()->create();
    $assignee = User::factory()->create();

    $this->from(route('tasks.show', $task))
        ->post(route('subtasks.store'), [
            'task_id' => $task->id,
            'title' => 'Write acceptance tests',
            'assigned_to' => $assignee->id,
        ])
        ->assertRedirect(route('tasks.show', $task));

    $this->assertDatabaseHas('subtasks', [
        'task_id' => $task->id,
        'title' => 'Write acceptance tests',
        'assigned_to' => $assignee->id,
        'is_completed' => false,
    ]);
});

it('marks the parent task completed when every subtask is done', function () {
    $task = Task::factory()->create(['status' => TaskStatus::InProgress]);
    Subtask::factory()->for($task)->completed()->create();
    $pending = Subtask::factory()->for($task)->create();

    $this->patch(route('subtasks.toggle', $pending))->assertRedirect();

    expect($pending->fresh()->is_completed)->toBeTrue()
        ->and($task->fresh()->status)->toBe(TaskStatus::Completed)
        ->and($task->fresh()->completed_at)->not->toBeNull();
});

it('reverts a completed task to in progress when a subtask is unchecked', function () {
    $task = Task::factory()->create(['status' => TaskStatus::InProgress]);
    $first = Subtask::factory()->for($task)->create();
    $second = Subtask::factory()->for($task)->create();

    $this->patch(route('subtasks.toggle', $first));
    $this->patch(route('subtasks.toggle', $second));

    expect($task->fresh()->status)->toBe(TaskStatus::Completed);

    $this->patch(route('subtasks.toggle', $first))->assertRedirect();

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress)
        ->and($task->fresh()->completed_at)->toBeNull();
});

it('updates a subtask title and assignee inline', function () {
    $subtask = Subtask::factory()->create(['title' => 'Old title']);
    $assignee = User::factory()->create();

    $this->patch(route('subtasks.update', $subtask), [
        'title' => 'Revised title',
        'assigned_to' => $assignee->id,
    ])->assertRedirect();

    expect($subtask->fresh()->title)->toBe('Revised title')
        ->and($subtask->fresh()->assigned_to)->toBe($assignee->id);
});

it('deletes a subtask', function () {
    $subtask = Subtask::factory()->create();

    $this->delete(route('subtasks.destroy', $subtask))->assertRedirect();

    $this->assertModelMissing($subtask);
});
