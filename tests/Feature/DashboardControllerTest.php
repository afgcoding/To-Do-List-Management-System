<?php

use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;

it('redirects guests from the dashboard to login', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('renders role-aware team analytics for an admin', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->employee()->create(['name' => 'Amina Karimi']);
    $department = Department::factory()->create(['name' => 'Engineering']);
    $task = Task::factory()->create([
        'title' => 'Ship reporting hub',
        'status' => TaskStatus::InProgress,
        'department_id' => $department->id,
        'due_date' => now()->addHours(6),
    ]);
    $task->assignedUsers()->sync([$member->id]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Reporting hub')
        ->assertSee('Total Active Tasks')
        ->assertSee('My Assigned Tasks')
        ->assertSee('Team Workload')
        ->assertSee('Critical Overdue')
        ->assertSee('Employee workload')
        ->assertSee('Amina Karimi')
        ->assertSee('Engineering')
        ->assertSee('Due Today')
        ->assertSee('Ship reporting hub');
});

it('hides other employees tasks from the employee dashboard', function () {
    $employee = User::factory()->employee()->create();
    $other = User::factory()->employee()->create();
    $mine = Task::factory()->create(['title' => 'My private draft', 'status' => TaskStatus::Todo]);
    $mine->assignedUsers()->sync([$employee->id]);
    $hidden = Task::factory()->create(['title' => 'Secret other work', 'status' => TaskStatus::Todo]);
    $hidden->assignedUsers()->sync([$other->id]);
    ActivityLog::record($hidden->id, 'changed_status', 'changed status to Completed', $other->id);

    $this->actingAs($employee)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Assigned to Me')
        ->assertSee('Pending Tasks')
        ->assertSee('My private draft')
        ->assertDontSee('Secret other work')
        ->assertDontSee('Employee workload')
        ->assertDontSee('Department breakdown');
});

it('escapes task titles on the dashboard', function () {
    $user = User::factory()->admin()->create();
    $task = Task::factory()->create([
        'title' => '<script>alert("xss")</script>',
        'due_date' => now()->addHours(3),
        'status' => TaskStatus::Todo,
    ]);
    $task->assignedUsers()->sync([$user->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
});
