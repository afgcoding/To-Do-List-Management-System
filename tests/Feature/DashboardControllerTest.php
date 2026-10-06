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
        ->assertSee('>Dashboard</h1>', false)
        ->assertDontSee('breadcrumb-header', false)
        ->assertDontSee('Reporting hub')
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

it('embeds the task workspace with search and status tabs', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Task Management Workspace')
        ->assertSee('All tasks')
        ->assertSee('Filters')
        ->assertSee('Apply filters')
        ->assertSee('name="search"', false)
        ->assertSee('placeholder="Search tasks..."', false)
        ->assertDontSee('breadcrumb-header', false)
        ->assertSee('>List</a>', false)
        ->assertSee('>Grid</a>', false);
});

it('renders the task workspace above dashboard widgets', function () {
    $admin = User::factory()->admin()->create();

    $html = $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    expect(strpos($html, 'Task Management Workspace'))->toBeLessThan(strpos($html, 'Total Active Tasks'))
        ->and(strpos($html, 'Total Active Tasks'))->toBeLessThan(strpos($html, 'Task distribution by status'));
});

it('filters dashboard workspace tasks by title keyword', function () {
    $admin = User::factory()->admin()->create();

    Task::factory()->for($admin, 'creator')->create(['title' => 'Alpha report']);
    Task::factory()->for($admin, 'creator')->create(['title' => 'Beta review']);

    $this->actingAs($admin)
        ->get(route('dashboard', ['search' => 'Alpha']))
        ->assertOk()
        ->assertSee('Alpha report')
        ->assertViewHas('workspaceTasks', function ($tasks): bool {
            $titles = $tasks->pluck('title');

            return $titles->contains('Alpha report') && ! $titles->contains('Beta review');
        });
});

it('filters dashboard workspace tasks by overdue status', function () {
    $admin = User::factory()->admin()->create();

    Task::factory()->for($admin, 'creator')->overdue()->create(['title' => 'Late invoice']);
    Task::factory()->for($admin, 'creator')->completed()->create(['title' => 'Finished briefing']);

    $this->actingAs($admin)
        ->get(route('dashboard', ['status' => 'overdue']))
        ->assertOk()
        ->assertSee('Late invoice')
        ->assertViewHas('workspaceTasks', function ($tasks): bool {
            $titles = $tasks->pluck('title');

            return $titles->contains('Late invoice') && ! $titles->contains('Finished briefing');
        });
});

it('paginates dashboard workspace tasks ten records per page', function () {
    $admin = User::factory()->admin()->create();

    foreach (range(1, 11) as $number) {
        Task::factory()->for($admin, 'creator')->create([
            'title' => "Workspace page item {$number}",
            'created_at' => now()->subSeconds(12 - $number),
        ]);
    }

    $pageOne = $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk();

    $pageOne
        ->assertSee('Showing 1 to 10 of 11 results')
        ->assertViewHas('workspaceTasks', function ($tasks): bool {
            $titles = $tasks->pluck('title');

            return $tasks->perPage() === 10
                && $titles->contains('Workspace page item 11')
                && ! $titles->contains('Workspace page item 1');
        });

    expect(substr_count($pageOne->getContent(), 'Showing 1 to 10 of 11 results'))->toBe(1);

    $this->actingAs($admin)
        ->get(route('dashboard', ['page' => 2]))
        ->assertOk()
        ->assertSee('Showing 11 to 11 of 11 results')
        ->assertViewHas('workspaceTasks', function ($tasks): bool {
            return $tasks->pluck('title')->contains('Workspace page item 1');
        });
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
