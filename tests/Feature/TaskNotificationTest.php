<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Comment;
use App\Models\Task;
use App\Models\TaskReminderDispatch;
use App\Models\User;
use App\Notifications\CommentAddedNotification;
use App\Notifications\DeadlineApproachingNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;
use App\Notifications\TaskOverdueNotification;
use App\Notifications\TaskReassignedNotification;
use App\Notifications\TaskUpdatedNotification;
use App\Notifications\UserMentionedNotification;
use Illuminate\Support\Facades\Notification;

it('notifies newly assigned users and skips the actor', function () {
    Notification::fake();

    $actor = User::factory()->admin()->create();
    $assignee = User::factory()->create();
    $task = Task::factory()->for($actor, 'creator')->create();

    $this->actingAs($actor);
    $task->syncAssignedUsers([$assignee->id]);

    Notification::assertSentTo($assignee, TaskAssignedNotification::class);
    Notification::assertNotSentTo($actor, TaskAssignedNotification::class);
});

it('notifies users when a task is reassigned', function () {
    Notification::fake();

    $actor = User::factory()->admin()->create();
    $first = User::factory()->create();
    $second = User::factory()->create();
    $task = Task::factory()->for($actor, 'creator')->create();

    $this->actingAs($actor);
    $task->syncAssignedUsers([$first->id]);

    Notification::fake();
    $task->syncAssignedUsers([$second->id]);

    Notification::assertSentTo($second, TaskReassignedNotification::class);
    Notification::assertNotSentTo($first, TaskAssignedNotification::class);
});

it('notifies participants when a task is completed', function () {
    Notification::fake();

    $actor = User::factory()->admin()->create();
    $assignee = User::factory()->create();
    $task = Task::factory()->for($actor, 'creator')->create();
    $task->assignedUsers()->sync([$assignee->id]);

    $this->actingAs($actor);
    $task->applyStatus(TaskStatus::Completed);

    Notification::assertSentTo($assignee, TaskCompletedNotification::class);
    Notification::assertNotSentTo($actor, TaskCompletedNotification::class);
});

it('notifies participants when critical task fields change', function () {
    Notification::fake();

    $actor = User::factory()->admin()->create();
    $assignee = User::factory()->create();
    $task = Task::factory()->for($actor, 'creator')->create(['title' => 'Old title']);
    $task->assignedUsers()->sync([$assignee->id]);

    $this->actingAs($actor);
    $task->update([
        'title' => 'New title',
        'priority' => TaskPriority::Urgent,
    ]);

    Notification::assertSentTo($assignee, TaskUpdatedNotification::class);
});

it('notifies other participants when a comment is added', function () {
    Notification::fake();

    $author = User::factory()->admin()->create();
    $assignee = User::factory()->create();
    $task = Task::factory()->for($author, 'creator')->create();
    $task->assignedUsers()->sync([$assignee->id]);

    $this->actingAs($author);
    Comment::query()->create([
        'task_id' => $task->id,
        'user_id' => $author->id,
        'comment' => 'Please review this.',
    ]);

    Notification::assertSentTo($assignee, CommentAddedNotification::class);
    Notification::assertNotSentTo($author, CommentAddedNotification::class);
});

it('notifies a user mentioned in a comment', function () {
    Notification::fake();

    $author = User::factory()->admin()->create();
    $mentioned = User::factory()->create(['name' => 'Sara Ali']);
    $task = Task::factory()->for($author, 'creator')->create();

    $this->actingAs($author);
    Comment::query()->create([
        'task_id' => $task->id,
        'user_id' => $author->id,
        'comment' => 'Need eyes from @Sara Ali',
    ]);

    Notification::assertSentTo($mentioned, UserMentionedNotification::class);
    Notification::assertNotSentTo($mentioned, CommentAddedNotification::class);
});

it('sends approaching and overdue reminders once', function () {
    Notification::fake();

    $assignee = User::factory()->create();
    $dayTask = Task::factory()->create(['due_date' => now()->addHours(12), 'status' => TaskStatus::Todo]);
    $dayTask->assignedUsers()->sync([$assignee->id]);
    $hourTask = Task::factory()->create(['due_date' => now()->addMinutes(30), 'status' => TaskStatus::InProgress]);
    $hourTask->assignedUsers()->sync([$assignee->id]);
    $overdueTask = Task::factory()->overdue()->create();
    $overdueTask->assignedUsers()->sync([$assignee->id]);

    $this->artisan('tasks:send-reminders')->assertSuccessful();
    $this->artisan('tasks:send-reminders')->assertSuccessful();

    Notification::assertSentToTimes($assignee, DeadlineApproachingNotification::class, 2);
    Notification::assertSentToTimes($assignee, TaskOverdueNotification::class, 1);
    expect(TaskReminderDispatch::query()->count())->toBe(3);
});

it('lists notifications in the navbar feed and marks them read', function () {
    $user = User::factory()->admin()->create();
    $task = Task::factory()->for($user, 'creator')->create(['title' => 'Notify me']);

    $user->notify(new TaskAssignedNotification($task, $user));

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('aria-label="Notifications"', false);

    $feed = $this->actingAs($user)
        ->getJson(route('notifications.feed'))
        ->assertOk()
        ->assertJsonPath('unread_count', 1);

    $id = $feed->json('notifications.0.id');

    $this->actingAs($user)
        ->postJson(route('notifications.read', $id))
        ->assertOk()
        ->assertJsonPath('unread_count', 0);

    $this->actingAs($user)
        ->deleteJson(route('notifications.clear'))
        ->assertOk()
        ->assertJsonPath('notifications', []);
});
