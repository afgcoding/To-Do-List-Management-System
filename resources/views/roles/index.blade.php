@extends('layouts.app')
@php
    $pageTitle = 'Roles & Permissions';
    $hideLayoutPageHeader = true;
    $roleNames = $roles->pluck('name')->map(fn ($name) => mb_strtolower($name))->values();
@endphp

@section('content')
<div class="roles-page mx-auto max-w-7xl space-y-5" x-data="{ q: '' }">
    <div class="roles-header">
        <div class="min-w-0">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
            </ol>
            <h1 class="workspace-title">Roles &amp; Permissions</h1>
            <p class="mt-1 text-sm text-gray-500">Create workspace roles and assign granular permissions.</p>
        </div>
        @can('create', Spatie\Permission\Models\Role::class)
            <a href="{{ route('roles.create') }}" class="btn btn-primary roles-add">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New role
            </a>
        @endcan
    </div>

    <div class="roles-panel w-full rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="nozha-filter-container">
            <form class="nozha-filter-form" @submit.prevent>
                <div class="nozha-input-wrapper">
                    <input type="search"
                           x-model="q"
                           placeholder="Search roles..."
                           class="nozha-filter-input"
                           aria-label="Search roles">
                </div>
            </form>
        </div>

        <div class="roles-grid">
            @forelse ($roles as $role)
                <article
                    class="roles-card"
                    x-show="! q.trim() || @js(mb_strtolower($role->name)).includes(q.trim().toLowerCase())">
                    <div class="roles-card-top">
                        <h2 class="roles-card-title">{{ $role->name }}</h2>
                        <x-badge tone="indigo">{{ $role->permissions_count }} perms</x-badge>
                    </div>
                    <p class="roles-card-meta">{{ $role->users_count }} assigned {{ \Illuminate\Support\Str::plural('user', $role->users_count) }}</p>
                    <div class="roles-actions">
                        @can('update', $role)
                            <a href="{{ route('roles.edit', $role) }}" class="roles-action">Edit</a>
                        @endcan
                        @can('delete', $role)
                            <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="roles-action roles-action-danger">Delete</button>
                            </form>
                        @endcan
                    </div>
                </article>
            @empty
                <p class="roles-empty">No roles have been defined yet.</p>
            @endforelse
            <p
                class="roles-empty"
                x-show="q.trim() !== '' && ! @js($roleNames).some(name => name.includes(q.trim().toLowerCase()))"
                x-cloak>
                No roles match that search.
            </p>
        </div>
    </div>
</div>
@endsection
