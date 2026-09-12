@extends('layouts.app')
@php
    $pageTitle = 'Users & Roles';
@endphp

@section('content')
<div
    class="mx-auto max-w-7xl space-y-6"
    x-data="usersIndex(@js($directory))"
    @keydown.escape.window="closeMenu(); profileId = null; confirm = null">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Users &amp; Team</h1>
            <p class="mt-1 text-sm text-slate-500">Manage profiles, roles, departments, and account status.</p>
        </div>
        @can('create', App\Models\User::class)
        <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
            Add user
        </a>
        @endcan
    </div>

    <div class="overflow-visible rounded-xl border border-slate-200 bg-white shadow-sm">
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
                    <tr class="hover:bg-slate-50/70">
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
                                <x-badge :tone="$user->role?->tone() ?? 'slate'">{{ $user->roles->first()?->name ?? $user->role?->label() ?? 'Employee' }}</x-badge>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @php($canToggle = auth()->user()?->can('toggleStatus', $user) ?? false)
                            <div class="inline-flex items-center gap-2.5">
                                <button
                                    type="button"
                                    role="switch"
                                    @if ($canToggle)
                                        @click="toggleStatus({{ $user->id }})"
                                        :disabled="row({{ $user->id }})?.busy"
                                    @else
                                        disabled
                                    @endif
                                    :aria-checked="row({{ $user->id }})?.isActive ? 'true' : 'false'"
                                    :aria-label="row({{ $user->id }})?.isActive ? 'Set status to Inactive' : 'Set status to Active'"
                                    @class([
                                        'relative inline-flex h-6 w-11 shrink-0 rounded-full border-0 p-0.5 shadow-inner transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2',
                                        $user->isActive() ? 'bg-emerald-500' : 'bg-rose-500',
                                        $canToggle ? 'cursor-pointer' : 'cursor-not-allowed opacity-60',
                                    ])
                                    :class="row({{ $user->id }})?.isActive ? 'bg-emerald-500' : 'bg-rose-500'">
                                    <span
                                        @class([
                                            'pointer-events-none inline-flex size-5 items-center justify-center rounded-full bg-white shadow transition duration-200',
                                            $user->isActive() ? 'translate-x-5' : 'translate-x-0',
                                        ])
                                        :class="{
                                            'translate-x-5': row({{ $user->id }})?.isActive,
                                            'translate-x-0': ! row({{ $user->id }})?.isActive,
                                            'animate-pulse': row({{ $user->id }})?.busy,
                                        }">
                                        <svg x-show="row({{ $user->id }})?.busy" x-cloak class="size-3 animate-spin text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
                                        </svg>
                                    </span>
                                </button>
                                <span
                                    @class([
                                        'text-sm font-medium',
                                        $user->isActive() ? 'text-emerald-600' : 'text-rose-600',
                                    ])
                                    :class="row({{ $user->id }})?.isActive ? 'text-emerald-600' : 'text-rose-600'"
                                    x-text="row({{ $user->id }})?.isActive ? 'Active' : 'Inactive'">{{ $user->isActive() ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <button
                                type="button"
                                @click.stop="openMenu({{ $user->id }}, $event)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50">
                                Actions
                                <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
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

    <template x-teleport="body">
        <div
            x-show="selected"
            x-cloak
            @click.outside="closeMenu()"
            @click.stop
            class="fixed z-[80] w-52 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl"
            :style="{ top: menu.top + 'px', right: menu.right + 'px' }">
            <template x-if="selected?.canView">
                <button type="button" @click="openProfile(selected.id)" class="block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">View Profile</button>
            </template>
            <a x-show="selected" :href="selected?.tasksUrl" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">View Tasks</a>
            <template x-if="selected?.canUpdate">
                <a :href="selected.editUrl" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Edit</a>
            </template>
            <template x-if="selected?.canDelete">
                <button type="button" @click="ask(selected.id, 'delete')" class="block w-full px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Delete</button>
            </template>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="profile" x-cloak class="fixed inset-0 z-[70]" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-950/40" @click="profileId = null"></div>
            <div
                class="absolute inset-y-0 right-0 flex w-full max-w-lg flex-col border-l border-slate-200 bg-white shadow-xl"
                @click.stop
                x-show="profile"
                x-transition:enter="transform transition duration-200 ease-out"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition duration-150 ease-in"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full">
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-6 py-5">
                    <div class="flex min-w-0 items-start gap-4">
                        <template x-if="profile?.avatar">
                            <img :src="profile.avatar" :alt="profile.name" class="size-16 shrink-0 rounded-2xl object-cover ring-2 ring-white">
                        </template>
                        <template x-if="profile && !profile.avatar">
                            <div class="grid size-16 shrink-0 place-items-center rounded-2xl bg-indigo-600 text-lg font-bold text-white" x-text="profile.name.substring(0, 2).toUpperCase()"></div>
                        </template>
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-semibold text-slate-900" x-text="profile?.name"></h2>
                            <p class="truncate text-sm text-slate-500" x-text="profile?.jobTitle"></p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700" x-text="profile?.role"></span>
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="profile?.isActive ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-600'"
                                    x-text="profile?.status"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="profileId = null" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Close">×</button>
                </div>
                <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5" x-show="profile">
                    <section>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Contact details</h3>
                        <dl class="mt-3 grid grid-cols-1 gap-3 text-sm">
                            <div><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-800" x-text="profile?.email"></dd></div>
                            <div><dt class="text-slate-500">Phone</dt><dd class="font-medium text-slate-800" x-text="profile?.phone"></dd></div>
                            <div><dt class="text-slate-500">Joined</dt><dd class="font-medium text-slate-800" x-text="profile?.joined"></dd></div>
                            <div><dt class="text-slate-500">Last login</dt><dd class="font-medium text-slate-800" x-text="profile?.lastLogin"></dd></div>
                        </dl>
                    </section>
                    <section>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Organization</h3>
                        <dl class="mt-3 grid grid-cols-1 gap-3 text-sm">
                            <div><dt class="text-slate-500">Department</dt><dd class="font-medium text-slate-800" x-text="profile?.department"></dd></div>
                            <div><dt class="text-slate-500">Direct manager</dt><dd class="font-medium text-slate-800" x-text="profile?.manager"></dd></div>
                            <div>
                                <dt class="text-slate-500">Assigned role</dt>
                                <dd class="mt-1"><span class="inline-flex items-center rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700" x-text="profile?.role"></span></dd>
                            </div>
                        </dl>
                    </section>
                    <section>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Task summary</h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700" x-text="'Total assigned: ' + (profile?.assignedTasks ?? 0)"></span>
                            <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700" x-text="'Completed: ' + (profile?.completedTasks ?? 0)"></span>
                            <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700" x-text="'Overdue: ' + (profile?.overdueTasks ?? 0)"></span>
                        </div>
                    </section>
                    <details class="rounded-xl border border-slate-200 bg-slate-50/70 p-4" open>
                        <summary class="cursor-pointer text-sm font-semibold text-slate-800">Permissions</summary>
                        <div class="mt-3 space-y-3">
                            <div>
                                <p class="text-xs font-medium text-slate-500">Direct grants</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <template x-for="name in (profile?.directPermissions ?? [])" :key="'d-' + name">
                                        <span class="inline-flex rounded-full border border-indigo-200 bg-white px-2 py-0.5 text-[11px] font-medium text-indigo-700" x-text="name"></span>
                                    </template>
                                    <span class="text-xs text-slate-400" x-show="!(profile?.directPermissions?.length)">None</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500">From role</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <template x-for="name in (profile?.rolePermissions ?? [])" :key="'r-' + name">
                                        <span class="inline-flex rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-medium text-slate-600" x-text="name"></span>
                                    </template>
                                    <span class="text-xs text-slate-400" x-show="!(profile?.rolePermissions?.length)">None</span>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="pending" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-950/40" @click="confirm = null"></div>
            <div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                <h3 class="text-base font-semibold text-slate-900">Delete user</h3>
                <p class="mt-2 text-sm text-slate-600">
                    This permanently removes
                    <span class="font-semibold text-slate-800" x-text="pending?.name"></span>.
                </p>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" @click="confirm = null" class="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
                    <form method="POST" :action="pending?.deleteUrl">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-xl bg-rose-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-rose-700">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <div
        x-show="toast"
        x-cloak
        x-transition
        class="fixed right-5 bottom-5 z-[100] max-w-sm rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg"
        role="status"
        aria-live="polite"
        x-text="toast"></div>
</div>
@endsection
