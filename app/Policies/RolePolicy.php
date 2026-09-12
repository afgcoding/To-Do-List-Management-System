<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('roles.view') || $user->hasPermissionTo('roles.manage');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('roles.manage');
    }

    public function update(User $user, Role $role): bool
    {
        if ($role->name === 'Super Admin' && ! $user->hasRole('Super Admin')) {
            return false;
        }

        return $user->hasPermissionTo('roles.manage');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('roles.manage') && $role->name !== 'Super Admin';
    }
}
