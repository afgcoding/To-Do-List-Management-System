<section id="password" class="w-full min-w-0">
    <header>
        <h2 class="text-base font-bold text-slate-800">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-0.5 text-xs text-slate-500">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" :class="$profileLabel" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block {{ $profileInput }}" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" :class="$profileLabel" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block {{ $profileInput }}" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" :class="$profileLabel" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block {{ $profileInput }}" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="btn btn-primary h-10 rounded-xl px-5 text-xs font-semibold text-white shadow-xs transition-all">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
