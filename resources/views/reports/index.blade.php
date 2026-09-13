@extends('layouts.app')
@php $pageTitle = 'Reports'; @endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Reports</h1>
            <p class="mt-1 text-sm text-slate-500">Export visible tasks for leadership reviews. Up to 500 rows per download.</p>
        </div>
        @if ($canExport)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.print') }}" target="_blank" rel="noopener"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-900">Export PDF</a>
                <a href="{{ route('reports.csv') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">Export Excel</a>
            </div>
        @endif
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-100 bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-4 py-3">Task</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Priority</th>
                    <th class="px-4 py-3">Due</th>
                    <th class="px-4 py-3">Department</th>
                    <th class="px-4 py-3">Assignees</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tasks as $task)
                    <tr>
                        <td class="px-4 py-3">
                            <a href="{{ route('tasks.show', $task) }}" dir="auto" class="bidi-auto font-medium text-slate-800 hover:text-indigo-600">{{ $task->title }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $task->status->label() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $task->priority->label() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ format_date($task->due_date) ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $task->department->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $task->assignedUsers->pluck('name')->join(', ') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400">No tasks are available for this report.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
