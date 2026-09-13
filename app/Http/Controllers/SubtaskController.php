<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubtaskRequest;
use App\Http\Requests\UpdateSubtaskRequest;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class SubtaskController extends Controller
{
    // Add a pending subtask to a parent task.
    public function store(StoreSubtaskRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $task = Task::query()->findOrFail($validated['task_id']);
        $this->authorize('update', $task);

        Subtask::query()->create([
            'task_id' => $validated['task_id'],
            'title' => $validated['title'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'is_completed' => false,
        ]);

        $this->ensureParentAssignment($task, $validated['assigned_to'] ?? null);

        return back()->with('success', 'Subtask added successfully!');
    }

    // Inline edit of subtask title and assignee.
    public function update(UpdateSubtaskRequest $request, Subtask $subtask): RedirectResponse
    {
        $this->authorize('update', $subtask->task);

        $subtask->update($request->safe()->only(['title', 'assigned_to']));

        $this->ensureParentAssignment($subtask->task, $request->validated('assigned_to'));

        return back()->with('success', 'Subtask updated successfully!');
    }

    // Toggle subtask status (is_completed: true/false). Parent status is synced by SubtaskObserver.
    public function toggle(Subtask $subtask): RedirectResponse
    {
        $this->authorize('updateStatus', $subtask->task);
        $subtask->toggleCompletion();

        return back()->with('success', 'Subtask status updated!');
    }

    // Remove a subtask; observer recalculates the parent task status.
    public function destroy(Subtask $subtask): RedirectResponse
    {
        $this->authorize('update', $subtask->task);
        $subtask->delete();

        return back()->with('success', 'Subtask deleted!');
    }

    private function ensureParentAssignment(Task $task, mixed $userId): void
    {
        if ($userId === null || $userId === '') {
            return;
        }

        $userId = (int) $userId;
        $assignedIds = $task->assignedUsers()
            ->pluck('users.id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        if (in_array($userId, $assignedIds, true)) {
            return;
        }

        $assignedIds[] = $userId;
        $task->syncAssignedUsers($assignedIds);
    }
}
