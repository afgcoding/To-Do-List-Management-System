@extends('layouts.app')
@php
    $pageTitle = 'Activity Logs';
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Activity Logs</h1>
            <p class="mt-1 text-sm text-slate-500">Audit trail of task changes, comments, attachments, and assignments.</p>
        </div>
        <x-back-link :href="route('tasks.index')">Back to tasks</x-back-link>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3">Actor</th>
                    <th class="px-5 py-3">Activity</th>
                    <th class="px-5 py-3">Task</th>
                    <th class="px-5 py-3">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($activityLogs as $log)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <x-user-avatar :name="$log->user->name ?? 'System'" size="sm" />
                                <span dir="auto" class="bidi-auto font-medium text-slate-800">{{ $log->user->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p dir="auto" class="bidi-auto text-slate-700">{{ $log->description }}</p>
                            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ str_replace('_', ' ', $log->action) }}</p>
                        </td>
                        <td class="px-5 py-4">
                            @if ($log->task)
                                <a href="{{ route('tasks.show', $log->task) }}" dir="auto" class="bidi-auto font-medium text-indigo-600 hover:text-indigo-800">
                                    {{ $log->task->title }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-500">
                            @if ($log->created_at)
                                <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ format_date($log->created_at) }}">
                                    {{ $log->created_at->diffForHumans() }} · {{ format_date($log->created_at) }}
                                </time>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-400">No activity recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $activityLogs->links() }}</div>
</div>
@endsection
