<x-guest-layout>
    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Reset password</h1>
    <p class="mt-2 text-sm text-slate-500">Choose a new password for <span class="font-medium text-slate-700">{{ $email }}</span>.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('New password')" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Reset Password') }}
        </x-primary-button>
    </form>
</x-guest-layout>
