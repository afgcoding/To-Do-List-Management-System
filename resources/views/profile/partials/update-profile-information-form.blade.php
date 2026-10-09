<section id="profile" class="w-full min-w-0">
    <header>
        <h2 class="text-base font-bold text-slate-800">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-0.5 text-xs text-slate-500">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="profile-settings-form mt-4 w-full space-y-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <label for="avatar" class="profile-settings-avatar-wrap profile-settings-avatar-wrap-sm">
                <img :src="preview" src="{{ $user->avatar_url }}" alt="" class="profile-settings-avatar">
                <span class="profile-settings-avatar-overlay" aria-hidden="true">
                    <i class="fas fa-camera"></i>
                </span>
            </label>
            <div class="min-w-0 flex-1">
                <p class="text-xs text-slate-500">Change your photo, name, and email, then save.</p>
                <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/jpg,image/webp" class="sr-only" @change="pick($event)">
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div class="w-full min-w-0">
            <x-input-label for="name" :value="__('Name')" :class="$profileLabel" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full {{ $profileInput }}" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="w-full min-w-0">
            <x-input-label for="email" :value="__('Email')" :class="$profileLabel" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full {{ $profileInput }}" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button type="submit" class="btn btn-primary h-10 rounded-xl px-5 text-xs font-semibold text-white shadow-xs transition-all">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
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
