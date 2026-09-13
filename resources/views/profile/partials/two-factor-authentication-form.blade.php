<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Two-Factor Authentication') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add an authenticator app (such as Google Authenticator) for an extra sign-in step.') }}
        </p>
    </header>

    @if (session('status') === 'two-factor-enabled')
        <p class="mt-4 text-sm font-medium text-emerald-700">Two-factor authentication is now enabled.</p>
    @elseif (session('status') === 'two-factor-disabled')
        <p class="mt-4 text-sm font-medium text-slate-600">Two-factor authentication has been disabled.</p>
    @endif

    @if (session('two_factor.recovery_codes'))
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-900">Recovery codes</p>
            <p class="mt-1 text-xs text-amber-800">Store these somewhere safe. Each code can be used once if you lose your authenticator.</p>
            <ul class="mt-3 grid grid-cols-1 gap-2 font-mono text-sm text-slate-800 sm:grid-cols-2">
                @foreach (session('two_factor.recovery_codes') as $recoveryCode)
                    <li class="rounded-lg bg-white px-2 py-1.5 text-center shadow-sm">{{ $recoveryCode }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($user->hasTwoFactorEnabled())
        <p class="mt-4 text-sm text-slate-600">Authenticator codes are required when you sign in.</p>
        <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-4 space-y-3">
            @csrf
            @method('DELETE')
            <div>
                <x-input-label for="two_factor_password" :value="__('Password')" />
                <x-text-input id="two_factor_password" name="password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <x-danger-button class="w-full justify-center sm:w-auto">{{ __('Disable two-factor authentication') }}</x-danger-button>
        </form>
    @elseif (filled($user->two_factor_secret))
        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-semibold text-slate-800">Finish setup</p>
            <p class="mt-1 text-sm text-slate-600">Scan this QR code, then enter the 6-digit code from your app.</p>
            @if ($user->twoFactorQrImageUrl())
                <img src="{{ $user->twoFactorQrImageUrl() }}" alt="Two-factor QR code" class="mt-4 h-auto w-full max-w-48 rounded-lg border border-slate-200 bg-white p-2">
            @endif
            <p class="mt-3 text-xs font-medium uppercase tracking-wide text-slate-500">Manual key</p>
            <p class="mt-1 break-all font-mono text-sm tracking-wide text-slate-800 sm:tracking-widest">{{ $user->twoFactorSecretPlain() }}</p>

            <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <x-input-label for="two_factor_code" :value="__('Authentication code')" />
                    <x-text-input id="two_factor_code" name="code" type="text" class="mt-1 block w-full" inputmode="numeric" autocomplete="one-time-code" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>
                <x-primary-button>{{ __('Confirm and enable') }}</x-primary-button>
            </form>
        </div>
    @else
        <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-6">
            @csrf
            <x-primary-button>{{ __('Enable two-factor authentication') }}</x-primary-button>
        </form>
    @endif
</section>
