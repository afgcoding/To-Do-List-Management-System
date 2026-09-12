<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-slate-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button type="button" data-modal-open="confirm-user-deletion">
        {{ __('Delete Account') }}
    </x-danger-button>

    <x-modal id="confirm-user-deletion" :title="__('Are you sure you want to delete your account?')">
        <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
            @csrf
            @method('delete')

            <p class="text-sm text-slate-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div>
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Password') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" data-modal-close="confirm-user-deletion" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    {{ __('Cancel') }}
                </button>
                <x-danger-button>
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
