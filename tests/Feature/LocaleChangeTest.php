<?php

use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;

it('stores an allowed locale in the session and redirects back', function () {
    $this->from(route('login'))
        ->get(route('locale.change', 'ps'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('locale', 'ps');
});

it('ignores an unsupported locale', function () {
    $this->from(route('login'))
        ->get(route('locale.change', 'fr'))
        ->assertRedirect(route('login'))
        ->assertSessionMissing('locale');
});

it('applies the session locale to chrome labels', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->withSession(['locale' => 'fa'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('داشبورد')
        ->assertSee('دری (FA)');
});

it('uses a right-to-left document for pashto and dari', function (string $locale) {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->withSession(['locale' => $locale])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee('data-locale-dir="rtl"', false);
})->with(['ps', 'fa']);

it('keeps english left to right', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('dir="ltr"', false)
        ->assertDontSee('dir="rtl"', false);
});

it('renders the language switcher in the navbar', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('locale.change', 'en'), false)
        ->assertSee(route('locale.change', 'ps'), false)
        ->assertSee(route('locale.change', 'fa'), false)
        ->assertSee('English (EN)')
        ->assertSee('پښتو (PS)');
});

it('translates dashboard status labels and activity phrases', function () {
    $user = User::factory()->admin()->create();
    $task = Task::factory()->create([
        'title' => 'Localize activity feed',
        'status' => TaskStatus::InProgress,
    ]);
    ActivityLog::record($task->id, 'changed_status', 'changed status from To Do to In Progress', $user->id);
    ActivityLog::record($task->id, 'updated_assignment', 'updated assigned team members', $user->id);

    $this->actingAs($user)
        ->withSession(['locale' => 'fa'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('در جریان')
        ->assertSee('وضعیت را از انجام‌دادنی به در جریان تغییر داد')
        ->assertSee('اعضای تیم مسئول را به‌روزرسانی کرد')
        ->assertDontSee('changed status from')
        ->assertDontSee('updated assigned team members');
});
