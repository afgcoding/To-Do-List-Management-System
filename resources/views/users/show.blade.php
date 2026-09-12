@extends('layouts.app')
@php
    $pageTitle = $user->name;
@endphp

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <x-back-link :href="route('users.index')">Back to users</x-back-link>
    <div class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center">
        <div class="flex items-center gap-4">
            <x-user-avatar :user="$user" size="xl" rounded="2xl" />
            <div>
                <h1 class="text-xl font-bold text-slate-900">{{ $user->name }}</h1>
                <p class="text-sm text-slate-500">{{ $user->job_title ?: 'No job title' }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ $user->email }}@if($user->phone) · {{ $user->phone }}@endif</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <x-badge>{{ $user->department->name ?? 'No department' }}</x-badge>
                    <x-badge :tone="$user->role?->tone() ?? 'slate'">{{ $user->roles->first()?->name ?? $user->role?->label() ?? 'Employee' }}</x-badge>
                    <x-badge :tone="$user->isActive() ? 'emerald' : 'slate'">{{ ucfirst($user->status?->value ?? 'inactive') }}</x-badge>
                </div>
            </div>
        </div>
        @can('update', $user)
            <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Edit profile</a>
        @endcan
    </div>
</div>
@endsection
