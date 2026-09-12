<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetOtpController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('password_reset.email');

        if (blank($email)) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', [
            'email' => $email,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $email = $request->session()->get('password_reset.email');

        if (blank($email)) {
            return redirect()->route('password.request');
        }

        if (! PasswordResetOtp::codeIsValid((string) $email, $request->string('otp')->toString())) {
            throw ValidationException::withMessages([
                'otp' => 'The verification code is invalid or has expired.',
            ]);
        }

        PasswordResetOtp::consume((string) $email);
        $request->session()->put('password_reset.verified', true);

        return redirect()->route('password.reset');
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset.email');

        if (blank($email)) {
            return redirect()->route('password.request');
        }

        $request->session()->forget('password_reset.verified');

        $user = User::query()->where('email', $email)->first();

        if ($user instanceof User) {
            PasswordResetOtp::sendTo($user);
        }

        return back()->with('status', 'A new code has been sent if the email matches an account.');
    }
}
