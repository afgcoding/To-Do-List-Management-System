<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }} · {{ setting('company_name', config('app.name')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-white p-8 font-sans text-slate-800">
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ setting('company_name', config('app.name')) }}</p>
            <h1 class="text-2xl font-semibold">Task report</h1>
            <p class="mt-1 text-sm text-slate-500">Generated {{ $generatedAt }}</p>
        </div>
        <button type="button" onclick="window.print()" class="no-print rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">Print / Save PDF</button>
    </div>
    <table class="w-full border-collapse text-left text-sm">
        <thead>
            <tr class="border-b border-slate-300 text-[11px] uppercase tracking-wider text-slate-500">
                <th class="py-2">Task</th>
                <th class="py-2">Status</th>
                <th class="py-2">Priority</th>
                <th class="py-2">Due</th>
                <th class="py-2">Department</th>
                <th class="py-2">Assignees</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $task)
                <tr class="border-b border-slate-100">
                    <td class="py-2 pr-3">{{ $task->title }}</td>
                    <td class="py-2 pr-3">{{ $task->status->label() }}</td>
                    <td class="py-2 pr-3">{{ $task->priority->label() }}</td>
                    <td class="py-2 pr-3">{{ format_date($task->due_date) ?? '—' }}</td>
                    <td class="py-2 pr-3">{{ $task->department->name ?? '—' }}</td>
                    <td class="py-2">{{ $task->assignedUsers->pluck('name')->join(', ') ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <script>window.addEventListener('load', () => { if (new URLSearchParams(window.location.search).has('autoprint')) { window.print(); } });</script>
</body>
</html>
