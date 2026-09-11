<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Subtask;

class SubtaskObserver
{
    // After create/update, sync the parent task's Completed / In Progress status.
    public function saved(Subtask $subtask): void
    {
        $subtask->task?->syncCompletionFromSubtasks();
    }

    // After delete, recalculate parent status from remaining subtasks.
    public function deleted(Subtask $subtask): void
    {
        $subtask->task?->syncCompletionFromSubtasks();
    }
}
