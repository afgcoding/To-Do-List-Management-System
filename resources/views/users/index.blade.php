@extends('layouts.app')
@php
    $pageTitle = 'Users & Team';
    $hideLayoutPageHeader = true;
    $status = in_array(request('status'), ['active', 'inactive'], true) ? request('status') : '';
    $statusLabel = match ($status) {
        'active' => 'Active',
        'inactive' => 'Inactive',
        default => 'All statuses',
    };
@endphp

@section('content')
<div
    class="users-page mx-auto max-w-7xl space-y-5"
    x-data="usersIndex(@js($directory))"
    @keydown.escape.window="closeMenu(); profileId = null; confirm = null">
    <div class="users-header">
        <div class="min-w-0">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
            </ol>
            <h1 class="workspace-title">Users &amp; Team</h1>
            <p class="mt-1 text-sm text-gray-500">Manage profiles, roles, departments, and account status.</p>
        </div>
        @can('create', App\Models\User::class)
            <a href="{{ route('users.create') }}" class="btn btn-primary users-add">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add user
            </a>
        @endcan
    </div>

    <div class="users-card w-full rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="nozha-filter-container">
            <form action="{{ route('users.index') }}" method="GET" class="nozha-filter-form">
                <div class="nozha-input-wrapper">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by name, email, or role..."
                           class="nozha-filter-input">
                </div>

                <div class="nozha-select-wrapper"
                    x-data="{
                        open: false,
                        value: @js($status),
                        label: @js($statusLabel)
                    }"
                    @click.outside="open = false">
                    <span class="nozha-select-label" x-text="label">{{ $statusLabel }}</span>
                    <input type="hidden" name="status" x-model="value" value="{{ $status }}">
                    <button type="button" class="nozha-select-trigger" @click="open = ! open" aria-haspopup="listbox" aria-label="Filter by status"></button>
                    <div class="nozha-select-menu" x-show="open" x-cloak>
                        <button type="button" class="nozha-select-option" @click="value = ''; label = 'All statuses'; open = false">All statuses</button>
                        <button type="button" class="nozha-select-option" @click="value = 'active'; label = 'Active'; open = false">Active</button>
                        <button type="button" class="nozha-select-option" @click="value = 'inactive'; label = 'Inactive'; open = false">Inactive</button>
                    </div>
                </div>

                <button type="submit" class="nozha-filter-btn">
                    <svg class="nozha-filter-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span>Filter</span>
                </button>
            </form>
        </div>

        <div class="users-list-mobile">
            @forelse ($users as $user)
                <article class="users-mobile-card">
                    <div class="users-mobile-top">
                        <div class="users-person">
                            <x-user-avatar :user="$user" size="lg" class="h-10 w-10 object-cover" />
                            <div class="min-w-0">
                                <p class="users-person-name">{{ $user->name }}</p>
                                <p class="users-person-meta">{{ $user->job_title ?: 'No job title' }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click.stop="openMenu({{ $user->id }}, $event)"
                            class="users-actions-btn">
                            Actions
                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </button>
                    </div>
                    <div class="users-contact">
                        <p>{{ $user->email }}</p>
                        <p>{{ $user->phone ?: 'No phone' }}</p>
                    </div>
                    <div class="users-tags">
                        @if ($user->department)
                            <x-badge>{{ $user->department->name }}</x-badge>
                        @else
                            <span class="users-muted">No department</span>
                        @endif
                        <x-badge :tone="$user->role?->tone() ?? 'slate'">{{ $user->roles->first()?->name ?? $user->role?->label() ?? 'Employee' }}</x-badge>
                    </div>
                    <div class="users-mobile-status">
                        @include('users.partials.status-toggle', ['user' => $user])
                    </div>
                </article>
            @empty
                <div class="users-empty">No users found.</div>
            @endforelse
        </div>

        <div class="users-list-table">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse text-left">
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
                                    <div class="users-person">
                                        <x-user-avatar :user="$user" size="lg" class="h-10 w-10 object-cover" />
                                        <div class="min-w-0">
                                            <p class="users-person-name">{{ $user->name }}</p>
                                            <p class="users-person-meta">{{ $user->job_title ?: 'No job title' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="users-contact">
                                        <p>{{ $user->email }}</p>
                                        <p>{{ $user->phone ?: 'No phone' }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="users-tags">
                                        @if ($user->department)
                                            <x-badge>{{ $user->department->name }}</x-badge>
                                        @else
                                            <span class="users-muted">No department</span>
                                        @endif
                                        <x-badge :tone="$user->role?->tone() ?? 'slate'">{{ $user->roles->first()?->name ?? $user->role?->label() ?? 'Employee' }}</x-badge>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @include('users.partials.status-toggle', ['user' => $user])
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button
                                        type="button"
                                        @click.stop="openMenu({{ $user->id }}, $event)"
                                        class="users-actions-btn">
                                        Actions
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
        </div>
        <div class="overflow-x-auto border-t border-slate-100 px-3 py-3">{{ $users->links() }}</div>
    </div>

    <template x-teleport="body">
        <div
            x-show="selected"
            x-cloak
            @click.outside="closeMenu()"
            @click.stop
            class="users-menu fixed z-[80] w-52 overflow-hidden border border-slate-200 bg-white py-1 text-left shadow-xl"
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
                class="users-drawer absolute inset-y-0 right-0 flex w-full max-w-lg flex-col border-l border-slate-200 bg-white shadow-xl"
                @click.stop
                x-show="profile"
                x-transition:enter="transform transition duration-200 ease-out"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition duration-150 ease-in"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full">
                <div class="users-drawer-head">
                    <div class="users-drawer-identity">
                        <template x-if="profile?.avatar">
                            <img :src="profile.avatar" :alt="profile.name" class="users-drawer-photo">
                        </template>
                        <template x-if="profile && !profile.avatar">
                            <div class="users-drawer-avatar" x-text="profile.name.substring(0, 2).toUpperCase()"></div>
                        </template>
                        <div class="users-drawer-id">
                            <h2 class="users-drawer-name" x-text="profile?.name"></h2>
                            <p class="users-drawer-job" x-text="profile?.jobTitle"></p>
                            <div class="users-drawer-pills">
                                <span class="users-drawer-pill users-drawer-pill-role" x-text="profile?.role"></span>
                                <span class="users-drawer-pill"
                                    :class="profile?.isActive ? 'users-drawer-pill-active' : 'users-drawer-pill-inactive'"
                                    x-text="profile?.status"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="profileId = null" class="users-icon-btn" aria-label="Close">×</button>
                </div>
                <div class="users-drawer-body" x-show="profile">
                    <section class="users-drawer-card">
                        <h3 class="users-drawer-kicker">Contact details</h3>
                        <dl class="users-drawer-list">
                            <div><dt>Email</dt><dd x-text="profile?.email"></dd></div>
                            <div><dt>Phone</dt><dd x-text="profile?.phone"></dd></div>
                            <div><dt>Joined</dt><dd x-text="profile?.joined"></dd></div>
                            <div><dt>Last login</dt><dd x-text="profile?.lastLogin"></dd></div>
                        </dl>
                    </section>
                    <section class="users-drawer-card">
                        <h3 class="users-drawer-kicker">Organization</h3>
                        <dl class="users-drawer-list">
                            <div><dt>Department</dt><dd x-text="profile?.department"></dd></div>
                            <div><dt>Direct manager</dt><dd x-text="profile?.manager"></dd></div>
                            <div>
                                <dt>Assigned role</dt>
                                <dd><span class="users-drawer-pill users-drawer-pill-role" x-text="profile?.role"></span></dd>
                            </div>
                        </dl>
                    </section>
                    <section class="users-drawer-card">
                        <h3 class="users-drawer-kicker">Task summary</h3>
                        <div class="users-drawer-stats">
                            <div class="users-drawer-stat">
                                <span class="users-drawer-stat-value" x-text="profile?.assignedTasks ?? 0"></span>
                                <span class="users-drawer-stat-label">Assigned</span>
                            </div>
                            <div class="users-drawer-stat users-drawer-stat-done">
                                <span class="users-drawer-stat-value" x-text="profile?.completedTasks ?? 0"></span>
                                <span class="users-drawer-stat-label">Completed</span>
                            </div>
                            <div class="users-drawer-stat users-drawer-stat-late">
                                <span class="users-drawer-stat-value" x-text="profile?.overdueTasks ?? 0"></span>
                                <span class="users-drawer-stat-label">Overdue</span>
                            </div>
                        </div>
                    </section>
                    <details class="users-drawer-card users-drawer-permissions" open>
                        <summary>Permissions</summary>
                        <div class="users-drawer-perm-groups">
                            <div>
                                <p>Direct grants</p>
                                <div class="users-drawer-chips">
                                    <template x-for="name in (profile?.directPermissions ?? [])" :key="'d-' + name">
                                        <span class="users-drawer-chip users-drawer-chip-direct" x-text="name"></span>
                                    </template>
                                    <span class="users-drawer-none" x-show="!(profile?.directPermissions?.length)">None</span>
                                </div>
                            </div>
                            <div>
                                <p>From role</p>
                                <div class="users-drawer-chips">
                                    <template x-for="name in (profile?.rolePermissions ?? [])" :key="'r-' + name">
                                        <span class="users-drawer-chip" x-text="name"></span>
                                    </template>
                                    <span class="users-drawer-none" x-show="!(profile?.rolePermissions?.length)">None</span>
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
            <div class="users-confirm relative w-full max-w-sm border border-slate-200 bg-white p-5 shadow-xl">
                <h3 class="text-base font-semibold text-slate-900">Delete user</h3>
                <p class="mt-2 text-sm text-slate-600">
                    This permanently removes
                    <span class="font-semibold text-slate-800" x-text="pending?.name"></span>.
                </p>
                <div class="users-confirm-actions">
                    <button type="button" @click="confirm = null" class="users-confirm-cancel">Cancel</button>
                    <form method="POST" :action="pending?.deleteUrl">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="users-confirm-delete">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <div
        x-show="toast"
        x-cloak
        x-transition
        class="users-toast fixed inset-x-4 bottom-5 z-[100] mx-auto max-w-sm px-4 py-3 text-sm font-medium shadow-lg sm:inset-x-auto sm:right-5 sm:mx-0"
        role="status"
        aria-live="polite"
        x-text="toast"></div>
</div>
@endsection
