@extends('layouts.app')
@php
    $pageTitle = 'Roles & Permissions';
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Roles &amp; Permissions</h1>
            <p class="mt-1 text-sm text-slate-500">Create workspace roles and assign granular permissions.</p>
        </div>
        @can('create', Spatie\Permission\Models\Role::class)
            <a href="{{ route('roles.create') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">
                New role
            </a>
        @endcan
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @forelse ($roles as $role)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-semibold text-slate-900">{{ $role->name }}</h2>
                    <x-badge tone="indigo">{{ $role->permissions_count }} perms</x-badge>
                </div>
                <p class="mt-2 text-sm text-slate-500">{{ $role->users_count }} assigned {{ \Illuminate\Support\Str::plural('user', $role->users_count) }}</p>
                <div class="mt-4 flex gap-2">
                    @can('update', $role)
                        <a href="{{ route('roles.edit', $role) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">Edit</a>
                    @endcan
                    @can('delete', $role)
                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete this role?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                        </form>
                    @endcan
                </div>
            </article>
        @empty
            <p class="text-sm text-slate-500">No roles have been defined yet.</p>
        @endforelse
    </div>
</div>
@endsection
