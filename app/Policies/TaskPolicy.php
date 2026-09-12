<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && (
            $user->hasPermissionTo('tasks.view-all')
            || $user->hasPermissionTo('tasks.view-assigned')
        );
    }

    public function view(User $user, Task $task): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->hasPermissionTo('tasks.view-all')) {
            return true;
        }

        return $user->hasPermissionTo('tasks.view-assigned')
            && ($user->isAssignedTo($task) || $task->creator_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->hasPermissionTo('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->isActive()
            && $user->hasPermissionTo('tasks.edit')
            && $this->view($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isActive() && $user->hasPermissionTo('tasks.delete');
    }

    public function updateStatus(User $user, Task $task): bool
    {
        return $user->isActive()
            && $user->hasPermissionTo('tasks.change-status')
            && $this->view($user, $task);
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->isActive()
            && $user->hasPermissionTo('tasks.assign')
            && $this->view($user, $task);
    }
}
