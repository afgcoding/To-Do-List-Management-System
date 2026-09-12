<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

final class PermissionCatalog
{
    /**
     * @return array<string, list<array{name: string, label: string}>>
     */
    public static function grouped(): array
    {
        return [
            'Users' => [
                ['name' => 'users.view', 'label' => 'View users'],
                ['name' => 'users.create', 'label' => 'Create users'],
                ['name' => 'users.edit', 'label' => 'Edit users'],
                ['name' => 'users.delete', 'label' => 'Delete users'],
                ['name' => 'users.manage-roles', 'label' => 'Manage user roles and extra grants'],
            ],
            'Roles & Permissions' => [
                ['name' => 'roles.view', 'label' => 'View roles'],
                ['name' => 'roles.manage', 'label' => 'Create and edit roles'],
            ],
            'Departments' => [
                ['name' => 'departments.view', 'label' => 'View departments'],
                ['name' => 'departments.manage', 'label' => 'Manage departments'],
            ],
            'Tasks' => [
                ['name' => 'tasks.view-all', 'label' => 'View all tasks'],
                ['name' => 'tasks.view-assigned', 'label' => 'View assigned and personal tasks'],
                ['name' => 'tasks.create', 'label' => 'Create tasks'],
                ['name' => 'tasks.edit', 'label' => 'Edit tasks'],
                ['name' => 'tasks.delete', 'label' => 'Delete tasks'],
                ['name' => 'tasks.assign', 'label' => 'Assign and reassign tasks'],
                ['name' => 'tasks.change-status', 'label' => 'Change task status'],
            ],
            'Categories & Tags' => [
                ['name' => 'categories.manage', 'label' => 'Manage categories'],
                ['name' => 'tags.manage', 'label' => 'Manage tags'],
            ],
            'Reports' => [
                ['name' => 'reports.view-team', 'label' => 'View team reports'],
                ['name' => 'reports.export', 'label' => 'Export reports'],
            ],
            'System Settings' => [
                ['name' => 'settings.view', 'label' => 'View system settings'],
                ['name' => 'settings.manage', 'label' => 'Update system settings'],
            ],
            'Activity Logs' => [
                ['name' => 'logs.view', 'label' => 'View activity logs'],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        $names = [];

        foreach (self::grouped() as $permissions) {
            foreach ($permissions as $permission) {
                $names[] = $permission['name'];
            }
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    public static function taskPermissions(): array
    {
        return array_column(self::grouped()['Tasks'], 'name');
    }

    /**
     * @return list<string>
     */
    public static function adminPermissions(): array
    {
        return array_values(array_filter(
            self::names(),
            fn (string $name): bool => ! in_array($name, ['settings.view', 'settings.manage'], true),
        ));
    }

    /**
     * @return list<string>
     */
    public static function managerPermissions(): array
    {
        return [
            'users.view',
            'users.create',
            'users.edit',
            'departments.view',
            ...self::taskPermissions(),
            'categories.manage',
            'tags.manage',
            'reports.view-team',
            'reports.export',
        ];
    }

    /**
     * @return list<string>
     */
    public static function employeePermissions(): array
    {
        return [
            'tasks.view-assigned',
            'tasks.create',
            'tasks.change-status',
        ];
    }

    /**
     * @return array<string, list<array{name: string, label: string}>>
     */
    public static function groupedFor(User $user): array
    {
        if ($user->hasRole('Super Admin')) {
            return self::grouped();
        }

        $grouped = [];

        foreach (self::grouped() as $group => $permissions) {
            $allowed = array_values(array_filter(
                $permissions,
                fn (array $permission): bool => $user->can($permission['name']),
            ));

            if ($allowed !== []) {
                $grouped[$group] = $allowed;
            }
        }

        return $grouped;
    }

    /**
     * @param  list<string>  $requested
     * @return list<string>
     */
    public static function grantableBy(User $user, array $requested, array $existing = []): array
    {
        $requested = array_values(array_unique($requested));

        if ($user->hasRole('Super Admin')) {
            return $requested;
        }

        $untouchable = array_values(array_filter(
            $existing,
            fn (string $name): bool => ! $user->can($name),
        ));

        $grantable = array_values(array_filter(
            $requested,
            fn (string $name): bool => $user->can($name),
        ));

        return array_values(array_unique([...$untouchable, ...$grantable]));
    }
}
