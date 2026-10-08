<?php

use App\Models\Tag;
use App\Models\User;

it('denies deleting a tag for every role', function (string $factoryState) {
    $user = User::factory()->{$factoryState}()->create();
    $tag = Tag::factory()->create();

    expect($user->can('delete', $tag))->toBeFalse();
})->with([
    'super admin' => ['superAdmin'],
    'admin' => ['admin'],
    'manager' => ['manager'],
    'employee' => ['employee'],
]);
