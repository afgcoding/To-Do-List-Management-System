<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tags.manage');
    }

    public function view(User $user, Tag $tag): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tags.manage');
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->hasPermissionTo('tags.manage');
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $user->hasPermissionTo('tags.manage');
    }
}
