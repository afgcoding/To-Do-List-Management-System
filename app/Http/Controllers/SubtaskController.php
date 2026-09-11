<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubtaskRequest;
use App\Http\Requests\UpdateSubtaskRequest;
use App\Models\Subtask;
use Illuminate\Http\RedirectResponse;

class SubtaskController extends Controller
{
    // Add a pending subtask to a parent task.
    public function store(StoreSubtaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Subtask::query()->create([
            'task_id' => $validated['task_id'],
            'title' => $validated['title'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'is_completed' => false,
        ]);

        return back()->with('success', 'Subtask added successfully!');
    }

    // Inline edit of subtask title and assignee.
    public function update(UpdateSubtaskRequest $request, Subtask $subtask): RedirectResponse
    {
        $subtask->update($request->safe()->only(['title', 'assigned_to']));

        return back()->with('success', 'Subtask updated successfully!');
    }

    // Toggle subtask status (is_completed: true/false). Parent status is synced by SubtaskObserver.
    public function toggle(Subtask $subtask): RedirectResponse
    {
        $subtask->toggleCompletion();

        return back()->with('success', 'Subtask status updated!');
    }

    // Remove a subtask; observer recalculates the parent task status.
    public function destroy(Subtask $subtask): RedirectResponse
    {
        $subtask->delete();

        return back()->with('success', 'Subtask deleted!');
    }
}
