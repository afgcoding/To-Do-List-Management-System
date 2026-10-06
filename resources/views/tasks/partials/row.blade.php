{{-- --- List row: title, team, badges, due date, progress, actions --- --}}
<tr @class(['transition hover:bg-slate-50/80', 'bg-rose-50/40' => $task->is_overdue, 'bg-emerald-50/20' => $task->status === \App\Enums\TaskStatus::Completed && ! $task->is_overdue]) style="overflow: visible;">
    <td class="min-w-[180px] max-w-[240px] px-2 py-2.5 align-middle text-xs sm:min-w-[220px] sm:max-w-[280px] sm:px-3 sm:py-3 sm:text-sm">
        <a href="{{ route('tasks.show', $task) }}" class="line-clamp-1 text-xs font-medium text-indigo-600 transition hover:text-indigo-800 sm:text-sm">{{ $task->title }}</a>
        <div class="mt-1 flex flex-wrap items-center gap-1">
            @if($task->department)
                <span class="inline-flex max-w-[7rem] shrink-0 truncate items-center rounded-md border border-indigo-100 bg-indigo-50 px-1.5 py-0.5 text-[11px] font-medium text-indigo-700">{{ $task->department->name }}</span>
            @endif
            @if($task->category)
                <x-color-pill class="max-w-[7rem] shrink-0 truncate" :label="$task->category->name" :color="$task->category->color" />
            @endif
            @foreach($task->tags->take(2) as $tag)
                <x-tasks.tag-badge class="max-w-[6.5rem] shrink-0 truncate" :name="$tag->name" />
            @endforeach
            @if ($task->tags->count() > 2)
                <span class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-500">+{{ $task->tags->count() - 2 }} more</span>
            @endif
            @if($task->is_overdue)
                <x-badge class="shrink-0" tone="rose">Overdue</x-badge>
            @endif
        </div>
    </td>
    <td class="min-w-[88px] whitespace-nowrap px-2 py-2.5 align-middle text-xs sm:min-w-[120px] sm:px-3 sm:py-3 sm:text-sm">
        <x-tasks.assignee-stack :users="$task->assignedUsers" />
    </td>
    <td class="min-w-[72px] whitespace-nowrap px-2 py-2.5 align-middle text-xs sm:min-w-[110px] sm:px-3 sm:py-3 sm:text-sm"><x-tasks.priority-badge :priority="$task->priority" /></td>
    <td class="min-w-[88px] whitespace-nowrap px-2 py-2.5 align-middle text-xs sm:min-w-[120px] sm:px-3 sm:py-3 sm:text-sm"><x-tasks.status-badge :status="$task->status" /></td>
    <td class="min-w-[96px] whitespace-nowrap px-2 py-2.5 align-middle text-xs font-medium text-slate-600 sm:min-w-[120px] sm:px-3 sm:py-3 sm:text-sm">
        {{ format_date($task->due_date) ?? 'No due date' }}
    </td>
    <td class="min-w-[120px] whitespace-nowrap px-2 py-2.5 align-middle text-xs sm:min-w-[140px] sm:px-3 sm:py-3 sm:text-sm">
        <x-tasks.progress :percent="$task->progress" label="" />
    </td>
    <td
        class="relative z-10 w-12 overflow-visible whitespace-nowrap bg-white px-1 py-2 text-right align-middle sm:px-2"
        x-data="{ open: false }"
        :class="open ? 'z-50' : 'z-10'"
    >
        <div class="relative inline-block text-left">
            <button type="button" @click="open = ! open" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Task actions">
                <i class="fas fa-ellipsis-v text-sm"></i>
            </button>
            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                class="absolute right-0 z-50 mt-2 w-36 rounded-xl bg-white py-1 shadow-lg ring-1 ring-black/5"
            >
                <a href="{{ route('tasks.show', $task) }}" class="hover:bg-slate-50" style="display: flex; flex-wrap: nowrap; align-items: center; gap: 0.5rem; width: 100%; padding: 0.45rem 0.75rem; font-size: 0.8125rem; color: #334155; white-space: nowrap; text-decoration: none;">
                    <i class="fas fa-eye" style="width: 1rem; min-width: 1rem; text-align: center; color: #94a3b8;"></i>
                    <span style="white-space: nowrap;">View</span>
                </a>
                @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="hover:bg-slate-50" style="display: flex; flex-wrap: nowrap; align-items: center; gap: 0.5rem; width: 100%; padding: 0.45rem 0.75rem; font-size: 0.8125rem; color: #334155; white-space: nowrap; text-decoration: none;">
                    <i class="fas fa-pen" style="width: 1rem; min-width: 1rem; text-align: center; color: #94a3b8;"></i>
                    <span style="white-space: nowrap;">Edit</span>
                </a>
                @endcan
                @can('delete', $task)
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="hover:bg-rose-50" style="display: flex; flex-wrap: nowrap; align-items: center; gap: 0.5rem; width: 100%; padding: 0.45rem 0.75rem; font-size: 0.8125rem; color: #e11d48; white-space: nowrap; background: transparent; border: 0; cursor: pointer; text-align: left;">
                        <i class="fas fa-trash-alt" style="width: 1rem; min-width: 1rem; text-align: center;"></i>
                        <span style="white-space: nowrap;">Delete</span>
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </td>
</tr>
