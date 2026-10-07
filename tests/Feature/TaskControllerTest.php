<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Department;
use App\Models\Subtask;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('renders the task index with stats and task titles', function () {
    $user = User::factory()->create();

    Task::factory()->for($user, 'creator')->create(['title' => 'Ship dashboard']);
    Task::factory()->for($user, 'creator')->completed()->create();
    Task::factory()->for($user, 'creator')->overdue()->create();

    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Tasks Workspace')
        ->assertSee('Create task')
        ->assertDontSee('breadcrumb-header', false)
        ->assertSee('Ship dashboard')
        ->assertSee('Total tasks')
        ->assertSee('In progress')
        ->assertSee('Completed')
        ->assertSee('Overdue')
        ->assertSee('Filters')
        ->assertSee('Apply filters')
        ->assertSee('name="search"', false)
        ->assertSee('name="status"', false)
        ->assertSee('name="assigned_user_id"', false)
        ->assertSee('Showing 1 to 3 of 3 results');
});

it('paginates the task index seven records per page', function () {
    $user = User::factory()->create();

    foreach (range(1, 8) as $number) {
        Task::factory()->for($user, 'creator')->create([
            'title' => "Paged task {$number}",
            'created_at' => now()->subSeconds(9 - $number),
        ]);
    }

    $pageOne = $this->get(route('tasks.index'))->assertOk();

    $pageOne
        ->assertSee('Showing 1 to 7 of 8 results')
        ->assertSee('Paged task 8')
        ->assertDontSee('Paged task 1');

    expect(substr_count($pageOne->getContent(), 'Showing 1 to 7 of 8 results'))->toBe(1);

    $this->get(route('tasks.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Showing 8 to 8 of 8 results')
        ->assertSee('Paged task 1');
});

it('filters tasks by title keyword', function () {
    $user = User::factory()->create();

    Task::factory()->for($user, 'creator')->create(['title' => 'Alpha report']);
    Task::factory()->for($user, 'creator')->create(['title' => 'Beta review']);

    $this->get(route('tasks.index', ['search' => 'Alpha']))
        ->assertOk()
        ->assertSee('Alpha report')
        ->assertDontSee('Beta review');
});

it('filters overdue tasks', function () {
    $user = User::factory()->create();

    Task::factory()->for($user, 'creator')->overdue()->create(['title' => 'Late invoice']);
    Task::factory()->for($user, 'creator')->create(['title' => 'On schedule']);

    $this->get(route('tasks.index', ['status' => 'overdue']))
        ->assertOk()
        ->assertSee('Late invoice')
        ->assertDontSee('On schedule');
});

it('filters tasks by assigned user', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create(['name' => 'Amina Karimi']);
    $other = User::factory()->create();

    $assigned = Task::factory()->for($creator, 'creator')->create(['title' => 'Assigned to Amina']);
    $assigned->assignedUsers()->sync([$assignee->id]);

    Task::factory()->for($creator, 'creator')->create(['title' => 'Assigned to someone else'])
        ->assignedUsers()->sync([$other->id]);

    $this->get(route('tasks.index', ['assigned_user_id' => $assignee->id]))
        ->assertOk()
        ->assertSee('Assigned to Amina')
        ->assertDontSee('Assigned to someone else');
});

it('creates a task with assignees and category', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Delivery']);

    $this->actingAs($creator)->post(route('tasks.store'), [
        'title' => 'New launch',
        'description' => 'Prep the launch',
        'priority' => TaskPriority::High->value,
        'status' => TaskStatus::Todo->value,
        'category_id' => $category->id,
        'assigned_users' => [$assignee->id],
        'start_date' => now()->toDateString(),
        'due_date' => now()->addWeek()->toDateString(),
    ])->assertRedirect();

    $task = Task::query()->where('title', 'New launch')->first();

    expect($task)->not->toBeNull()
        ->and($task->creator_id)->toBe($creator->id)
        ->and($task->category_id)->toBe($category->id)
        ->and($task->assignedUsers->pluck('id')->all())->toContain($assignee->id);
});

it('rejects a task without a title', function () {
    User::factory()->create();

    $this->from(route('tasks.create'))
        ->post(route('tasks.store'), [
            'priority' => TaskPriority::Medium->value,
            'status' => TaskStatus::Todo->value,
        ])
        ->assertRedirect(route('tasks.create'))
        ->assertSessionHasErrors('title');
});

it('rejects a due date before the start date', function () {
    User::factory()->create();

    $this->from(route('tasks.create'))
        ->post(route('tasks.store'), [
            'title' => 'Impossible dates',
            'priority' => TaskPriority::Low->value,
            'status' => TaskStatus::Todo->value,
            'start_date' => '2026-09-20',
            'due_date' => '2026-09-10',
        ])
        ->assertRedirect(route('tasks.create'))
        ->assertSessionHasErrors('due_date');
});

it('completes every subtask when the status is set to completed', function () {
    $task = Task::factory()->create(['status' => TaskStatus::InProgress]);
    Subtask::factory()->for($task)->count(2)->create();
    Subtask::factory()->for($task)->completed()->create();

    $this->from(route('tasks.show', $task))
        ->patch(route('tasks.status.update', $task), [
            'status' => TaskStatus::Completed->value,
        ])
        ->assertRedirect(route('tasks.show', $task));

    expect($task->fresh()->status)->toBe(TaskStatus::Completed)
        ->and($task->fresh()->completed_at)->not->toBeNull()
        ->and($task->subtasks()->where('is_completed', false)->exists())->toBeFalse();
});

