@props(['subtask', 'assignees'])
@php
    $assigneeOptions = collect($assignees);
    if ($subtask->assignedUser && ! $assigneeOptions->contains('id', $subtask->assignedUser->id)) {
        $assigneeOptions = $assigneeOptions->push($subtask->assignedUser);
    }
@endphp
{{-- Subtask row: checkbox, title, assignee, status badge, hover actions --}}
<div x-data="{ editing: false }" class="subtask-item">
    {{-- --- View mode --- --}}
    <div x-show="!editing" class="subtask-item-line">
        <div class="subtask-item-main">
            {{-- Toggle is_completed (true/false) --}}
            <form action="{{ route('subtasks.toggle', $subtask) }}" method="POST" class="subtask-item-toggle">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="subtask-item-check {{ $subtask->is_completed ? 'is-complete' : '' }}"
                    aria-label="{{ $subtask->is_completed ? 'Mark pending' : 'Mark completed' }}">
                    @if($subtask->is_completed)
                        <svg class="subtask-item-check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </button>
            </form>

            <p dir="auto" @class(['bidi-auto subtask-item-title', 'is-complete' => $subtask->is_completed])>
                {{ $subtask->title }}
            </p>

            @if($subtask->assignedUser)
                <span class="subtask-item-assignee">{{ $subtask->assignedUser->name }}</span>
            @endif
            @if($subtask->is_completed)
                <x-badge class="subtask-item-status" tone="emerald">Completed ✓</x-badge>
            @else
                <x-badge class="subtask-item-status" tone="amber">Pending</x-badge>
            @endif
        </div>

        <div class="subtask-item-actions">
            <button type="button" @click="editing = true" class="btn btn-secondary btn-sm">Edit</button>
            <form action="{{ route('subtasks.destroy', $subtask) }}" method="POST" onsubmit="return confirm('Delete this subtask?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm subtask-item-delete" aria-label="Delete subtask">
                    <svg class="subtask-item-delete-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </form>
        </div>
    </div>

    {{-- --- Inline Alpine.js edit form --- --}}
    <div x-show="editing" x-cloak class="subtask-item-edit">
        <form action="{{ route('subtasks.update', $subtask) }}" method="POST" class="subtask-create-row subtask-add-row subtask-edit-row">
            @csrf
            @method('PATCH')
            <input type="text" name="title" value="{{ $subtask->title }}" required dir="auto" class="form-control subtask-title bidi-auto">
            <select name="assigned_to" class="form-control subtask-assignee">
                <option value="">Assignee (optional)</option>
                @foreach($assigneeOptions as $user)
                    <option value="{{ $user->id }}" @selected($subtask->assigned_to === $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm subtask-submit">Save</button>
            <button type="button" @click="editing = false" class="btn btn-secondary btn-sm subtask-cancel">Cancel</button>
        </form>
    </div>
</div>
