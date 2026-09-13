<?php

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;

it('forbids employees from opening team reports', function () {
    $this->actingAs(User::factory()->employee()->create())
        ->get(route('reports.index'))
        ->assertForbidden();
});

it('renders the report hub with export actions for managers', function () {
    $manager = User::factory()->manager()->create();
    Task::factory()->create(['title' => 'Board export item', 'status' => TaskStatus::Todo]);

    $this->actingAs($manager)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Export PDF')
        ->assertSee('Export Excel')
        ->assertSee('Board export item');
});

it('streams a csv export that excel can open', function () {
    $manager = User::factory()->manager()->create();
    Task::factory()->create(['title' => 'Csv row task', 'status' => TaskStatus::InProgress]);

    $response = $this->actingAs($manager)
        ->get(route('reports.csv'));

    $response->assertOk()
        ->assertHeader('content-disposition');

    expect($response->streamedContent())->toContain('Csv row task')->toContain('Title');
});

it('renders a printable pdf report view', function () {
    $manager = User::factory()->manager()->create();
    Task::factory()->create(['title' => 'Printable task', 'status' => TaskStatus::Todo]);

    $this->actingAs($manager)
        ->get(route('reports.print'))
        ->assertOk()
        ->assertSee('Print / Save PDF')
        ->assertSee('Printable task');
});

it('escapes task titles in the report table', function () {
    $manager = User::factory()->manager()->create();
    Task::factory()->create(['title' => '<script>alert("xss")</script>']);

    $this->actingAs($manager)
        ->get(route('reports.index'))
        ->assertOk()
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertSee('&lt;script&gt;', false);
});

it('forbids employees from exporting reports', function () {
    $this->actingAs(User::factory()->employee()->create())
        ->get(route('reports.csv'))
        ->assertForbidden();
});
