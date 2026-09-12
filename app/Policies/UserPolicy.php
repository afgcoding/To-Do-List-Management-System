<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('users.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    public function update(User $user, User $model): bool
    {
        if ($model->hasRole('Super Admin') && ! $user->hasRole('Super Admin')) {
            return false;
        }

        return $user->hasPermissionTo('users.edit');
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->hasRole('Super Admin') || $user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('users.delete');
    }

    public function toggleStatus(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $this->update($user, $model);
    }

    public function manageRoles(User $user, ?User $model = null): bool
    {
        if ($model instanceof User && $model->hasRole('Super Admin') && ! $user->hasRole('Super Admin')) {
            return false;
        }

        return $user->hasRole(['Super Admin', 'Admin'])
            || $user->hasDirectPermission('users.manage-roles');
    }
}
