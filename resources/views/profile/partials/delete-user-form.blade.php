<section id="danger" class="w-full min-w-0 space-y-6">
    <header>
        <h2 class="text-base font-bold text-slate-800">
            {{ __('Deactivate Account') }}
        </h2>

        <p class="mt-0.5 text-xs text-slate-500">
            {{ __('Deactivating your account will disable your access. Your tasks and history will be preserved, and an Administrator can reactivate your account anytime.') }}
        </p>
    </header>

    <button type="button" class="btn btn-danger h-10 w-full rounded-xl px-5 text-xs font-semibold text-white sm:w-auto" data-modal-open="confirm-user-deletion">
        {{ __('Deactivate Account') }}
    </button>

    <x-modal id="confirm-user-deletion" :title="__('Deactivate your account?')">
        <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
            @csrf
            @method('delete')

            <p class="text-sm text-slate-600">
                {{ __('Deactivating your account will disable your access. Your tasks and history will be preserved, and an Administrator can reactivate your account anytime. Please enter your password to confirm.') }}
            </p>

            <div>
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block {{ $profileInput }}"
                    placeholder="{{ __('Password') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-2">
                <button type="button" data-modal-close="confirm-user-deletion" class="btn btn-light h-10 w-full rounded-xl px-5 text-xs font-semibold sm:w-auto">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="btn btn-danger h-10 w-full rounded-xl px-5 text-xs font-semibold text-white sm:w-auto">
                    {{ __('Deactivate Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
