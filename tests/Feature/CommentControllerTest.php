<?php

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('posts a comment on a task', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $task = Task::factory()->create();

    $this->actingAs($user)
        ->from(route('tasks.show', $task))
        ->post(route('comments.store'), [
            'task_id' => $task->id,
            'comment' => 'Need a review from @'.$user->name,
        ])
        ->assertRedirect(route('tasks.show', $task));

    $this->assertDatabaseHas('comments', [
        'task_id' => $task->id,
        'user_id' => $user->id,
        'comment' => 'Need a review from @'.$user->name,
    ]);
});

it('highlights @mentions on the task show page', function () {
    $mentioned = User::factory()->create(['name' => 'Sara Ali', 'status' => 'active']);
    $task = Task::factory()->create();
    Comment::factory()->for($task)->create([
        'comment' => 'Please check this @Sara Ali today.',
    ]);

    $this->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('text-indigo-600', false)
        ->assertSee('@Sara Ali');
});

it('updates a comment owned by the current user', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $user->id, 'comment' => 'Draft note']);

    $this->actingAs($user)
        ->patch(route('comments.update', $comment), ['comment' => 'Final note'])
        ->assertRedirect();

    expect($comment->fresh()->comment)->toBe('Final note');
});

it('forbids updating someone else comment', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($intruder)
        ->patch(route('comments.update', $comment), ['comment' => 'Hijacked'])
        ->assertForbidden();
});

it('deletes a comment owned by the current user', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete(route('comments.destroy', $comment))
        ->assertRedirect();

    $this->assertModelMissing($comment);
});

it('attaches files when posting a comment', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $task = Task::factory()->create();
    $file = UploadedFile::fake()->image('shot.png');

    $this->actingAs($user)
        ->post(route('comments.store'), [
            'task_id' => $task->id,
            'comment' => 'Screenshot attached',
            'files' => [$file],
        ])
        ->assertRedirect();

    $comment = Comment::query()->firstOrFail();

    $this->assertDatabaseHas('attachments', [
        'task_id' => $task->id,
        'comment_id' => $comment->id,
        'file_name' => 'shot.png',
        'file_type' => 'png',
    ]);
});