it('offers completed as a selectable status on the show page', function () {
    $task = Task::factory()->create();

    $this->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('value="completed"', false)
        ->assertDontSee('Automated by Subtasks');
});

it('disables the status dropdown when every subtask is complete and the task is completed', function () {
    $task = Task::factory()->create(['status' => TaskStatus::Completed]);
    Subtask::factory()->for($task)->completed()->create();

    $html = $this->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('✓ Task is 100% complete. Uncheck any subtask to re-enable manual status change.')
        ->getContent();

    expect($html)->toContain('name="status"')
        ->and($html)->toContain('disabled')
        ->and($html)->not->toContain('Choosing Completed marks every subtask done.');
});

it('keeps the status dropdown enabled when a completed task has no subtasks', function () {
    $task = Task::factory()->create(['status' => TaskStatus::Completed]);

    $this->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('Choosing Completed marks every subtask done.')
        ->assertDontSee('Uncheck any subtask to re-enable manual status change.');
});

it('updates task status and priority from quick actions', function () {
    $task = Task::factory()->create();

    $this->patch(route('tasks.status.update', $task), [
        'status' => TaskStatus::InProgress->value,
    ])->assertRedirect();

    $this->patch(route('tasks.priority.update', $task), [
        'priority' => TaskPriority::Urgent->value,
    ])->assertRedirect();

    expect($task->fresh()->status)->toBe(TaskStatus::InProgress)
        ->and($task->fresh()->priority)->toBe(TaskPriority::Urgent);
});

it('deletes a task', function () {
    $task = Task::factory()->create();

    $this->delete(route('tasks.destroy', $task))
        ->assertRedirect(route('tasks.index'));

    $this->assertModelMissing($task);
});

it('shows progress from completed subtasks', function () {
    $task = Task::factory()->create();

    Subtask::factory()->for($task)->create();
    Subtask::factory()->for($task)->completed()->create();

    $task->load('subtasks');

    expect($task->progress)->toBe(50);

    $this->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('50%');
});

it('renders the task details page as a stacked layout with a compact back link', function () {
    $task = Task::factory()->create(['title' => 'Show page layout task']);
    Subtask::factory()->for($task)->create(['title' => 'Visible subtask title']);

    $this->get(route('tasks.show', $task))
        ->assertSee('Show page layout task')
        ->assertSee('← Back to Tasks')
        ->assertSee('card flat workspace-card', false)
        ->assertSee('task-show-toolbar', false)
        ->assertSee('task-show-header', false)
        ->assertSee('task-show-body', false)
        ->assertSee('header-actions', false)
        ->assertSee('subtask-create-row subtask-add-row', false)
        ->assertSee('subtask-add-row subtask-edit-row', false)
        ->assertSee('form-control subtask-title', false)
        ->assertSee('btn btn-primary btn-sm subtask-submit', false)
        ->assertSee('+ Add Subtask')
        ->assertSee('Visible subtask title')
        ->assertSee('subtask-item-title', false)
        ->assertSee('btn btn-secondary btn-sm', false)
        ->assertDontSee('breadcrumb-header', false);
});

it('labels the tasks column as assignee when every row has at most one assignee', function () {
    $assignee = User::factory()->create(['name' => 'Solo Worker']);
    $task = Task::factory()->create(['title' => 'Solo task']);
    $task->assignedUsers()->sync([$assignee->id]);

    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('>Assignee<', false)
        ->assertDontSee('>Team<', false)
        ->assertSee('title="Solo Worker"', false);
});

it('labels the tasks column as team when a row has multiple assignees', function () {
    $first = User::factory()->create(['name' => 'Amina Karimi']);
    $second = User::factory()->create(['name' => 'Omar Rahimi']);
    $third = User::factory()->create(['name' => 'Lina Ahmadi']);
    $fourth = User::factory()->create(['name' => 'Neda Ahmadi']);
    $task = Task::factory()->create(['title' => 'Shared task']);
    $task->assignedUsers()->sync([$first->id, $second->id, $third->id, $fourth->id]);

    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('>Team<', false)
        ->assertSee('title="Amina Karimi"', false)
        ->assertSee('>+1<', false);
});

it('renders the navbar profile photo when the signed-in user has an avatar', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->image('me.jpg')->store('avatars', 'public');
    $user = User::factory()->admin()->create([
        'name' => 'Sara Hassan',
        'avatar' => $path,
    ]);

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url($path), false);
});

it('renders soft category tags and a department badge on the task list', function () {
    $department = Department::factory()->create(['name' => 'Engineering']);
    $category = Category::factory()->create([
        'name' => 'Delivery',
        'color' => '#6366F1',
    ]);
    $tag = Tag::factory()->create(['name' => 'Urgent']);
    $task = Task::factory()->create([
        'title' => 'Badge task',
        'category_id' => $category->id,
        'department_id' => $department->id,
    ]);
    $task->tags()->sync([$tag->id]);

    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Delivery')
        ->assertSee('#Urgent', false)
        ->assertSee('#6366F115', false)
        ->assertSee('Engineering');
});

it('collapses extra tags on the task list row with a more badge', function () {
    $task = Task::factory()->create(['title' => 'Many tags']);
    $alpha = Tag::factory()->create(['name' => 'Alpha']);
    $beta = Tag::factory()->create(['name' => 'Beta']);
    $gamma = Tag::factory()->create(['name' => 'Gamma']);
    $task->tags()->sync([$alpha->id, $beta->id, $gamma->id]);

    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('#Alpha', false)
        ->assertSee('#Beta', false)
        ->assertSee('+1 more')
        ->assertDontSee('#Gamma', false);
});
