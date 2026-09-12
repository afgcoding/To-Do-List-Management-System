{{-- --- List row: title, team, badges, due date, progress, actions --- --}}
<tr @class(['transition hover:bg-slate-50/70', 'bg-rose-50/40' => $task->is_overdue, 'bg-emerald-50/30' => $task->status === \App\Enums\TaskStatus::Completed])>
    <td class="px-4 py-4">
        <a href="{{ route('tasks.show', $task) }}" class="font-semibold text-slate-800 transition hover:text-indigo-600">{{ $task->title }}</a>
        <div class="mt-1 flex flex-wrap items-center gap-2">
            @if($task->category)
                <x-color-pill :label="$task->category->name" :color="$task->category->color" />
            @endif
            @foreach($task->tags as $tag)
                <x-tasks.tag-badge :name="$tag->name" />
            @endforeach
            @if($task->department)
                <span class="inline-flex items-center rounded-md border border-indigo-100 bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{{ $task->department->name }}</span>
            @endif
            @if($task->is_overdue)
                <x-badge tone="rose">Overdue</x-badge>
            @endif
        </div>
    </td>
    <td class="px-4 py-4">
        <x-tasks.assignee-stack :users="$task->assignedUsers" />
    </td>
    <td class="px-4 py-4"><x-tasks.priority-badge :priority="$task->priority" /></td>
    <td class="px-4 py-4"><x-tasks.status-badge :status="$task->status" /></td>
    <td class="px-4 py-4 text-xs text-slate-600">
        {{ format_date($task->due_date) ?? 'No due date' }}
    </td>
    <td class="px-4 py-4 w-40">
        <x-tasks.progress :percent="$task->progress" label="" />
    </td>
    <td class="px-4 py-4 text-right">
        <div class="inline-flex items-center gap-3">
            <a href="{{ route('tasks.show', $task) }}" class="text-xs font-medium text-slate-600 hover:text-indigo-600">View</a>
            @can('update', $task)
            <a href="{{ route('tasks.edit', $task) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
            @endcan
            @can('delete', $task)
            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-800">Delete</button>
            </form>
            @endcan
        </div>
    </td>
</tr>
