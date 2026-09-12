<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SystemSetting;
use App\Models\User;

class SystemSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('settings.view');
    }

    public function view(User $user, SystemSetting $systemSetting): bool
    {
        return $user->hasPermissionTo('settings.view');
    }

    public function update(User $user, SystemSetting $systemSetting): bool
    {
        return $user->hasPermissionTo('settings.manage');
    }
}
