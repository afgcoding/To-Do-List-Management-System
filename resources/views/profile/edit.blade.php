@extends('layouts.app')

@php
    $pageTitle = 'Account Settings';
    $hideLayoutPageHeader = true;
    $profileInput = 'h-10 w-full min-w-0 rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-xs text-slate-800 transition-all focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-100';
    $profileLabel = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-500';
    $profileCard = 'profile-settings-card w-full min-w-0 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-6';
    $profileRole = $user->role?->label() ?? 'Employee';
@endphp

@section('content')
<div
    class="profile-settings mx-auto max-w-7xl space-y-5"
    x-data="{
        preview: @js($user->avatar_url),
        pick(event) {
            const file = event.target.files?.[0]
            if (file) {
                this.preview = URL.createObjectURL(file)
            }
        }
    }"
>
    <div class="profile-settings-back">
        <x-back-link :href="route('tasks.index')">Back to tasks</x-back-link>
    </div>

    <div>
        <h1 class="workspace-title">Account Settings</h1>
        <p class="mt-1 text-sm text-gray-500">Manage your account security, profile information, and active sessions.</p>
    </div>

    <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-3 lg:gap-6">
        <aside class="min-w-0 lg:col-span-1">
            <div class="{{ $profileCard }} lg:sticky lg:top-4">
                <div class="flex flex-col items-center text-center">
                    <label for="avatar" class="profile-settings-avatar-wrap">
                        <img :src="preview" src="{{ $user->avatar_url }}" alt="" class="profile-settings-avatar">
                        <span class="profile-settings-avatar-overlay" aria-hidden="true">
                            <i class="fas fa-camera"></i>
                        </span>
                    </label>
                    <p class="mt-3 text-base font-bold text-slate-800">{{ $user->name }}</p>
                    <p class="mt-0.5 break-all text-xs text-slate-500">{{ $user->email }}</p>
                    <span class="badge mt-2">{{ $profileRole }}</span>
                    <p class="mt-2 text-xs text-slate-500">Click the photo to change it, then save.</p>
                </div>

                <nav class="profile-settings-pills" aria-label="Settings sections">
                    <a href="#profile">Profile</a>
                    <a href="#password">Password</a>
                    <a href="#two-factor">2FA</a>
                    <a href="#sessions">Sessions</a>
                    <a href="#danger">Danger Zone</a>
                </nav>
            </div>
        </aside>

        <div class="min-w-0 w-full space-y-4 lg:col-span-2 lg:space-y-6">
            <div class="{{ $profileCard }}">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="{{ $profileCard }}">
                @include('profile.partials.update-password-form')
            </div>

            <div class="{{ $profileCard }}">
                @include('profile.partials.two-factor-authentication-form')
            </div>

            <div class="{{ $profileCard }}">
                @include('profile.partials.browser-sessions-form')
            </div>

            <div class="{{ $profileCard }} border-rose-100 bg-rose-50/30 p-5">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
