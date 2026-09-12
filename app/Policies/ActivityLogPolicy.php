<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('logs.view');
    }

    public function view(User $user, ActivityLog $activityLog): bool
    {
        return $user->hasPermissionTo('logs.view');
    }
}
