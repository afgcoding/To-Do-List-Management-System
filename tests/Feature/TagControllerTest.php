<?php

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders tags as colored pills with a linked task count', function () {
    Tag::factory()->create([
        'name' => 'Urgent-Fix',
        'color' => '#EF4444',
    ]);

    $this->get(route('tags.index'))
        ->assertOk()
        ->assertSee('Urgent-Fix')
        ->assertSee('#EF4444', false)
        ->assertSee('0 tasks');
});

it('stores a tag with a hex color', function () {
    $this->post(route('tags.store'), [
        'name' => 'Feature',
        'color' => '#22C55E',
    ])->assertRedirect(route('tags.index'))->assertSessionHas('success');

    $this->assertDatabaseHas('tags', [
        'name' => 'Feature',
        'color' => '#22C55E',
    ]);
});

it('rejects a tag color that is not a hex code', function () {
    $this->post(route('tags.store'), [
        'name' => 'Bug',
        'color' => 'red',
    ])->assertSessionHasErrors('color');
});

it('updates a tag color without changing a unique name', function () {
    $tag = Tag::factory()->create(['name' => 'Bug', 'color' => '#EF4444']);

    $this->put(route('tags.update', $tag), [
        'name' => 'Bug',
        'color' => '#6366F1',
    ])->assertRedirect(route('tags.index'));

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Bug',
        'color' => '#6366F1',
    ]);
});
