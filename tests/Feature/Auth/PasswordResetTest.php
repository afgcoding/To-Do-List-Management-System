<?php

use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

test('the otp email includes the 6-digit code', function () {
    $mailable = new PasswordResetOtpMail('482910', 'Ada Lovelace', 'Hassas SH');

    $mailable->assertSeeInHtml('482910')
        ->assertSeeInHtml('Hassas SH')
        ->assertSeeInHtml('Ada Lovelace');
});

test('reset password request screen can be rendered', function () {
    $this->get('/forgot-password')
        ->assertOk()
        ->assertSee('Send code');
});

test('a 6-digit otp is emailed when a known account requests a reset', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertRedirect(route('password.otp'));

    Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use ($user): bool {
        expect($mail->code)->toMatch('/^\d{6}$/')
            ->and(Hash::check($mail->code, PasswordResetOtp::query()->where('email', $user->email)->value('otp_code')))->toBeTrue();

        return $mail->hasTo($user->email);
    });

    $this->get(route('password.otp'))
        ->assertOk()
        ->assertSee('Check your email');
});

test('an unknown email does not send an otp mail', function () {
    Mail::fake();

    $this->post('/forgot-password', ['email' => 'missing@example.com'])
        ->assertRedirect(route('password.otp'));

    Mail::assertNothingSent();
});

test('the otp screen redirects when no reset has been requested', function () {
    $this->get(route('password.otp'))->assertRedirect(route('password.request'));
    $this->get(route('password.reset'))->assertRedirect(route('password.request'));
});

test('an invalid otp is rejected', function () {
    Mail::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $this->from(route('password.otp'))
        ->post(route('password.otp.store'), ['otp' => '000000'])
        ->assertRedirect(route('password.otp'))
        ->assertSessionHasErrors('otp');
});

test('an expired otp cannot be used', function () {
    Mail::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $this->travel(11)->minutes();

    $code = '';
    Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$code): bool {
        $code = $mail->code;

        return true;
    });

    $this->from(route('password.otp'))
        ->post(route('password.otp.store'), ['otp' => $code])
        ->assertSessionHasErrors('otp');
});

test('password can be reset after a valid otp', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    $code = '';
    Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$code): bool {
        $code = $mail->code;

        return true;
    });

    $this->post(route('password.otp.store'), ['otp' => $code])
        ->assertRedirect(route('password.reset'));

    $this->get(route('password.reset'))
        ->assertOk()
        ->assertSee('Reset password');

    $this->post('/reset-password', [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect(route('login'));

    $this->assertGuest();
    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue()
        ->and(PasswordResetOtp::query()->where('email', $user->email)->exists())->toBeFalse();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'new-password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('a new otp can be resent from the verification screen', function () {
    Mail::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);
    Mail::assertSent(PasswordResetOtpMail::class, 1);

    $this->from(route('password.otp'))
        ->post(route('password.otp.resend'))
        ->assertRedirect(route('password.otp'));

    Mail::assertSent(PasswordResetOtpMail::class, 2);
});
