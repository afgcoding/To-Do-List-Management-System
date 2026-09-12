<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $normalized = strtolower($request->string('email')->toString());

        $request->session()->put('password_reset.email', $normalized);
        $request->session()->forget('password_reset.verified');

        $user = User::query()->where('email', $normalized)->first();

        if ($user instanceof User) {
            PasswordResetOtp::sendTo($user);
        }

        return redirect()->route('password.otp');
    }
}
