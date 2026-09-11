{{-- --- Grid card: summary, badges, progress, assignees --- --}}
<article @class(['flex h-full flex-col rounded-xl border bg-white p-5 shadow-sm transition hover:shadow-md', 'border-rose-200' => $task->is_overdue, 'border-emerald-200' => $task->status === \App\Enums\TaskStatus::Completed && ! $task->is_overdue, 'border-slate-200' => ! $task->is_overdue && $task->status !== \App\Enums\TaskStatus::Completed])>
    <div class="flex items-start justify-between gap-3">
        <a href="{{ route('tasks.show', $task) }}" class="font-semibold text-slate-800 hover:text-indigo-600">{{ $task->title }}</a>
        <x-tasks.priority-badge :priority="$task->priority" />
    </div>
    <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ $task->description ?: 'No description' }}</p>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-tasks.status-badge :status="$task->status" />
        @if($task->is_overdue)
            <x-badge tone="rose">Overdue</x-badge>
        @endif
        @if($task->category)
            <x-color-pill :label="$task->category->name" :color="$task->category->color" />
        @endif
        @foreach($task->tags as $tag)
            <x-color-pill :label="$tag->name" :color="$tag->color" />
        @endforeach
    </div>
    <div class="mt-4">
        <x-tasks.progress :percent="$task->progress" />
    </div>
    <div class="mt-auto flex items-center justify-between pt-4">
        <div class="flex -space-x-2">
            @forelse($task->assignedUsers as $user)
                <x-user-avatar :name="$user->name" />
            @empty
                <span class="text-xs italic text-slate-400">Unassigned</span>
            @endforelse
        </div>
        <span class="text-xs text-slate-500">{{ format_date($task->due_date) ?? 'No due date' }}</span>
    </div>
</article>
