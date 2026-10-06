<?php

use App\Models\User;

it('renders Nozha shell assets and navigation for an authenticated admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('/css/main.css', false)
        ->assertSee('/css/normalize.css', false)
        ->assertSee('/js/main.js', false)
        ->assertSee('/svg/logo-8.svg', false)
        ->assertSee('bmd-layout-drawer', false)
        ->assertSee('Task hub')
        ->assertSee('Tasks</span>', false)
        ->assertSee('Calendar</span>', false)
        ->assertSee('rel="icon"', false)
        ->assertSee('aria-label="Notifications"', false)
        ->assertSee('name="_token"', false)
        ->assertSee(route('logout'), false);
});

it('keeps the profile page inside the application layout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Back to tasks')
        ->assertSee('bmd-layout-content', false)
        ->assertSee('/css/main.css', false);
});
