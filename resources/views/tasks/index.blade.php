@extends('layouts.app')

@section('content')
@php
    $query = request()->except('layout');
    $listUrl = route('tasks.index', array_merge($query, ['layout' => 'list']));
    $gridUrl = route('tasks.index', array_merge($query, ['layout' => 'grid']));
    $assigneeHeading = $tasks->getCollection()->contains(
        fn ($task): bool => $task->assignedUsers->count() > 1,
    ) ? 'Team' : 'Assignee';
    $control = 'h-9 w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 shadow-sm transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm';
    $secondaryCount = collect(['due_from', 'due_to'])
        ->filter(fn (string $key): bool => filled(request($key)))
        ->count();
    if (filled(request('sort_by')) && request('sort_by') !== 'created_at') {
        $secondaryCount++;
    }
    if (filled(request('sort_order')) && request('sort_order') !== 'desc') {
        $secondaryCount++;
    }
@endphp
<div class="space-y-6">
    {{-- ==================== PAGE HEADER ==================== --}}
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Tasks workspace</h1>
            <p class="mt-1 text-sm text-slate-500">Search, filter, and track every assignment from one place.</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 md:w-auto">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create task
        </a>
    </div>

    {{-- ==================== STATS COUNTERS ==================== --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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

    {{-- ==================== SEARCH, FILTERS & SORT ==================== --}}
    <form
        method="GET"
        action="{{ route('tasks.index') }}"
        class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-sm"
        x-data="{ showFilters: {{ $secondaryCount > 0 ? 'true' : 'false' }} }"
    >
        <input type="hidden" name="layout" value="{{ $layout }}">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <label class="relative min-w-[12rem] flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
                </span>
                <span class="sr-only">Search tasks</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..."
                    class="{{ $control }} pl-9">
            </label>
            <select name="status" class="{{ $control }} min-w-[8.5rem] flex-1 sm:max-w-[11rem] sm:flex-none">
                <option value="">All statuses</option>
                @foreach(\App\Enums\TaskStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
                <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
            </select>
            <select name="priority" class="{{ $control }} min-w-[8.5rem] flex-1 sm:max-w-[11rem] sm:flex-none">
                <option value="">All priorities</option>
                @foreach(\App\Enums\TaskPriority::cases() as $priority)
                    <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </select>
            <select name="assigned_user_id" class="{{ $control }} min-w-[8.5rem] flex-1 sm:max-w-[12rem] sm:flex-none">
                <option value="">All assignees</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('assigned_user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <select name="category_id" class="{{ $control }} min-w-[8.5rem] flex-1 sm:max-w-[12rem] sm:flex-none">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <div class="ml-auto flex items-center gap-2">
                <button
                    type="button"
                    @click="showFilters = ! showFilters"
                    class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50 sm:text-sm"
                >
                    <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6Zm0 6h.008v.008H3.75V12Zm0 6h.008v.008H3.75V18Z"/></svg>
                    Filter
                    @if ($secondaryCount > 0)
                        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1.5 text-[11px] font-semibold leading-5 text-white">{{ $secondaryCount }}</span>
                    @endif
                </button>
                <button type="submit" class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg bg-indigo-600 px-4 text-xs font-medium text-white transition-colors hover:bg-indigo-700 sm:text-sm">Apply</button>
                <a href="{{ route('tasks.index', ['layout' => $layout]) }}" class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 px-4 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-200 sm:text-sm">Reset</a>
            </div>
        </div>

        <div x-show="showFilters" x-cloak class="mt-2 grid grid-cols-2 gap-2 border-t border-slate-100 pt-2 sm:grid-cols-4">
            <input type="date" name="due_from" value="{{ request('due_from') }}" class="{{ $control }}" aria-label="Start date">
            <input type="date" name="due_to" value="{{ request('due_to') }}" class="{{ $control }}" aria-label="End date">
            <select name="sort_by" class="{{ $control }}">
                <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>Sort by created</option>
                <option value="due_date" @selected(request('sort_by') === 'due_date')>Sort by due date</option>
                <option value="priority" @selected(request('sort_by') === 'priority')>Sort by priority</option>
                <option value="status" @selected(request('sort_by') === 'status')>Sort by status</option>
            </select>
            <select name="sort_order" class="{{ $control }}">
                <option value="desc" @selected(request('sort_order', 'desc') === 'desc')>Newest / high first</option>
                <option value="asc" @selected(request('sort_order') === 'asc')>Oldest / low first</option>
            </select>
        </div>
    </form>

    {{-- ==================== LIST / GRID TOGGLE ==================== --}}
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-xs font-medium text-slate-500">{{ $tasks->total() }} matching tasks</p>
        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
            <a href="{{ $listUrl }}" @class(['rounded-md px-3 py-1.5 text-xs font-semibold', 'bg-indigo-600 text-white' => $layout === 'list', 'text-slate-500 hover:text-slate-700' => $layout !== 'list'])>List</a>
            <a href="{{ $gridUrl }}" @class(['rounded-md px-3 py-1.5 text-xs font-semibold', 'bg-indigo-600 text-white' => $layout === 'grid', 'text-slate-500 hover:text-slate-700' => $layout !== 'grid'])>Grid</a>
        </div>
    </div>

    {{-- ==================== TASK RESULTS (GRID OR TABLE) ==================== --}}
    @if($layout === 'grid')
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($tasks as $task)
                @include('tasks.partials.card', ['task' => $task])
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-slate-200 bg-white py-16 text-center text-slate-500">No tasks match these filters.</div>
            @endforelse
        </div>
    @else
        <div class="block space-y-3 md:hidden">
            @forelse($tasks as $task)
                @include('tasks.partials.card', ['task' => $task])
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 bg-white py-16 text-center text-slate-500">No tasks created yet.</div>
            @endforelse
        </div>
        <div class="hidden w-full overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm md:block">
                <table class="w-full min-w-[960px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <th class="min-w-[220px] px-6 py-3">Task</th>
                            <th class="min-w-[120px] px-6 py-3">{{ $assigneeHeading }}</th>
                            <th class="min-w-[110px] px-6 py-3">Priority</th>
                            <th class="min-w-[120px] px-6 py-3">Status</th>
                            <th class="min-w-[120px] px-6 py-3">Due</th>
                            <th class="min-w-[160px] px-6 py-3">Progress</th>
                            <th class="min-w-[72px] px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($tasks as $task)
                            @include('tasks.partials.row', ['task' => $task])
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-500">No tasks created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
        </div>
    @endif

    <div class="overflow-x-auto">{{ $tasks->links() }}</div>
</div>
@endsection
