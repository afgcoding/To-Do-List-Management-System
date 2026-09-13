{{-- --- Task card: summary, badges, progress, assignees, actions --- --}}
<article @class(['flex h-full flex-col rounded-xl border bg-white p-5 shadow-sm transition hover:shadow-md', 'border-rose-200' => $task->is_overdue, 'border-emerald-200' => $task->status === \App\Enums\TaskStatus::Completed && ! $task->is_overdue, 'border-slate-200' => ! $task->is_overdue && $task->status !== \App\Enums\TaskStatus::Completed])>
    <div class="flex items-start justify-between gap-3">
        <a href="{{ route('tasks.show', $task) }}" class="min-w-0 font-semibold text-slate-900 hover:text-indigo-600">{{ $task->title }}</a>
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
        <span class="text-xs font-medium text-slate-500">{{ format_date($task->due_date) ?? 'No due date' }}</span>
    </div>
    <div class="mt-4 flex gap-2 border-t border-slate-100 pt-4">
        <a href="{{ route('tasks.show', $task) }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">View</a>
        @can('update', $task)
        <a href="{{ route('tasks.edit', $task) }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-600 hover:bg-indigo-100">Edit</a>
        @endcan
        @can('delete', $task)
        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');" class="flex-1">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100">Delete</button>
        </form>
        @endcan
    </div>
</article>
