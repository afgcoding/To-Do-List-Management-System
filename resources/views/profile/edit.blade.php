<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div>
        <div class="mx-auto max-w-7xl space-y-6">
            <x-back-link :href="route('tasks.index')">Back to tasks</x-back-link>
            <div class="p-4 sm:p-8 bg-white shadow rounded-xl border border-slate-200">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow rounded-xl border border-slate-200">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow rounded-xl border border-slate-200">
                <div class="max-w-xl">
                    @include('profile.partials.two-factor-authentication-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow rounded-xl border border-slate-200">
                <div class="max-w-xl">
                    @include('profile.partials.browser-sessions-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow rounded-xl border border-slate-200">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
