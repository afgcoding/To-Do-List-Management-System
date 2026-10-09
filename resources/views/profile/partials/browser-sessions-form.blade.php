<section id="sessions" class="w-full min-w-0">
    <header>
        <h2 class="text-base font-bold text-slate-800">
            {{ __('Active Browser Sessions') }}
        </h2>
        <p class="mt-0.5 text-xs text-slate-500">
            {{ __('Review devices signed into your account and log out everywhere else.') }}
        </p>
    </header>

    @if (session('status') === 'other-sessions-logged-out')
        <p class="mt-4 text-sm font-medium text-emerald-700">Other browser sessions have been logged out.</p>
    @endif

    <div class="mt-6 space-y-3">
        @forelse ($sessions as $session)
            @php
                $deviceLabel = strtolower((string) $session->device);
                $sessionIcon = match (true) {
                    str_contains($deviceLabel, 'android'), str_contains($deviceLabel, 'ios') => 'fas fa-mobile-alt',
                    str_contains($deviceLabel, 'windows') => 'fab fa-windows',
                    str_contains($deviceLabel, 'chrome') => 'fab fa-chrome',
                    default => 'fas fa-desktop',
                };
            @endphp
            <div class="flex flex-col items-start justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:gap-4">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="profile-session-icon" aria-hidden="true"><i class="{{ $sessionIcon }}"></i></span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800">{{ $session->device }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $session->ip_address ?? 'Unknown IP' }}
                            · {{ $session->last_active }}
                        </p>
                    </div>
                </div>
                @if ($session->is_current)
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600">This Device</span>
                @endif
            </div>
        @empty
            <p class="text-sm text-slate-500">
                {{ config('session.driver') === 'database'
                    ? 'No other recorded sessions were found.'
                    : 'Session listing is available when the application stores sessions in the database.' }}
            </p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('profile.sessions.destroy') }}" class="mt-6 grid grid-cols-1 items-end gap-3 sm:grid-cols-[1fr_auto]">
        @csrf
        @method('DELETE')
        <div class="min-w-0">
            <x-input-label for="logout_other_sessions_password" :value="__('Password')" :class="$profileLabel" />
            <x-text-input id="logout_other_sessions_password" name="password" type="password" class="mt-1 block {{ $profileInput }}" autocomplete="current-password" />
            <x-input-error :messages="$errors->logoutOtherSessions->get('password')" class="mt-2" />
        </div>
        <button type="submit" class="btn btn-primary h-10 w-full rounded-xl px-5 text-xs font-semibold text-white shadow-xs transition-all sm:w-auto">{{ __('Log out other browser sessions') }}</button>
    </form>
</section>
