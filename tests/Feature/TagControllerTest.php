<?php

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('renders tags with a linked task count', function () {
    Tag::factory()->create(['name' => 'Urgent-Fix']);

    $this->get(route('tags.index'))
        ->assertOk()
        ->assertSee('Urgent-Fix')
        ->assertSee('0 tasks')
        ->assertSee('Unique labels such as Bug, Feature, or Urgent-Fix.')
        ->assertSee('Add tag')
        ->assertSee('Edit')
        ->assertSee('Cancel')
        ->assertSee('Save')
        ->assertDontSee('Delete');
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

it('returns 403 Forbidden when a user tries to delete a tag', function (string $factoryState) {
    $actor = User::factory()->{$factoryState}()->create();
    $tag = Tag::factory()->create();

    $this->actingAs($actor)
        ->delete(route('tags.destroy', $tag))
        ->assertForbidden();

    $this->assertDatabaseHas('tags', ['id' => $tag->id]);
})->with([
    'super admin' => ['superAdmin'],
    'admin' => ['admin'],
    'manager' => ['manager'],
    'employee' => ['employee'],
]);
