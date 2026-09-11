<?php

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns zero progress when a task has no subtasks', function () {
    $task = Task::factory()->create();

    expect($task->progress)->toBe(0);
});

it('calculates completion percentage from completed subtasks', function () {
    $task = Task::factory()->create();

    Subtask::factory()->for($task)->count(3)->create();
    Subtask::factory()->for($task)->completed()->create();

    $task->load('subtasks');

    expect($task->progress)->toBe(25);
});
