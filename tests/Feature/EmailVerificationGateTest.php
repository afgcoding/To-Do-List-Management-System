<?php

use App\Models\User;

it('redirects unverified users from protected task routes to the verification notice', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertRedirect(route('verification.notice'));
});

it('lets unverified users open the verification notice and profile', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('Verify your email');

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk();
});
