<?php

use App\Models\SystemSetting;
use App\Models\User;
use App\Support\TotpAuthenticator;
use Illuminate\Support\Facades\Crypt;

function totpCodeFor(User $user): string
{
    $secret = $user->twoFactorSecretPlain();

    expect($secret)->not->toBeNull();

    return TotpAuthenticator::at($secret, intdiv(time(), 30));
}

it('renders the company name and forgot-password link on the login page', function () {
    SystemSetting::query()->updateOrCreate(['id' => 1], [
        'company_name' => 'Hassas SH Workspace',
        'primary_color' => '#0F766E',
    ]);
    SystemSetting::forgetCache();

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Hassas SH Workspace')
        ->assertSee('Forgot your password?')
        ->assertSee('Remember me')
        ->assertSee('#0F766E', false);
});

it('starts two-factor setup from profile and confirms with a valid authenticator code', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->post(route('two-factor.enable'))
        ->assertRedirect(route('profile.edit'));

    $user->refresh();
    expect($user->hasTwoFactorEnabled())->toBeFalse()
        ->and($user->two_factor_secret)->not->toBeNull();

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->post(route('two-factor.confirm'), ['code' => totpCodeFor($user)])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('two_factor.recovery_codes')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->hasTwoFactorEnabled())->toBeTrue();
});

it('rejects an invalid confirmation code while two-factor is pending', function () {
    $user = User::factory()->create();
    $user->startTwoFactorSetup();

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->post(route('two-factor.confirm'), ['code' => '000000'])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

it('challenges two-factor users after a valid password and completes login with totp', function () {
    $user = User::factory()->create();
    $user->startTwoFactorSetup();
    expect($user->confirmTwoFactor(totpCodeFor($user)))->toBeArray();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->assertGuest();

    $this->get(route('two-factor.login'))->assertOk();

    $this->post(route('two-factor.login.store'), [
        'code' => totpCodeFor($user->fresh()),
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

it('accepts a recovery code on the two-factor challenge', function () {
    $user = User::factory()->create();
    $user->startTwoFactorSetup();
    $codes = $user->confirmTwoFactor(totpCodeFor($user));
    expect($codes)->toBeArray()->not->toBeEmpty();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post(route('two-factor.login.store'), [
        'code' => $codes[0],
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);

    $remaining = json_decode(Crypt::decryptString($user->fresh()->two_factor_recovery_codes), true);
    expect($remaining)->toHaveCount(7);
});

it('rejects an invalid two-factor challenge code', function () {
    $user = User::factory()->create();
    $user->startTwoFactorSetup();
    $user->confirmTwoFactor(totpCodeFor($user));

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->from(route('two-factor.login'))
        ->post(route('two-factor.login.store'), ['code' => '000000'])
        ->assertRedirect(route('two-factor.login'))
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});

it('redirects the two-factor challenge to login when no pending login exists', function () {
    $this->get(route('two-factor.login'))->assertRedirect(route('login'));
});

it('disables two-factor authentication with the current password', function () {
    $user = User::factory()->create();
    $user->startTwoFactorSetup();
    $user->confirmTwoFactor(totpCodeFor($user));

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('two-factor.disable'), ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

it('renders two-factor and browser session panels on the profile page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Two-Factor Authentication')
        ->assertSee('Active Browser Sessions');
});

it('logs out other browser sessions when the password is correct', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.sessions.destroy'), ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', 'other-sessions-logged-out');
});

it('requires the current password to log out other sessions', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.sessions.destroy'), ['password' => 'wrong-password'])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrorsIn('logoutOtherSessions', 'password');
});
