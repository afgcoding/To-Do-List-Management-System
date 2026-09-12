<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('renders users with job title role and contact details', function () {
    $department = Department::factory()->create(['name' => 'Delivery']);
    User::factory()->create([
        'name' => 'Sara Ali',
        'email' => 'sara@example.com',
        'phone' => '+93 700 111 222',
        'job_title' => 'Senior Laravel Developer',
        'role' => UserRole::Manager,
        'department_id' => $department->id,
    ]);

    $this->get(route('users.index'))
        ->assertOk()
        ->assertSee('Sara Ali')
        ->assertSee('Senior Laravel Developer')
        ->assertSee('sara@example.com')
        ->assertSee('+93 700 111 222')
        ->assertSee('Delivery')
        ->assertSee('Manager')
        ->assertSee('Actions');
});

it('renders a back link on the create user page', function () {
    $this->get(route('users.create'))
        ->assertOk()
        ->assertSee('Create user')
        ->assertSee('Back to users');
});

it('creates a user with an avatar', function () {
    Storage::fake('public');
    $department = Department::factory()->create();

    $this->post(route('users.store'), [
        'name' => 'Omar Rahimi',
        'email' => 'omar@example.com',
        'phone' => '+93 700 333 444',
        'job_title' => 'Product Designer',
        'role' => 'Employee',
        'department_id' => $department->id,
        'status' => 'active',
        'password' => 'password123',
        'avatar' => UploadedFile::fake()->image('omar.jpg'),
    ])->assertRedirect(route('users.index'));

    $user = User::query()->where('email', 'omar@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->job_title)->toBe('Product Designer')
        ->and($user->role)->toBe(UserRole::Employee)
        ->and($user->hasRole('Employee'))->toBeTrue()
        ->and($user->avatar)->toStartWith('avatars/');

    Storage::disk('public')->assertExists($user->avatar);
});

it('replaces an existing avatar and toggles status', function () {
    Storage::fake('public');
    $user = User::factory()->create(['status' => 'active']);
    $oldPath = UploadedFile::fake()->image('old.png')->store('avatars', 'public');
    $user->update(['avatar' => $oldPath]);

    $this->put(route('users.update', $user), [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'job_title' => $user->job_title,
        'role' => 'Admin',
        'department_id' => $user->department_id,
        'status' => 'active',
        'avatar' => UploadedFile::fake()->image('new.webp'),
    ])->assertRedirect(route('users.index'));

    $user->refresh();

    expect($user->role)->toBe(UserRole::Admin)
        ->and($user->hasRole('Admin'))->toBeTrue()
        ->and($user->avatar)->not->toBe($oldPath);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($user->avatar);

    $this->patch(route('users.status.toggle', $user))->assertRedirect();

    expect($user->fresh()->status)->toBe(UserStatus::Inactive);
});

it('lets a manager view and edit users without a delete grant', function () {
    $manager = User::factory()->manager()->create();
    $employee = User::factory()->employee()->create(['name' => 'Lina Ahmadi']);

    expect($manager->can('view', $employee))->toBeTrue()
        ->and($manager->can('update', $employee))->toBeTrue()
        ->and($manager->can('delete', $employee))->toBeFalse();

    $this->actingAs($manager)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('Lina Ahmadi')
        ->assertSee('View Profile')
        ->assertSee('Edit');
});

it('returns 403 Forbidden when an employee visits the users index', function () {
    $this->actingAs(User::factory()->employee()->create())
        ->get(route('users.index'))
        ->assertForbidden();
});

it('forbids editing or deleting a Super Admin and toggling your own status', function () {
    $admin = User::factory()->admin()->create();
    $superAdmin = User::factory()->superAdmin()->create();

    expect($admin->can('update', $superAdmin))->toBeFalse()
        ->and($admin->can('delete', $superAdmin))->toBeFalse()
        ->and($admin->can('toggleStatus', $admin))->toBeFalse();

    $this->actingAs($admin)
        ->get(route('users.edit', $superAdmin))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $superAdmin))
        ->assertForbidden();

    $this->actingAs($admin)
        ->patch(route('users.status.toggle', $admin))
        ->assertForbidden();
});

it('rejects an unsupported avatar type', function () {
    Storage::fake('public');

    $this->post(route('users.store'), [
        'name' => 'Bad File',
        'email' => 'bad@example.com',
        'role' => 'Employee',
        'status' => 'active',
        'password' => 'password123',
        'avatar' => UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf'),
    ])->assertSessionHasErrors('avatar');
});
