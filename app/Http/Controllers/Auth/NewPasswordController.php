<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('password_reset.verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'email' => $request->session()->get('password_reset.email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->session()->get('password_reset.verified')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->session()->get('password_reset.email');
        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            return redirect()->route('password.request');
        }

        $user->forceFill([
            'password' => $request->string('password')->toString(),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $request->session()->forget(['password_reset.email', 'password_reset.verified']);

        return redirect()->route('login')->with('status', 'Your password has been reset. You can sign in now.');
    }
}
