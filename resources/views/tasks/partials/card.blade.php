{{-- --- Task card: summary, badges, progress, assignees, actions --- --}}
@php
    $action = 'inline-flex h-9 w-full items-center justify-center rounded-lg border px-2 text-xs font-semibold transition';
@endphp
<article @class(['flex h-full min-h-[17.5rem] w-full flex-col rounded-xl border bg-white p-4 shadow-sm transition hover:shadow-md', 'border-rose-200' => $task->is_overdue, 'border-emerald-200' => $task->status === \App\Enums\TaskStatus::Completed && ! $task->is_overdue, 'border-slate-200' => ! $task->is_overdue && $task->status !== \App\Enums\TaskStatus::Completed])>
    <div class="flex items-start justify-between gap-2">
        <a href="{{ route('tasks.show', $task) }}" class="line-clamp-2 min-w-0 flex-1 break-words text-xs font-medium leading-snug text-indigo-600 hover:text-indigo-700 sm:text-sm sm:font-semibold">{{ $task->title }}</a>
        <x-tasks.priority-badge class="shrink-0" :priority="$task->priority" />
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
    <div class="mt-auto flex items-center justify-between gap-2 pt-4">
        <div class="flex min-w-0 items-center gap-2">
            <x-tasks.assignee-stack class="shrink-0" :users="$task->assignedUsers" />
            @if ($task->assignedUsers->isNotEmpty())
                <p class="min-w-0 truncate text-[11px] font-medium leading-tight text-slate-600 sm:text-xs">
                    {{ $task->assignedUsers->first()->name }}
                    @if ($task->assignedUsers->count() > 1)
                        <span class="text-slate-400">+{{ $task->assignedUsers->count() - 1 }}</span>
                    @endif
                </p>
            @endif
        </div>
        <span class="shrink-0 text-[11px] font-medium text-slate-500 sm:text-xs">{{ format_date($task->due_date) ?? 'No due date' }}</span>
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
