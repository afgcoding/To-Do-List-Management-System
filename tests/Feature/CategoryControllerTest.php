<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders categories with color preview badges', function () {
    Category::factory()->create([
        'name' => 'Frontend',
        'color' => '#3B82F6',
    ]);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Frontend')
        ->assertSee('#3B82F6', false);
});

it('rejects a category color that is not a hex code', function () {
    $this->post(route('categories.store'), [
        'name' => 'Database',
        'color' => 'blue',
    ])->assertSessionHasErrors('color');
});

it('redirects edit to the index with the drawer query', function () {
    $category = Category::factory()->create();

    $this->get(route('categories.edit', $category))
        ->assertRedirect(route('categories.index', ['edit' => $category->id]));
});
