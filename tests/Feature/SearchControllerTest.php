<?php

use App\Models\Tag;
use App\Models\Task;
use App\Models\User;

it('redirects guests to login', function () {
    auth()->logout();

    $this->getJson(route('search.global', ['q' => 'alpha']))
        ->assertUnauthorized();
});

it('returns grouped tasks users and tags for an admin', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin Searcher']);
    $teammate = User::factory()->create(['name' => 'Zara Needle']);
    $task = Task::factory()->for($admin, 'creator')->create(['title' => 'Needle deploy plan']);
    $tag = Tag::factory()->create(['name' => 'Needle-Fix']);

    $this->actingAs($admin)
        ->getJson(route('search.global', ['q' => 'Needle']))
        ->assertOk()
        ->assertJsonPath('tasks.0.title', 'Needle deploy plan')
        ->assertJsonPath('tasks.0.url', route('tasks.show', $task))
        ->assertJsonPath('users.0.name', 'Zara Needle')
        ->assertJsonPath('users.0.url', route('users.show', $teammate))
        ->assertJsonPath('tags.0.name', 'Needle-Fix')
        ->assertJsonPath('tags.0.url', route('tags.show', $tag));
});

it('rejects a query longer than 80 characters', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->getJson(route('search.global', ['q' => str_repeat('a', 81)]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('q');
});

it('returns empty groups when the query is blank', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->getJson(route('search.global', ['q' => '']))
        ->assertOk()
        ->assertExactJson([
            'tasks' => [],
            'users' => [],
            'tags' => [],
        ]);
});

it('hides other users tags and unassigned tasks from an employee', function () {
    $employee = User::factory()->employee()->create();
    $other = User::factory()->create(['name' => 'Hidden Colleague']);
    $visible = Task::factory()->create(['title' => 'Visible Needle task']);
    $visible->syncAssignedUsers([$employee->id]);
    Task::factory()->create(['title' => 'Secret Needle task']);
    Tag::factory()->create(['name' => 'Needle-Tag']);

    $this->actingAs($employee)
        ->getJson(route('search.global', ['q' => 'Needle']))
        ->assertOk()
        ->assertJsonPath('tasks.0.title', 'Visible Needle task')
        ->assertJsonCount(1, 'tasks')
        ->assertJsonPath('users', [])
        ->assertJsonPath('tags', [])
        ->assertJsonMissing(['name' => 'Hidden Colleague'])
        ->assertJsonMissing(['title' => 'Secret Needle task']);
});

it('renders the navbar search field', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-quick-search', false)
        ->assertSee('Search tasks, people, or projects', false);
});
