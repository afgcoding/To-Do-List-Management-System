@extends('layouts.app')

@section('content')
@php
    $query = request()->except('layout');
    $listUrl = route('tasks.index', array_merge($query, ['layout' => 'list']));
    $gridUrl = route('tasks.index', array_merge($query, ['layout' => 'grid']));
    $assigneeHeading = $tasks->getCollection()->contains(
        fn ($task): bool => $task->assignedUsers->count() > 1,
    ) ? 'Team' : 'Assignee';
@endphp
<div class="space-y-6">
    {{-- ==================== PAGE HEADER ==================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tasks workspace</h1>
            <p class="text-sm text-slate-500">Search, filter, and track every assignment from one place.</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create task
        </a>
    </div>

    {{-- ==================== STATS COUNTERS ==================== --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-tasks.stat-card label="Total tasks" :value="$stats['total']" tone="indigo" :href="route('tasks.index')" />
        <x-tasks.stat-card label="In progress" :value="$stats['in_progress']" tone="sky" :href="route('tasks.index', ['status' => 'in_progress'])" />
        <x-tasks.stat-card label="Completed" :value="$stats['completed']" tone="emerald" :href="route('tasks.index', ['status' => 'completed'])" />
        <x-tasks.stat-card label="Overdue" :value="$stats['overdue']" tone="rose" :href="route('tasks.index', ['status' => 'overdue'])" />
    </div>

    {{-- ==================== QUICK STATUS TABS ==================== --}}
    <div class="flex gap-4 border-b border-slate-200">
        <a href="{{ route('tasks.index') }}" @class(['pb-2 text-sm font-semibold', 'border-b-2 border-indigo-600 text-indigo-600' => ! request('status'), 'text-slate-500 hover:text-slate-700' => request('status')])>All tasks</a>
        <a href="{{ route('tasks.index', ['status' => 'overdue']) }}" @class(['pb-2 text-sm font-semibold', 'border-b-2 border-rose-600 text-rose-600' => request('status') === 'overdue', 'text-slate-500 hover:text-slate-700' => request('status') !== 'overdue'])>Overdue</a>
        <a href="{{ route('tasks.index', ['status' => 'completed']) }}" @class(['pb-2 text-sm font-semibold', 'border-b-2 border-emerald-600 text-emerald-600' => request('status') === 'completed', 'text-slate-500 hover:text-slate-700' => request('status') !== 'completed'])>Completed</a>
    </div>

    {{-- ==================== SEARCH, FILTERS & SORT ==================== --}}
    <form method="GET" action="{{ route('tasks.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="layout" value="{{ $layout }}">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or description..." class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 xl:col-span-2">
            <select name="status" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All statuses</option>
                @foreach(\App\Enums\TaskStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
                <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
            </select>
            <select name="priority" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All priorities</option>
                @foreach(\App\Enums\TaskPriority::cases() as $priority)
                    <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </select>
            <select name="assigned_user_id" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All assignees</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('assigned_user_id') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <select name="category_id" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
            <input type="date" name="due_from" value="{{ request('due_from') }}" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" aria-label="Due from">
            <input type="date" name="due_to" value="{{ request('due_to') }}" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" aria-label="Due to">
            <select name="sort_by" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>Sort by created</option>
                <option value="due_date" @selected(request('sort_by') === 'due_date')>Sort by due date</option>
                <option value="priority" @selected(request('sort_by') === 'priority')>Sort by priority</option>
                <option value="status" @selected(request('sort_by') === 'status')>Sort by status</option>
            </select>
            <select name="sort_order" class="rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="desc" @selected(request('sort_order', 'desc') === 'desc')>Newest / high first</option>
                <option value="asc" @selected(request('sort_order') === 'asc')>Oldest / low first</option>
            </select>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">Apply filters</button>
            <a href="{{ route('tasks.index', ['layout' => $layout]) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-center text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50">Reset</a>
        </div>
    </form>

    {{-- ==================== LIST / GRID TOGGLE ==================== --}}
    <div class="flex items-center justify-between">
        <p class="text-xs font-medium text-slate-500">{{ $tasks->total() }} matching tasks</p>
        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
            <a href="{{ $listUrl }}" @class(['rounded-md px-3 py-1.5 text-xs font-semibold', 'bg-indigo-600 text-white' => $layout === 'list', 'text-slate-500 hover:text-slate-700' => $layout !== 'list'])>List</a>
            <a href="{{ $gridUrl }}" @class(['rounded-md px-3 py-1.5 text-xs font-semibold', 'bg-indigo-600 text-white' => $layout === 'grid', 'text-slate-500 hover:text-slate-700' => $layout !== 'grid'])>Grid</a>
        </div>
    </div>

    {{-- ==================== TASK RESULTS (GRID OR TABLE) ==================== --}}
    @if($layout === 'grid')
        {{-- --- Grid cards --- --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($tasks as $task)
                @include('tasks.partials.card', ['task' => $task])
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-slate-200 bg-white py-16 text-center text-slate-500">No tasks match these filters.</div>
            @endforelse
        </div>
    @else
        {{-- --- List table --- --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase text-slate-500">
                        <th class="px-4 py-3">Task</th>
                        <th class="px-4 py-3">{{ $assigneeHeading }}</th>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Due</th>
                        <th class="px-4 py-3">Progress</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse($tasks as $task)
                        @include('tasks.partials.row', ['task' => $task])
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No tasks created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- --- Pagination --- --}}
    <div>{{ $tasks->links() }}</div>
</div>
@endsection
