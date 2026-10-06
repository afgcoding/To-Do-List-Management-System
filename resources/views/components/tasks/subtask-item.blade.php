@props(['subtask', 'assignees'])
@php
    $assigneeOptions = collect($assignees);
    if ($subtask->assignedUser && ! $assigneeOptions->contains('id', $subtask->assignedUser->id)) {
        $assigneeOptions = $assigneeOptions->push($subtask->assignedUser);
    }
@endphp
{{-- Subtask row: checkbox, title, assignee, status badge, hover actions --}}
<div x-data="{ editing: false }" class="group rounded-xl border border-slate-200/60 bg-slate-50/60 px-3 py-2.5 transition hover:border-gray-200 hover:bg-slate-100/80">
    {{-- --- View mode --- --}}
    <div x-show="!editing" class="flex items-center gap-3">
        <div class="flex min-w-0 flex-1 items-center gap-3">
            {{-- Toggle is_completed (true/false) --}}
            <form action="{{ route('subtasks.toggle', $subtask) }}" method="POST" class="flex shrink-0 items-center">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="flex size-5 items-center justify-center rounded-md border transition {{ $subtask->is_completed ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-slate-300 bg-white hover:border-blue-500' }}"
                    aria-label="{{ $subtask->is_completed ? 'Mark pending' : 'Mark completed' }}">
                    @if($subtask->is_completed)
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </button>
            </form>

            <p dir="auto" @class(['bidi-auto min-w-0 flex-1 truncate text-sm font-medium', 'text-slate-400 line-through' => $subtask->is_completed, 'text-slate-800' => ! $subtask->is_completed])>
                {{ $subtask->title }}
            </p>

            @if($subtask->assignedUser)
                <span class="hidden max-w-[10rem] shrink-0 truncate rounded-full bg-indigo-50/80 px-2 py-0.5 text-xs font-medium text-indigo-600 sm:inline-flex">{{ $subtask->assignedUser->name }}</span>
            @endif
            @if($subtask->is_completed)
                <x-badge class="shrink-0" tone="emerald">Completed ✓</x-badge>
            @else
                <x-badge class="shrink-0" tone="amber">Pending</x-badge>
            @endif
        </div>

        <div class="ml-auto flex shrink-0 items-center gap-1">
            <button type="button" @click="editing = true" class="rounded-md px-2 py-1 text-xs font-medium text-slate-500 hover:bg-white hover:text-indigo-600">Edit</button>
            <form action="{{ route('subtasks.destroy', $subtask) }}" method="POST" onsubmit="return confirm('Delete this subtask?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md p-1 text-slate-400 hover:bg-white hover:text-rose-600" aria-label="Delete subtask">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </form>
        </div>
    </div>

    {{-- --- Inline Alpine.js edit form --- --}}
    <div x-show="editing" x-cloak class="pt-1">
        <form action="{{ route('subtasks.update', $subtask) }}" method="POST" class="flex flex-nowrap items-center gap-2">
            @csrf
            @method('PATCH')
            <input type="text" name="title" value="{{ $subtask->title }}" required dir="auto" class="bidi-auto h-10 min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
            <select name="assigned_to" class="h-10 w-40 shrink-0 rounded-lg border border-gray-300 bg-gray-50 px-2 text-sm">
                <option value="">Assignee (optional)</option>
                @foreach($assigneeOptions as $user)
                    <option value="{{ $user->id }}" @selected($subtask->assigned_to === $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex h-10 shrink-0 items-center rounded-lg bg-indigo-600 px-4 text-sm font-medium whitespace-nowrap text-white hover:bg-indigo-700">Save</button>
            <button type="button" @click="editing = false" class="inline-flex h-10 shrink-0 items-center rounded-lg px-3 text-sm font-medium whitespace-nowrap text-slate-500 hover:bg-white">Cancel</button>
        </form>
    </div>
</div>
