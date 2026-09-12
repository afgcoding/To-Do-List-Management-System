<?php

use App\Models\SystemSetting;
use App\Models\Task;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('lets a Super Admin pass the global gate even without explicit permissions', function () {
    Role::findByName('Super Admin')->syncPermissions([]);

    $superAdmin = User::factory()->superAdmin()->create();

    expect($superAdmin->hasRole('Super Admin'))->toBeTrue()
        ->and($superAdmin->hasPermissionTo('settings.manage'))->toBeFalse()
        ->and($superAdmin->can('settings.manage'))->toBeTrue();

    SystemSetting::getSettings();

    $this->actingAs($superAdmin)
        ->get(route('system-settings.index'))
        ->assertOk();

    $this->actingAs($superAdmin)
        ->get(route('roles.index'))
        ->assertOk();
});

it('creates a custom role from the permission checkbox grid', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('roles.store'), [
            'name' => 'Auditor',
            'permissions' => ['logs.view', 'reports.view-team'],
        ])
        ->assertRedirect(route('roles.index'));

    $role = Role::findByName('Auditor');

    expect($role->hasPermissionTo('logs.view'))->toBeTrue()
        ->and($role->hasPermissionTo('reports.view-team'))->toBeTrue()
        ->and($role->hasPermissionTo('users.delete'))->toBeFalse();
});

it('grants a user extra permissions beyond their role', function () {
    $admin = User::factory()->admin()->create();
    $employee = User::factory()->employee()->create();

    $this->actingAs($employee)
        ->get(route('activity-logs.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->put(route('users.update', $employee), [
            'name' => $employee->name,
            'email' => $employee->email,
            'phone' => $employee->phone,
            'job_title' => $employee->job_title,
            'role' => 'Employee',
            'department_id' => $employee->department_id,
            'status' => 'active',
            'permissions' => ['logs.view'],
        ])
        ->assertRedirect(route('users.index'));

    expect($employee->fresh()->hasDirectPermission('logs.view'))->toBeTrue();

    $this->actingAs($employee->fresh())
        ->get(route('activity-logs.index'))
        ->assertOk();
});

it('seeds the four core roles with the enterprise permission matrix', function () {
    expect(Role::findByName('Super Admin')->permissions)->toHaveCount(23)
        ->and(Role::findByName('Admin')->permissions->pluck('name')->all())
        ->toHaveCount(21)
        ->not->toContain('settings.view')
        ->not->toContain('settings.manage')
        ->and(Role::findByName('Manager')->permissions->pluck('name')->sort()->values()->all())
        ->toBe(collect([
            'users.view',
            'users.create',
            'users.edit',
            'departments.view',
            'tasks.view-all',
            'tasks.view-assigned',
            'tasks.create',
            'tasks.edit',
            'tasks.delete',
            'tasks.assign',
            'tasks.change-status',
            'categories.manage',
            'tags.manage',
            'reports.view-team',
            'reports.export',
        ])->sort()->values()->all())
        ->and(Role::findByName('Employee')->permissions->pluck('name')->sort()->values()->all())
        ->toBe([
            'tasks.change-status',
            'tasks.create',
            'tasks.view-assigned',
        ]);
});

it('does not let an admin grant system settings permissions onto a role', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('roles.store'), [
            'name' => 'Elevated',
            'permissions' => ['logs.view', 'settings.view', 'settings.manage'],
        ])
        ->assertRedirect(route('roles.index'));

    $role = Role::findByName('Elevated');

    expect($role->hasPermissionTo('logs.view'))->toBeTrue()
        ->and($role->hasPermissionTo('settings.view'))->toBeFalse()
        ->and($role->hasPermissionTo('settings.manage'))->toBeFalse();
});

it('forbids a manager from deleting users or managing roles', function () {
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->employee()->create();

    expect($manager->can('delete', $employee))->toBeFalse()
        ->and($manager->can('manageRoles', $employee))->toBeFalse();

    $this->actingAs($manager)
        ->get(route('roles.index'))
        ->assertForbidden();

    $this->actingAs($manager)
        ->delete(route('users.destroy', $employee))
        ->assertForbidden();

    $this->actingAs($manager)
        ->get(route('users.index'))
        ->assertOk();
});

it('lets a user delete another user when they hold a direct users.delete grant', function () {
    $employee = User::factory()->employee()->create();
    $target = User::factory()->employee()->create();
    $employee->givePermissionTo('users.delete');

    $this->actingAs($employee->fresh())
        ->delete(route('users.destroy', $target))
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

it('returns 403 Forbidden when an unauthorized user attempts restricted actions', function () {
    $employee = User::factory()->employee()->create();
    $task = Task::factory()->create();

    $this->actingAs($employee)
        ->get(route('roles.index'))
        ->assertForbidden();

    $this->actingAs($employee)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($employee)
        ->delete(route('tasks.destroy', $task))
        ->assertForbidden();
});
