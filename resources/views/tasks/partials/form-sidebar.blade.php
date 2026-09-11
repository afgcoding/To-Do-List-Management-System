{{-- Create / edit sidebar: priority, status, org fields, assignees --}}
@php
    $task = $task ?? null;
    $assignedUserIds = old('assigned_users', $assignedUserIds ?? []);
    $selectedTagIds = old('tags', $selectedTagIds ?? []);
    $priorityValue = old('priority', $task?->priority?->value ?? 'medium');
    $statusValue = old('status', $task?->status?->value ?? 'todo');
@endphp

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Priority</label>
    <select name="priority" class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach(\App\Enums\TaskPriority::cases() as $priority)
            <option value="{{ $priority->value }}" @selected($priorityValue === $priority->value)>{{ $priority->label() }}</option>
        @endforeach
    </select>
</div>

<div>
    {{-- Completed cannot be chosen here; subtasks drive that status --}}
    <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
    <select name="status" class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach(\App\Enums\TaskStatus::manualCases() as $status)
            <option value="{{ $status->value }}" @selected($statusValue === $status->value)>{{ $status->label() }}</option>
        @endforeach
        @if($statusValue === 'completed')
            <option value="" disabled selected>Completed — Automated by Subtasks</option>
        @endif
    </select>
    <p class="mt-1 text-[11px] text-slate-400">Completed is automated by subtask progress.</p>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Department</label>
    <select name="department_id" class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Select department</option>
        @foreach($departments as $department)
            <option value="{{ $department->id }}" @selected((string) old('department_id', $task?->department_id) === (string) $department->id)>{{ $department->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Category</label>
    <select name="category_id" class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Select category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $task?->category_id) === (string) $category->id)>{{ $category->name }}</option>
        @endforeach
    </select>
</div>

@if(isset($tags) && $tags->isNotEmpty())
    <div>
        <label class="mb-2 block text-sm font-medium text-slate-700">Tags</label>
        <div class="max-h-28 space-y-1.5 overflow-y-auto rounded-lg border border-slate-200 p-2.5">
            @foreach($tags as $tag)
                <label class="flex cursor-pointer items-center gap-2 text-xs font-medium text-slate-700">
                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTagIds, false))
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    {{ $tag->name }}
                    <span class="size-2.5 rounded-full border border-slate-200" style="background-color: {{ $tag->color ?: '#94A3B8' }}"></span>
                </label>
            @endforeach
        </div>
    </div>
@endif

<div>
    <label class="mb-2 block text-sm font-medium text-slate-700">Assign / reassign team</label>
    <div class="max-h-36 space-y-1.5 overflow-y-auto rounded-lg border border-slate-200 p-2.5">
        @forelse($users as $user)
            <label class="flex cursor-pointer items-center gap-2 text-xs font-medium text-slate-700">
                <input type="checkbox" name="assigned_users[]" value="{{ $user->id }}" @checked(in_array($user->id, $assignedUserIds, false))
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                {{ $user->name }}
            </label>
        @empty
            <p class="text-xs text-slate-400">No active users available.</p>
        @endforelse
    </div>
</div>
