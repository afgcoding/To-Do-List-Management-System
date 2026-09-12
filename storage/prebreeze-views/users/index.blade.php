@extends('layouts.app')
@php
    $pageTitle = 'Users & Roles';
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Users &amp; Team</h1>
            <p class="mt-1 text-sm text-slate-500">Manage profiles, roles, departments, and account status.</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
            Add user
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3">User</th>
                    <th class="px-5 py-3">Contact</th>
                    <th class="px-5 py-3">Department &amp; role</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50/70" x-data="{ menu: false }">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <x-user-avatar :name="$user->name" :src="$user->avatar ? $user->avatar_url : null" size="lg" />
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-800">{{ $user->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $user->job_title ?: 'No job title' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="text-slate-700">{{ $user->email }}</p>
                            <p class="text-xs text-slate-500">{{ $user->phone ?: 'No phone' }}</p>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @if ($user->department)
                                    <x-badge>{{ $user->department->name }}</x-badge>
                                @else
                                    <span class="text-xs text-slate-400">No department</span>
                                @endif
                                <x-badge :tone="$user->role?->tone() ?? 'slate'">{{ $user->role?->label() ?? 'Member' }}</x-badge>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if ($user->status === 'active')
                                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700">
                                    <span class="relative flex size-2">
                                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                                        <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                                    </span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500">
                                    <span class="size-2 rounded-full bg-slate-400"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="relative inline-block text-left">
                                <button type="button" @click="menu = !menu" class="rounded-lg px-2 py-1 text-sm font-medium text-slate-600 hover:bg-slate-100">
                                    Actions
                                </button>
                                <div x-show="menu" x-cloak @click.outside="menu = false" class="absolute right-0 z-20 mt-1 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-lg">
                                    <a href="{{ route('users.edit', $user) }}" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Edit</a>
                                    <a href="{{ route('tasks.index', ['assigned_user_id' => $user->id]) }}" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">View tasks</a>
                                    <form method="POST" action="{{ route('users.status.toggle', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">
                                            {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="block w-full px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-14 text-center text-slate-500">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $users->links() }}</div>
</div>
@endsection
