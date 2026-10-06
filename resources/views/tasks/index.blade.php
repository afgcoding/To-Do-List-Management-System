@extends('layouts.app')

@php
    $pageTitle = 'Tasks Workspace';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
@php
    $query = request()->except('layout');
    $listUrl = route('tasks.index', array_merge($query, ['layout' => 'list']));
    $gridUrl = route('tasks.index', array_merge($query, ['layout' => 'grid']));
    $assigneeHeading = $tasks->getCollection()->contains(
        fn ($task): bool => $task->assignedUsers->count() > 1,
    ) ? 'Team' : 'Assignee';
    $searchControl = 'h-9 w-64 rounded-lg border border-gray-300 bg-gray-50 py-1.5 pr-3 pl-9 text-sm text-gray-700 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500';
    $selectControl = 'h-9 w-full rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
    $activeFilterCount = collect(['status', 'priority', 'assigned_user_id', 'category_id', 'due_from', 'due_to'])
        ->filter(fn (string $key): bool => filled(request($key)))
        ->count();
    if (filled(request('sort_by')) && request('sort_by') !== 'created_at') {
        $activeFilterCount++;
    }
    if (filled(request('sort_order')) && request('sort_order') !== 'desc') {
        $activeFilterCount++;
    }
@endphp
<div class="space-y-5">
    {{-- ==================== PAGE HEADER ==================== --}}
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Tasks Workspace</h1>
            <p class="mt-1 text-sm text-gray-500">Search, filter, and track every assignment from one place.</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="inline-flex h-10 w-full shrink-0 items-center justify-center gap-2 rounded-full px-4 text-sm font-semibold text-white shadow-sm transition md:w-auto" style="background-color: var(--brand)">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create task
        </a>
    </div>

    {{-- ==================== STATS COUNTERS ==================== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-tasks.stat-card label="Total tasks" :value="$stats['total']" tone="indigo" :href="route('tasks.index')" />
        <x-tasks.stat-card label="In progress" :value="$stats['in_progress']" tone="sky" :href="route('tasks.index', ['status' => 'in_progress'])" />
        <x-tasks.stat-card label="Completed" :value="$stats['completed']" tone="emerald" :href="route('tasks.index', ['status' => 'completed'])" />
        <x-tasks.stat-card label="Overdue" :value="$stats['overdue']" tone="rose" :href="route('tasks.index', ['status' => 'overdue'])" />
    </div>

    {{-- ==================== QUICK STATUS TABS ==================== --}}
    <div class="flex gap-5 overflow-x-auto border-b border-slate-200">
        <a href="{{ route('tasks.index') }}" @class(['shrink-0 pb-2.5 text-sm font-medium', 'border-b-2 border-indigo-600 text-indigo-600' => ! request('status'), 'text-slate-500 hover:text-slate-700' => request('status')])>All tasks</a>
        <a href="{{ route('tasks.index', ['status' => 'overdue']) }}" @class(['shrink-0 pb-2.5 text-sm font-medium', 'border-b-2 border-rose-600 text-rose-600' => request('status') === 'overdue', 'text-slate-500 hover:text-slate-700' => request('status') !== 'overdue'])>Overdue</a>
        <a href="{{ route('tasks.index', ['status' => 'completed']) }}" @class(['shrink-0 pb-2.5 text-sm font-medium', 'border-b-2 border-emerald-600 text-emerald-600' => request('status') === 'completed', 'text-slate-500 hover:text-slate-700' => request('status') !== 'completed'])>Completed</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <form
            method="GET"
            action="{{ route('tasks.index') }}"
            class="flex items-center justify-end gap-3 border-b border-gray-200 bg-white p-4"
        >
            <input type="hidden" name="layout" value="{{ $layout }}">
            <label class="relative shrink-0">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
                </span>
                <span class="sr-only">Search tasks</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..."
                    class="{{ $searchControl }}">
            </label>

            <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                <button
                    type="button"
                    @click="open = ! open"
                    class="flex h-9 items-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-3 text-xs font-medium text-gray-700 hover:bg-gray-100"
                >
                    <svg class="size-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 4.5h18l-6.75 7.89v4.86L9.75 19.5v-7.11L3 4.5Z"/></svg>
                    Filters
                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $activeFilterCount }}</span>
                </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition
                        class="absolute right-0 z-30 mt-2 w-[22rem] max-w-[calc(100vw-2rem)] rounded-xl border border-gray-200 bg-white p-4 shadow-lg"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-900">Filters</p>
                            <a href="{{ route('tasks.index', ['layout' => $layout]) }}" class="text-xs font-medium text-gray-500 hover:text-gray-700">Reset</a>
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <label class="block">
                                <span class="mb-1 block text-xs font-medium text-gray-500">Status</span>
                                <select name="status" class="{{ $selectControl }}">
                                    <option value="">All statuses</option>
                                    @foreach(\App\Enums\TaskStatus::cases() as $status)
                                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                                    @endforeach
                                    <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-medium text-gray-500">Priority</span>
                                <select name="priority" class="{{ $selectControl }}">
                                    <option value="">All priorities</option>
                                    @foreach(\App\Enums\TaskPriority::cases() as $priority)
                                        <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-medium text-gray-500">Assignee</span>
                                <select name="assigned_user_id" class="{{ $selectControl }}">
                                    <option value="">All assignees</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected((string) request('assigned_user_id') === (string) $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-medium text-gray-500">Category</span>
                                <select name="category_id" class="{{ $selectControl }}">
                                    <option value="">All categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="block">
                                    <span class="mb-1 block text-xs font-medium text-gray-500">Due from</span>
                                    <input type="date" name="due_from" value="{{ request('due_from') }}" class="{{ $selectControl }}">
                                </label>
                                <label class="block">
                                    <span class="mb-1 block text-xs font-medium text-gray-500">Due to</span>
                                    <input type="date" name="due_to" value="{{ request('due_to') }}" class="{{ $selectControl }}">
                                </label>
                            </div>
                            <label class="block">
                                <span class="mb-1 block text-xs font-medium text-gray-500">Sort by</span>
                                <select name="sort_by" class="{{ $selectControl }}">
                                    <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>Created</option>
                                    <option value="due_date" @selected(request('sort_by') === 'due_date')>Due date</option>
                                    <option value="priority" @selected(request('sort_by') === 'priority')>Priority</option>
                                    <option value="status" @selected(request('sort_by') === 'status')>Status</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs font-medium text-gray-500">Order</span>
                                <select name="sort_order" class="{{ $selectControl }}">
                                    <option value="desc" @selected(request('sort_order', 'desc') === 'desc')>Newest / high first</option>
                                    <option value="asc" @selected(request('sort_order') === 'asc')>Oldest / low first</option>
                                </select>
                            </label>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <a href="{{ route('tasks.index', ['layout' => $layout]) }}" class="px-2 py-1.5 text-xs font-medium text-gray-500 hover:text-gray-700">Reset</a>
                            <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white transition-colors hover:bg-indigo-700">Apply filters</button>
                        </div>
                    </div>
            </div>

            <div class="inline-flex h-9 shrink-0 items-center rounded-lg border border-gray-200 bg-gray-100 p-0.5">
                <a href="{{ $listUrl }}" @class(['inline-flex h-full items-center rounded-md px-3 text-xs font-semibold', 'bg-white text-gray-900 shadow-xs' => $layout === 'list', 'text-gray-500 hover:text-gray-700' => $layout !== 'list'])>List</a>
                <a href="{{ $gridUrl }}" @class(['inline-flex h-full items-center rounded-md px-3 text-xs font-semibold', 'bg-white text-gray-900 shadow-xs' => $layout === 'grid', 'text-gray-500 hover:text-gray-700' => $layout !== 'grid'])>Grid</a>
            </div>
        </form>

        @if($layout === 'grid')
            <div class="grid grid-cols-1 items-stretch gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($tasks as $task)
                    @include('tasks.partials.card', ['task' => $task])
                @empty
                    <div class="col-span-full py-16 text-center text-gray-500">No tasks match these filters.</div>
                @endforelse
            </div>
        @else
            <div class="grid grid-cols-1 items-stretch gap-4 p-4 sm:grid-cols-2 md:hidden">
                @forelse($tasks as $task)
                    @include('tasks.partials.card', ['task' => $task])
                @empty
                    <div class="py-16 text-center text-gray-500">No tasks created yet.</div>
                @endforelse
            </div>
            <div class="hidden w-full overflow-x-auto md:block">
                <table class="w-full min-w-[960px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                            <th class="min-w-[220px] px-4 py-2.5">Task</th>
                            <th class="min-w-[120px] px-4 py-2.5">{{ $assigneeHeading }}</th>
                            <th class="min-w-[110px] px-4 py-2.5">Priority</th>
                            <th class="min-w-[120px] px-4 py-2.5">Status</th>
                            <th class="min-w-[120px] px-4 py-2.5">Due</th>
                            <th class="min-w-[160px] px-4 py-2.5">Progress</th>
                            <th class="min-w-[72px] px-4 py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($tasks as $task)
                            @include('tasks.partials.row', ['task' => $task])
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500">No tasks created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <div class="flex flex-col gap-3 border-t border-gray-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-medium leading-9 text-gray-600">
                Showing {{ $tasks->firstItem() ?? 0 }} to {{ $tasks->lastItem() ?? 0 }} of {{ $tasks->total() }} results
            </p>
            <div class="flex items-center">{{ $tasks->links('pagination.task-table') }}</div>
        </div>
    </div>
</div>
@endsection
