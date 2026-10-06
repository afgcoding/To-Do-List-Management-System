{{-- --- Task card: summary, badges, progress, assignees, actions --- --}}
@php
    $action = 'inline-flex h-9 w-full items-center justify-center rounded-lg border px-2 text-xs font-semibold transition';
@endphp
<article @class(['flex h-full min-h-[17.5rem] w-full flex-col rounded-xl border bg-white p-4 shadow-sm transition hover:shadow-md', 'border-rose-200' => $task->is_overdue, 'border-emerald-200' => $task->status === \App\Enums\TaskStatus::Completed && ! $task->is_overdue, 'border-slate-200' => ! $task->is_overdue && $task->status !== \App\Enums\TaskStatus::Completed])>
    <div class="flex items-start justify-between gap-3">
        <a href="{{ route('tasks.show', $task) }}" class="line-clamp-2 min-h-10 min-w-0 text-sm font-semibold text-indigo-600 hover:text-indigo-700">{{ $task->title }}</a>
        <x-tasks.priority-badge :priority="$task->priority" />
    </div>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-tasks.status-badge :status="$task->status" />
        @if($task->is_overdue)
            <x-badge tone="rose">Overdue</x-badge>
        @endif
    </div>
    <div class="mt-4">
        <x-tasks.progress :percent="$task->progress" />
    </div>
    <div class="mt-auto flex items-center justify-between gap-3 pt-4">
        <x-tasks.assignee-stack :users="$task->assignedUsers" />
        <span class="shrink-0 text-xs font-medium text-slate-500">{{ format_date($task->due_date) ?? 'No due date' }}</span>
    </div>
    <div class="mt-4 flex items-stretch gap-2 border-t border-slate-100 pt-4">
        <a href="{{ route('tasks.show', $task) }}" class="{{ $action }} flex-1 border-slate-200 bg-white text-slate-600 hover:bg-slate-50">View</a>
        @can('update', $task)
            <a href="{{ route('tasks.edit', $task) }}" class="{{ $action }} flex-1 border-slate-200 bg-white text-slate-600 hover:bg-slate-50">Edit</a>
        @endcan
        @can('delete', $task)
            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');" class="min-w-0 flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="{{ $action }} border-rose-200 bg-white text-rose-500 hover:bg-rose-50">Delete</button>
            </form>
        @endcan
    </div>
</article>
