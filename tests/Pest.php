<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

uses()->beforeEach(function (): void {
    $this->actingAs(User::factory()->admin()->create());
})->in(
    'Feature/ActivityLogTest.php',
    'Feature/AttachmentControllerTest.php',
    'Feature/CategoryControllerTest.php',
    'Feature/CommentControllerTest.php',
    'Feature/DepartmentControllerTest.php',
    'Feature/ProfileTest.php',
    'Feature/RecurringTaskTest.php',
    'Feature/SubtaskControllerTest.php',
    'Feature/SystemSettingTest.php',
    'Feature/TagControllerTest.php',
    'Feature/TaskControllerTest.php',
    'Feature/UserControllerTest.php',
);

pest()->extend(TestCase::class)
    ->in('Unit');
