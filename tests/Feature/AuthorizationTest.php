<?php

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Models\Task;
use App\Models\User;

it('rejects inactive users at login', function () {
    $user = User::factory()->inactive()->create();

    $this->from(route('login'))->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors([
        'email' => 'Your account is deactivated. Please contact an administrator.',
    ]);

    $this->assertGuest();
});

it('returns 403 Forbidden when an employee visits system settings', function () {
    $employee = User::factory()->employee()->create();

    $this->actingAs($employee)
        ->get(route('system-settings.index'))
        ->assertForbidden();
});

it('allows a manager to manage departmental tasks but not system settings', function () {
    $department = Department::factory()->create();
    $manager = User::factory()->manager()->create([
        'department_id' => $department->id,
    ]);
    $task = Task::factory()->create([
        'department_id' => $department->id,
        'status' => TaskStatus::Todo,
    ]);

    $this->actingAs($manager)
        ->get(route('system-settings.index'))
        ->assertForbidden();

    $this->actingAs($manager)
        ->patch(route('tasks.status.update', $task), [
            'status' => TaskStatus::InProgress->value,
        ])
        ->assertRedirect();

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress);

    $this->actingAs($manager)
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

it('allows an admin to access admin features', function () {
    $admin = User::factory()->admin()->create();
    SystemSetting::getSettings();

    $this->actingAs($admin)
        ->get(route('system-settings.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('activity-logs.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk();

    expect($admin->can('viewAny', User::class))->toBeTrue()
        ->and($admin->role)->toBe(UserRole::Admin);
});
