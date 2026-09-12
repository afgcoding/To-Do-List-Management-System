<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_department_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('departments.store'), [
            'name' => 'Engineering',
            'code' => 'ENG',
            'is_active' => true,
        ])->assertRedirect(route('departments.index'))->assertSessionHas('success');

        $department = Department::firstOrFail();

        $this->put(route('departments.update', $department), [
            'name' => 'Product Engineering',
            'code' => 'PE',
            'is_active' => false,
        ])->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', ['name' => 'Product Engineering', 'is_active' => false]);

        $this->delete(route('departments.destroy', $department))->assertRedirect(route('departments.index'));

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_category_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('categories.store'), [
            'name' => 'Planning',
            'color' => '#4F46E5',
        ])->assertRedirect(route('categories.index'))->assertSessionHas('success');

        $category = Category::firstOrFail();

        $this->put(route('categories.update', $category), [
            'name' => 'Delivery Planning',
            'color' => '#10B981',
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Delivery Planning', 'color' => '#10B981']);

        $this->delete(route('categories.destroy', $category))->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_tag_name_must_be_unique_and_tag_can_be_deleted(): void
    {
        Tag::create(['name' => 'Urgent']);

        $this->post(route('tags.store'), ['name' => 'Urgent'])->assertSessionHasErrors('name');

        $this->post(route('tags.store'), ['name' => 'Client-facing'])->assertRedirect(route('tags.index'));

        $tag = Tag::where('name', 'Client-facing')->firstOrFail();

        $this->delete(route('tags.destroy', $tag))->assertRedirect(route('tags.index'));

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
