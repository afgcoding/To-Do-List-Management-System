<?php

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('uploads files to a task from the dropzone', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'creator')->create();
    $file = UploadedFile::fake()->create('notes.txt', 20, 'text/plain');

    $this->actingAs($user)
        ->from(route('tasks.show', $task))
        ->post(route('attachments.store'), [
            'task_id' => $task->id,
            'files' => [$file],
        ])
        ->assertRedirect(route('tasks.show', $task));

    $this->assertDatabaseHas('attachments', [
        'task_id' => $task->id,
        'user_id' => $user->id,
        'file_name' => 'notes.txt',
        'file_type' => 'txt',
        'comment_id' => null,
    ]);
});

it('rejects an unsupported attachment type', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'creator')->create();

    $this->actingAs($user)
        ->post(route('attachments.store'), [
            'task_id' => $task->id,
            'files' => [UploadedFile::fake()->create('payload.exe', 10)],
        ])
        ->assertSessionHasErrors('files.0');
});

it('downloads an attachment', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'creator')->create();
    $path = UploadedFile::fake()->create('brief.pdf', 40, 'application/pdf')->store('attachments', 'public');

    $attachment = Attachment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $user->id,
        'file_name' => 'brief.pdf',
        'file_path' => $path,
        'file_type' => 'pdf',
        'file_size' => 40,
    ]);

    $this->get(route('attachments.download', $attachment))
        ->assertOk()
        ->assertDownload('brief.pdf');
});

it('lets the uploader delete an attachment', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'creator')->create();
    $path = UploadedFile::fake()->create('brief.pdf', 12)->store('attachments', 'public');
    $attachment = Attachment::factory()->create([
        'user_id' => $user->id,
        'task_id' => $task->id,
        'file_path' => $path,
    ]);

    $this->actingAs($user)
        ->delete(route('attachments.destroy', $attachment))
        ->assertRedirect();

    $this->assertModelMissing($attachment);
    Storage::disk('public')->assertMissing($path);
});
