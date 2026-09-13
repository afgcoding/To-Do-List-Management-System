{{-- --- List row: title, team, badges, due date, progress, actions --- --}}
<tr @class(['transition hover:bg-slate-50/80', 'bg-rose-50/40' => $task->is_overdue, 'bg-emerald-50/20' => $task->status === \App\Enums\TaskStatus::Completed && ! $task->is_overdue])>
    <td class="min-w-[220px] max-w-[280px] px-6 py-4 align-middle">
        <a href="{{ route('tasks.show', $task) }}" class="line-clamp-1 font-medium text-slate-900 transition hover:text-indigo-600">{{ $task->title }}</a>
        <div class="mt-1 flex items-center gap-1.5 overflow-hidden whitespace-nowrap">
            @if($task->department)
                <span class="inline-flex shrink-0 items-center rounded-md border border-indigo-100 bg-indigo-50 px-1.5 py-0.5 text-[11px] font-medium text-indigo-700">{{ $task->department->name }}</span>
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
    <td class="min-w-[120px] whitespace-nowrap px-6 py-4 align-middle">
        <x-tasks.assignee-stack :users="$task->assignedUsers" />
    </td>
    <td class="min-w-[110px] whitespace-nowrap px-6 py-4 align-middle"><x-tasks.priority-badge :priority="$task->priority" /></td>
    <td class="min-w-[120px] whitespace-nowrap px-6 py-4 align-middle"><x-tasks.status-badge :status="$task->status" /></td>
    <td class="min-w-[120px] whitespace-nowrap px-6 py-4 align-middle text-xs font-medium text-slate-600">
        {{ format_date($task->due_date) ?? 'No due date' }}
    </td>
    <td class="min-w-[160px] px-6 py-4 align-middle">
        <x-tasks.progress :percent="$task->progress" label="" />
    </td>
    <td class="min-w-[72px] px-6 py-4 text-right align-middle">
        <div class="relative inline-flex" x-data="{ open: false }">
            <button type="button" @click="open = ! open" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="Task actions">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Zm0 6a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Zm0 6a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z"/></svg>
            </button>
            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                class="absolute right-0 z-20 mt-1 w-36 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-lg">
                <a href="{{ route('tasks.show', $task) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                    <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    View
                </a>
                @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                    <svg class="size-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L8.25 18.002H5.25v-3L16.862 4.487z"/></svg>
                    Edit
                </a>
                @endcan
                @can('delete', $task)
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </td>
</tr>
