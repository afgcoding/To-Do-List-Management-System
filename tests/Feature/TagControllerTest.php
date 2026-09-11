<?php

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('renders tags with a linked task count', function () {
    Tag::factory()->create(['name' => 'Urgent-Fix']);

    $this->get(route('tags.index'))
        ->assertOk()
        ->assertSee('Urgent-Fix')
        ->assertSee('0 tasks');
});

it('stores a tag by unique name only', function () {
    $this->post(route('tags.store'), [
        'name' => 'Feature',
    ])->assertRedirect(route('tags.index'))->assertSessionHas('success');

    $this->assertDatabaseHas('tags', ['name' => 'Feature']);
});

it('ignores a color value when storing a tag', function () {
    $this->post(route('tags.store'), [
        'name' => 'Bug',
        'color' => '#22C55E',
    ])->assertRedirect(route('tags.index'));

    $this->assertDatabaseHas('tags', ['name' => 'Bug']);
    expect(Schema::hasColumn('tags', 'color'))->toBeFalse();
});
