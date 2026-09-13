<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Active Browser Sessions') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Review devices signed into your account and log out everywhere else.') }}
        </p>
    </header>

    @if (session('status') === 'other-sessions-logged-out')
        <p class="mt-4 text-sm font-medium text-emerald-700">Other browser sessions have been logged out.</p>
    @endif

    <div class="mt-6 space-y-3">
        @forelse ($sessions as $session)
            <div class="flex flex-col items-start justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-start sm:gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800">{{ $session->device }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ $session->ip_address ?? 'Unknown IP' }}
                        · {{ $session->last_active }}
                    </p>
                </div>
                @if ($session->is_current)
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">This device</span>
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

    <form method="POST" action="{{ route('profile.sessions.destroy') }}" class="mt-6 space-y-3">
        @csrf
        @method('DELETE')
        <div>
            <x-input-label for="logout_other_sessions_password" :value="__('Password')" />
            <x-text-input id="logout_other_sessions_password" name="password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->logoutOtherSessions->get('password')" class="mt-2" />
        </div>
        <x-primary-button class="w-full sm:w-auto">{{ __('Log out other browser sessions') }}</x-primary-button>
    </form>
</section>
