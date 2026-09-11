<?php

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders departments with code badges and active status', function () {
    Department::factory()->create([
        'name' => 'Development',
        'code' => 'DEV-01',
        'is_active' => true,
    ]);

    $this->get(route('departments.index'))
        ->assertOk()
        ->assertSee('Development')
        ->assertSee('DEV-01')
        ->assertSee('Active');
});

it('filters departments by name search and inactive status', function () {
    Department::factory()->create(['name' => 'Northwind Ops', 'code' => 'NWO-01', 'is_active' => true]);
    Department::factory()->create(['name' => 'Zephyr Labs', 'code' => 'ZPH-02', 'is_active' => false]);

    $this->get(route('departments.index', ['search' => 'Zephyr']))
        ->assertOk()
        ->assertSee('Zephyr Labs')
        ->assertDontSee('Northwind Ops');

    $this->get(route('departments.index', ['active' => '0']))
        ->assertOk()
        ->assertSee('Zephyr Labs')
        ->assertDontSee('Northwind Ops');
});

it('toggles a department between active and inactive', function () {
    $department = Department::factory()->create(['is_active' => true]);

    $this->patch(route('departments.active.toggle', $department))
        ->assertRedirect();

    expect($department->fresh()->is_active)->toBeFalse();

    $this->patch(route('departments.active.toggle', $department));

    expect($department->fresh()->is_active)->toBeTrue();
});

it('redirects the create page to the index drawer', function () {
    $this->get(route('departments.create'))
        ->assertRedirect(route('departments.index'));
});
