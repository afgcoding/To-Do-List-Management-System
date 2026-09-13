@extends('layouts.app')

@php
    $pageTitle = 'Dashboard';
    $trend = $kpis['activeTrend'];
    $statusSlices = collect($distribution)->reject(fn (array $item): bool => $item['key'] === 'overdue');
    $statusTotal = max(1, $statusSlices->sum('count'));
    $radius = 36;
    $circumference = 2 * 3.1416 * $radius;
    $dashOffset = 0;
@endphp

@section('content')
<div class="space-y-8">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Reporting hub</h1>
            <p class="mt-1 text-sm text-slate-500">Live workload, completion velocity, and upcoming deadlines for your workspace.</p>
        </div>
        <a href="{{ route('tasks.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 sm:w-auto">Open tasks</a>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Total Active Tasks</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $kpis['active'] }}</p>
            <p @class(['mt-2 inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold', 'bg-emerald-50 text-emerald-700' => $trend >= 0, 'bg-rose-50 text-rose-700' => $trend < 0])>
                {{ $trend >= 0 ? '+' : '' }}{{ $trend }}% from last week
            </p>
        </article>
        <article class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">{{ $canViewTeamAnalytics ? 'My Assigned Tasks' : 'Assigned to Me' }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $kpis['assigned'] }}</p>
            <p class="mt-2 text-[11px] text-slate-400">Open tasks assigned to you</p>
        </article>
        <article class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500">{{ $canViewTeamAnalytics ? 'Team Workload' : 'Pending Tasks' }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $kpis['pending'] }}</p>
            <p class="mt-2 text-[11px] text-slate-400">To Do and In Progress</p>
        </article>
        <article class="rounded-xl border border-rose-100 bg-rose-50 p-5 shadow-sm">
            <p class="text-xs font-medium text-rose-700">Critical Overdue</p>
            <p class="mt-2 text-3xl font-semibold tracking-tight text-rose-700">{{ $kpis['overdue'] }}</p>
            <p class="mt-2 text-[11px] text-rose-600">Past deadline and still open</p>
        </article>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-sm font-semibold text-slate-900">Task distribution by status</h2>
            <div class="mt-5 flex flex-col gap-6 sm:flex-row sm:items-center">
                <svg viewBox="0 0 96 96" class="mx-auto size-40 shrink-0" aria-hidden="true">
                    <circle cx="48" cy="48" r="{{ $radius }}" fill="none" stroke="#e2e8f0" stroke-width="12" />
                    @foreach ($statusSlices as $slice)
                        @php
                            $length = ($slice['count'] / $statusTotal) * $circumference;
                        @endphp
                        @if ($slice['count'] > 0)
                            <circle
                                cx="48" cy="48" r="{{ $radius }}" fill="none"
                                stroke="{{ $slice['color'] }}" stroke-width="12"
                                stroke-dasharray="{{ $length }} {{ $circumference }}"
                                stroke-dashoffset="{{ -$dashOffset }}"
                                stroke-linecap="butt"
                                transform="rotate(-90 48 48)"
                            />
                            @php $dashOffset += $length; @endphp
                        @endif
                    @endforeach
                    <text x="48" y="50" text-anchor="middle" class="fill-slate-800" font-size="14" font-weight="600">{{ $statusSlices->sum('count') }}</text>
                </svg>
                <div class="min-w-0 flex-1 space-y-3">
                    @php $barMax = max(1, collect($distribution)->max('count')); @endphp
                    @foreach ($distribution as $item)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-600">{{ $item['label'] }}</span>
                                <span class="font-semibold text-slate-800">{{ $item['count'] }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full" style="width: {{ (int) round(($item['count'] / $barMax) * 100) }}%; background: {{ $item['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm lg:col-span-1">
            <h2 class="text-sm font-semibold text-slate-900">Task completion velocity</h2>
            <p class="mt-1 text-xs text-slate-500">Completed tasks and subtasks versus remaining work.</p>
            @php
                $gauge = 2 * 3.1416 * 42;
                $filled = ($velocity / 100) * $gauge;
            @endphp
            <div class="mt-6 flex flex-col items-center">
                <svg viewBox="0 0 120 80" class="w-48" aria-hidden="true">
                    <path d="M18 70 A42 42 0 0 1 102 70" fill="none" stroke="#e2e8f0" stroke-width="12" stroke-linecap="round" />
                    <path d="M18 70 A42 42 0 0 1 102 70" fill="none" stroke="#4f46e5" stroke-width="12" stroke-linecap="round"
                        stroke-dasharray="{{ $filled }} {{ $gauge }}" />
                    <text x="60" y="62" text-anchor="middle" class="fill-slate-900" font-size="18" font-weight="700">{{ $velocity }}%</text>
                </svg>
                <p class="text-xs text-slate-500">Overall completion</p>
            </div>
        </section>
    </div>

    @if ($canViewTeamAnalytics)
        <section class="mb-8 rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900">Employee workload &amp; performance</h2>
            <div class="mt-4 space-y-4">
                @forelse ($workload as $member)
                    @php
                        $pending = (int) $member->pending_count;
                        $load = $pending >= 7 ? 'overloaded' : ($pending >= 4 ? 'high' : 'balanced');
                        $bar = min(100, (int) round(($pending / 8) * 100));
                        $barColor = $load === 'overloaded' ? 'bg-rose-500' : ($load === 'high' ? 'bg-amber-400' : 'bg-emerald-500');
                    @endphp
                    <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 last:border-0 last:pb-0 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <x-user-avatar :user="$member" size="md" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-800">{{ $member->name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $member->assigned_count }} assigned · {{ $member->completed_count }} completed · {{ $pending }} pending</p>
                            </div>
                        </div>
                        <div class="w-full sm:w-48">
                            <div class="mb-1 flex justify-between text-[10px] font-semibold uppercase tracking-wide">
                                <span @class(['text-emerald-700' => $load === 'balanced', 'text-amber-700' => $load === 'high', 'text-rose-700' => $load === 'overloaded'])>{{ $load }}</span>
                                <span class="text-slate-400">{{ $bar }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $bar }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No assigned team members yet.</p>
                @endforelse
            </div>
        </section>

        <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
            <section class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900">Department breakdown</h2>
                <div class="mt-4 space-y-2">
                    @forelse ($departments as $department)
                        <div class="flex items-center justify-between gap-3">
                            <span class="truncate text-sm text-slate-700">{{ $department->name }}</span>
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $department->tasks_count }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No department activity yet.</p>
                    @endforelse
                </div>
            </section>
            <section class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900">Category breakdown</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse ($categories as $category)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700">
                            {{ $category->name }}
                            <span class="rounded-full bg-white px-1.5 text-[10px] font-semibold text-slate-500">{{ $category->tasks_count }}</span>
                        </span>
                    @empty
                        <p class="text-sm text-slate-400">No category activity yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm lg:col-span-1">
            <h2 class="text-sm font-semibold text-slate-900">Upcoming deadlines</h2>
            <div class="mt-4 space-y-3">
                @forelse ($upcoming as $task)
                    @php
                        $dueLabel = $task->due_date?->isToday() ? 'Due Today' : 'Due Tomorrow';
                    @endphp
                    <a href="{{ route('tasks.show', $task) }}" class="block rounded-lg border border-slate-100 p-3 hover:border-indigo-100 hover:bg-slate-50">
                        <p dir="auto" class="bidi-auto truncate text-sm font-medium text-slate-800">{{ $task->title }}</p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                            <span class="text-[11px] font-medium text-slate-500">{{ $dueLabel }}</span>
                            <x-tasks.priority-badge :priority="$task->priority" />
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-slate-400">No tasks due in the next 48 hours.</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm lg:col-span-2">
            <h2 class="text-sm font-semibold text-slate-900">Recent activity</h2>
            <div class="custom-scrollbar mt-4 max-h-[350px] space-y-3 overflow-y-auto pr-1">
                @forelse ($activities as $log)
                    <div class="flex gap-3">
                        <x-user-avatar :user="$log->user" size="sm" />
                        <div class="min-w-0">
                            <p class="text-sm text-slate-700">
                                <span class="font-medium text-slate-900">{{ $log->user->name ?? 'System' }}</span>
                                {{ $log->description }}
                            </p>
                            @if ($log->task)
                                <a href="{{ route('tasks.show', $log->task) }}" dir="auto" class="bidi-auto mt-0.5 block truncate text-xs text-indigo-600 hover:text-indigo-800">{{ $log->task->title }}</a>
                            @endif
                            <p class="text-[11px] text-slate-400">{{ $log->created_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No recent updates.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
