<x-guest-layout>
    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Two-factor authentication</h1>
    <p class="mt-2 text-sm leading-6 text-slate-500">
        Enter a code from your authenticator app, or use one of your recovery codes.
    </p>

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Authentication code')" />
            <x-text-input id="code" class="mt-1 block w-full tracking-[0.3em]" type="text" name="code" required autofocus autocomplete="one-time-code" inputmode="numeric" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Continue') }}
        </x-primary-button>
    </form>
</x-guest-layout>
