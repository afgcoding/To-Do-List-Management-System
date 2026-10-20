<?php

use App\Models\Comment;
use App\Models\SystemSetting;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

uses(RefreshDatabase::class);

it('seeds the default system settings row when missing', function () {
    $this->seed(SystemSettingSeeder::class);

    $this->assertDatabaseHas('system_settings', [
        'id' => 1,
        'company_name' => 'Task Management Enterprise',
        'date_format' => 'Y-m-d',
        'time_zone' => 'Asia/Kabul',
    ]);

    $this->seed(SystemSettingSeeder::class);

    expect(SystemSetting::query()->count())->toBe(1);
});

it('renders the system settings page', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    SystemSetting::getSettings();

    $this->get(route('system-settings.index'))
        ->assertOk()
        ->assertViewIs('settings.index')
        ->assertSee('System Settings')
        ->assertSee('Task Management Enterprise')
        ->assertSee('Primary accent color')
        ->assertSee('Asia/Kabul');
});

it('serves the same settings panel at the /settings alias', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    SystemSetting::getSettings();

    $this->get(route('settings.index'))
        ->assertOk()
        ->assertViewIs('settings.index')
        ->assertSee('Company name')
        ->assertSee('Timezone')
        ->assertSee('Date format')
        ->assertSee('Calendar Type')
        ->assertSee('Miladi (Gregorian)')
        ->assertSee('Shamsi (Jalali)')
        ->assertSee('Qamari (Hijri)');
});

it('updates the primary accent color', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    SystemSetting::getSettings();

    $this->from(route('system-settings.index'))
        ->put(route('system-settings.update'), [
            'company_name' => 'Task Management Enterprise',
            'date_format' => 'Y-m-d',
            'time_zone' => 'Asia/Kabul',
            'primary_color' => '#0F766E',
        ])
        ->assertRedirect(route('system-settings.index'));

    $this->assertDatabaseHas('system_settings', [
        'id' => 1,
        'primary_color' => '#0F766E',
    ]);
});

it('updates branding timezone and date format and refreshes the cache', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    SystemSetting::getSettings();

    $this->from(route('system-settings.index'))
        ->put(route('system-settings.update'), [
            'company_name' => 'Hassas SH',
            'date_format' => 'd M Y',
            'time_zone' => 'UTC',
        ])
        ->assertRedirect(route('system-settings.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('system_settings', [
        'id' => 1,
        'company_name' => 'Hassas SH',
        'date_format' => 'd M Y',
        'time_zone' => 'UTC',
    ]);

    expect(Cache::has(SystemSetting::CACHE_KEY))->toBeFalse()
        ->and(setting('company_name'))->toBe('Hassas SH');
});

it('stores a new logo and deletes the previous file', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    Storage::fake('public');
    $settings = SystemSetting::getSettings();
    $oldPath = UploadedFile::fake()->image('old.png')->store('settings', 'public');
    $settings->update(['logo' => $oldPath]);
    SystemSetting::forgetCache();

    $this->put(route('system-settings.update'), [
        'company_name' => 'Task Management Enterprise',
        'date_format' => 'Y-m-d',
        'time_zone' => 'Asia/Kabul',
        'logo' => UploadedFile::fake()->image('brand.png'),
    ])->assertRedirect(route('system-settings.index'));

    $settings = $settings->fresh();

    expect($settings->logo)->not->toBe($oldPath)
        ->and($settings->logo)->toStartWith('settings/');

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($settings->logo);
});

it('removes the stored logo when requested', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    Storage::fake('public');
    $settings = SystemSetting::getSettings();
    $path = UploadedFile::fake()->image('mark.png')->store('settings', 'public');
    $settings->update(['logo' => $path]);
    SystemSetting::forgetCache();

    $this->put(route('system-settings.update'), [
        'company_name' => 'Task Management Enterprise',
        'date_format' => 'Y-m-d',
        'time_zone' => 'Asia/Kabul',
        'remove_logo' => '1',
    ])->assertRedirect(route('system-settings.index'));

    expect($settings->fresh()->logo)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

it('rejects an invalid timezone and date format', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    SystemSetting::getSettings();

    $this->from(route('system-settings.index'))
        ->put(route('system-settings.update'), [
            'company_name' => 'Task Management Enterprise',
            'date_format' => 'not-a-format',
            'time_zone' => 'Not/AZone',
            'calendar_type' => 'lunar-wrong',
        ])
        ->assertRedirect(route('system-settings.index'))
        ->assertSessionHasErrors(['date_format', 'time_zone', 'calendar_type']);
});

it('saves the calendar type and formats dates as shamsi', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    SystemSetting::getSettings();

    $this->from(route('system-settings.index'))
        ->put(route('system-settings.update'), [
            'company_name' => 'Task Management Enterprise',
            'date_format' => 'Y-m-d',
            'time_zone' => 'UTC',
            'calendar_type' => 'jalali',
        ])
        ->assertRedirect(route('system-settings.index'));

    $this->assertDatabaseHas('system_settings', [
        'id' => 1,
        'calendar_type' => 'jalali',
    ]);

    SystemSetting::forgetCache();

    expect(format_date('2026-09-12'))->toBe(Jalalian::fromDateTime('2026-09-12')->format('%Y-%m-%d'));
});

it('formats dates as qamari when hijri is selected', function () {
    SystemSetting::query()->updateOrCreate(['id' => 1], [
        'company_name' => 'Task Management Enterprise',
        'date_format' => 'Y-m-d',
        'time_zone' => 'UTC',
        'calendar_type' => 'hijri',
    ]);
    SystemSetting::forgetCache();

    $formatted = format_date('2026-09-12');

    expect($formatted)->not->toBe('2026-09-12')
        ->and($formatted)->toMatch('/^\d{4}-\d{2}-\d{2}$/');

    expect(parse_calendar_date($formatted))->toBe('2026-09-12');
});

it('round-trips shamsi ymd back to the stored gregorian date', function () {
    SystemSetting::query()->updateOrCreate(['id' => 1], [
        'company_name' => 'Task Management Enterprise',
        'date_format' => 'Y-m-d',
        'time_zone' => 'UTC',
        'calendar_type' => 'jalali',
    ]);
    SystemSetting::forgetCache();

    $jalali = Jalalian::fromDateTime('2026-09-12')->format('%Y-%m-%d');

    expect($jalali)->not->toBe('2026-09-12')
        ->and(parse_calendar_date($jalali))->toBe('2026-09-12')
        ->and(to_calendar_ymd('2026-09-12'))->toBe($jalali);
});

it('formats task comment and activity dates with the saved timezone and format', function () {
    $user = User::factory()->create();
    SystemSetting::query()->updateOrCreate(['id' => 1], [
        'company_name' => 'Task Management Enterprise',
        'date_format' => 'd M Y',
        'time_zone' => 'UTC',
    ]);
    SystemSetting::forgetCache();

    $task = Task::factory()->for($user, 'creator')->create([
        'title' => 'Dated workspace task',
        'due_date' => '2026-09-12 12:00:00',
        'start_date' => '2026-09-12 12:00:00',
    ]);
    Comment::factory()->for($task)->create([
        'user_id' => $user->id,
        'comment' => 'Need the local date.',
    ]);

    expect(format_date($task->due_date))->toBe('12 Sep 2026');

    $this->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('12 Sep 2026');

    $this->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('12 Sep 2026')
        ->assertSee('Need the local date.');

    $this->get(route('activity-logs.index'))
        ->assertOk()
        ->assertSee(format_date(now()));
});
