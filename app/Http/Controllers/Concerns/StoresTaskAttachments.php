<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;

trait StoresTaskAttachments
{
    /**
     * Store uploaded files on the public disk and persist attachment rows.
     *
     * @param  array<int, UploadedFile>  $files
     */
    protected function storeUploadedFiles(Task $task, array $files, int $userId, ?int $commentId = null): void
    {
        foreach ($files as $file) {
            $path = $file->store('attachments', 'public');

            Attachment::query()->create([
                'task_id' => $task->id,
                'user_id' => $userId,
                'comment_id' => $commentId,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => strtolower($file->getClientOriginalExtension() ?: 'bin'),
                'file_size' => (int) max(1, (int) ceil($file->getSize() / 1024)),
            ]);
        }
    }

    // Match task creation: signed-in user, or the first user when auth is not required.
    protected function actorId(): int
    {
        $id = auth()->id();

        if ($id === null) {
            abort(403);
        }

        return (int) $id;
    }

    protected function actor(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }
}
