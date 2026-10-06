@extends('layouts.app')

@php
    $pageTitle = 'Profile';
@endphp

@section('content')
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
@endsection
