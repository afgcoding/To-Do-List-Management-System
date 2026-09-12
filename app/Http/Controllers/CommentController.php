<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StoresTaskAttachments;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    use StoresTaskAttachments;

    // Post a comment on a task, with optional file uploads.
    public function store(StoreCommentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $task = Task::query()->findOrFail($validated['task_id']);
        $this->authorize('view', $task);
        $userId = $this->actorId();

        $comment = Comment::query()->create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'comment' => $validated['comment'],
        ]);

        $this->storeUploadedFiles($task, $request->file('files', []), $userId, $comment->id);

        return back()->with('success', 'Comment posted.');
    }

    // Save an inline edit for the comment owner.
    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        abort_unless($comment->user_id === $this->actorId(), 403);

        $comment->update($request->safe()->only(['comment']));

        return back()->with('success', 'Comment updated.');
    }

    // Remove a comment. Linked files stay on the task (comment_id set to null).
    public function destroy(Comment $comment): RedirectResponse
    {
        abort_unless($comment->user_id === $this->actorId(), 403);

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
