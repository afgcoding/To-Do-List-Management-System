<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StoresTaskAttachments;
use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    use StoresTaskAttachments;

    // Dedicated dropzone upload onto a task (not tied to a comment).
    public function store(StoreAttachmentRequest $request): RedirectResponse
    {
        $task = Task::query()->findOrFail($request->validated('task_id'));

        $this->storeUploadedFiles($task, $request->file('files', []), $this->actorId());

        return back()->with('success', 'Files uploaded.');
    }

    // Stream the original file through a named download route.
    public function download(Attachment $attachment): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($attachment->file_path), 404);

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        abort_unless($attachment->user_id === $this->actorId(), 403);

        $attachment->delete();

        return back()->with('success', 'File removed.');
    }
}
