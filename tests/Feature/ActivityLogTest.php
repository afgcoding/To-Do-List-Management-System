<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('logs task creation and assignment changes', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create();

    $this->actingAs($creator)->post(route('tasks.store'), [
        'title' => 'Audit launch',
        'description' => 'Track history',
        'priority' => TaskPriority::High->value,
        'status' => TaskStatus::Todo->value,
        'assigned_users' => [$assignee->id],
        'start_date' => now()->toDateString(),
        'due_date' => now()->addWeek()->toDateString(),
    ])->assertRedirect();

    $task = Task::query()->where('title', 'Audit launch')->first();

    expect($task)->not->toBeNull();

    $this->assertDatabaseHas('activity_logs', [
        'task_id' => $task->id,
        'user_id' => $creator->id,
        'action' => 'created_task',
        'description' => 'created this task',
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'task_id' => $task->id,
        'user_id' => $creator->id,
        'action' => 'updated_assignment',
        'description' => 'updated assigned team members',
    ]);
});

it('logs status and priority changes from quick actions', function () {
    $user = User::factory()->manager()->create();
    $task = Task::factory()->for($user, 'creator')->create([
        'status' => TaskStatus::Todo,
        'priority' => TaskPriority::Medium,
    ]);

    $this->actingAs($user)
        ->patch(route('tasks.status.update', $task), [
            'status' => TaskStatus::InProgress->value,
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->patch(route('tasks.priority.update', $task), [
            'priority' => TaskPriority::Urgent->value,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('activity_logs', [
        'task_id' => $task->id,
        'action' => 'changed_status',
        'description' => 'changed status from To Do to In Progress',
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'task_id' => $task->id,
        'action' => 'changed_priority',
        'description' => 'changed priority from Medium to Urgent',
    ]);
});

it('logs due date changes when a task is updated', function () {
    $user = User::factory()->manager()->create();
    $task = Task::factory()->for($user, 'creator')->create([
        'title' => 'Deadline move',
        'priority' => TaskPriority::Low,
        'status' => TaskStatus::Todo,
        'start_date' => '2026-09-01',
        'due_date' => '2026-09-10',
    ]);

    $this->actingAs($user)->put(route('tasks.update', $task), [
        'title' => 'Deadline move',
        'priority' => TaskPriority::Low->value,
        'status' => TaskStatus::Todo->value,
        'start_date' => '2026-09-01',
        'due_date' => '2026-09-20',
        'assigned_users' => [],
    ])->assertRedirect();

    expect(ActivityLog::query()->where('task_id', $task->id)->where('action', 'changed_deadline')->exists())->toBeTrue();

    $this->assertDatabaseHas('activity_logs', [
        'task_id' => $task->id,
        'action' => 'changed_deadline',
        'description' => 'changed due date to Sep 20, 2026',
    ]);
});

it('logs comments and file uploads', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'creator')->create();

    $this->actingAs($user)
        ->from(route('tasks.show', $task))
        ->post(route('comments.store'), [
            'task_id' => $task->id,
            'comment' => 'Please review.',
        ])
        ->assertRedirect(route('tasks.show', $task));

    $this->actingAs($user)
        ->from(route('tasks.show', $task))
        ->post(route('attachments.store'), [
            'task_id' => $task->id,
            'files' => [UploadedFile::fake()->create('brief.pdf', 30, 'application/pdf')],
        ])
        ->assertRedirect(route('tasks.show', $task));

    $this->assertDatabaseHas('activity_logs', [
        'task_id' => $task->id,
        'user_id' => $user->id,
        'action' => 'added_comment',
        'description' => 'added a comment',
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'task_id' => $task->id,
        'user_id' => $user->id,
        'action' => 'uploaded_attachment',
        'description' => 'attached file brief.pdf',
    ]);
});

it('renders the paginated activity logs index', function () {
    $user = User::factory()->admin()->create(['name' => 'Omar Rahimi']);
    $this->actingAs($user);

    $task = Task::factory()->for($user, 'creator')->create(['title' => 'Ship audit feed']);

    $this->get(route('activity-logs.index'))
        ->assertOk()
        ->assertViewIs('activity-logs.index')
        ->assertViewHas('activityLogs')
        ->assertSee('Activity Logs')
        ->assertSee('created this task')
        ->assertSee('Ship audit feed')
        ->assertSee('Omar Rahimi');
});

it('renders the activity timeline on the task show page', function () {
    $user = User::factory()->create(['name' => 'Lina Ahmadi']);
    $this->actingAs($user);
    $task = Task::factory()->for($user, 'creator')->create(['title' => 'Timeline task']);

    $this->actingAs($user)
        ->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('Activity & History')
        ->assertSee('created this task')
        ->assertSee('Lina Ahmadi');
});
