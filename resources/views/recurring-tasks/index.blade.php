@extends('layouts.app')
@php
    $pageTitle = 'Recurring Tasks';
    $hideLayoutPageHeader = true;
    $editId = old('edit_id', request('edit'));
    $editing = $editId ? $recurringTasks->getCollection()->firstWhere('id', (int) $editId) : null;
    $openForm = $errors->any() || request()->filled('edit');
@endphp

@section('content')
<div class="space-y-5"
    x-data="{
        open: {{ $openForm ? 'true' : 'false' }},
        id: {{ $editing?->id ?? (old('edit_id') ? (int) old('edit_id') : 'null') }},
        recurrenceType: @js(old('recurrence_type', $editing?->recurrence_type?->value ?? 'weekly')),
        repeatInterval: @js(old('repeat_interval', $editing?->repeat_interval ?? 1)),
        nextDate: @js(old('next_recurring_date', $editing?->next_recurring_date?->format('Y-m-d') ?? ''))
    }">
    <div class="mb-3">
        <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 transition-colors hover:text-indigo-800">
            ← Back to tasks
        </a>
    </div>

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="min-w-0">
            <h1 class="workspace-title">Recurring Tasks</h1>
            <p class="mt-1 text-sm text-gray-500">Schedules that automatically create new task copies when their next run date is due.</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary recurring-toolbar-btn">
            <span class="inline-flex items-center gap-1">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add recurring task
            </span>
        </a>
    </div>

    <div class="recurring-mobile-list">
        @forelse ($recurringTasks as $schedule)
            <article class="card flat workspace-card recurring-mobile-card">
                @if ($schedule->task)
                    <a href="{{ route('tasks.show', $schedule->task) }}" class="font-medium text-slate-800 hover:text-indigo-600">{{ $schedule->task->title }}</a>
                @else
                    <span class="text-slate-400">Deleted task</span>
                @endif
                <p class="mt-2 text-sm text-slate-600">{{ $schedule->frequencyLabel() }}</p>
                <p class="text-sm text-slate-600">Next run: {{ format_date($schedule->next_recurring_date) ?? '—' }}</p>
                <div class="mt-2">
                    @if ($schedule->is_active)
                        <x-badge tone="emerald">Active</x-badge>
                    @else
                        <x-badge tone="slate">Paused</x-badge>
                    @endif
                </div>
                <div class="recurring-actions recurring-card-actions">
                    <form method="POST" action="{{ route('recurring-tasks.active.toggle', $schedule) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-light">
                            {{ $schedule->is_active ? 'Pause' : 'Resume' }}
                        </button>
                    </form>
                    <button type="button"
                        @click="open = true; id = {{ $schedule->id }}; recurrenceType = @js($schedule->recurrence_type->value); repeatInterval = {{ $schedule->repeat_interval }}; nextDate = @js($schedule->next_recurring_date?->format('Y-m-d'))"
                        class="btn btn-sm btn-secondary">Edit schedule</button>
                    @if ($schedule->task)
                        <a href="{{ route('tasks.edit', $schedule->task) }}" class="btn btn-sm btn-secondary">Edit task</a>
                    @endif
                    <form method="POST" action="{{ route('recurring-tasks.destroy', $schedule) }}" onsubmit="return confirm('Remove this recurring schedule? The base task will be kept.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="card flat workspace-card py-14 text-center text-slate-500">No recurring schedules yet. Enable recurrence when creating or editing a task.</div>
        @endforelse
    </div>

    <div class="card flat workspace-card recurring-desktop-table overflow-hidden">
        <div class="w-full overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3">Base task</th>
                    <th class="px-5 py-3">Frequency</th>
                    <th class="px-5 py-3">Next run</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($recurringTasks as $schedule)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-4">
                            @if ($schedule->task)
                                <a href="{{ route('tasks.show', $schedule->task) }}" class="font-medium text-slate-800 hover:text-indigo-600">{{ $schedule->task->title }}</a>
                            @else
                                <span class="text-slate-400">Deleted task</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $schedule->frequencyLabel() }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ format_date($schedule->next_recurring_date) ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @if ($schedule->is_active)
                                <x-badge tone="emerald">Active</x-badge>
                            @else
                                <x-badge tone="slate">Paused</x-badge>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="recurring-actions">
                                <form method="POST" action="{{ route('recurring-tasks.active.toggle', $schedule) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-light">
                                        {{ $schedule->is_active ? 'Pause' : 'Resume' }}
                                    </button>
                                </form>
                                <button type="button"
                                    @click="open = true; id = {{ $schedule->id }}; recurrenceType = @js($schedule->recurrence_type->value); repeatInterval = {{ $schedule->repeat_interval }}; nextDate = @js($schedule->next_recurring_date?->format('Y-m-d'))"
                                    class="btn btn-sm btn-secondary">Edit schedule</button>
                                @if ($schedule->task)
                                    <a href="{{ route('tasks.edit', $schedule->task) }}" class="btn btn-sm btn-secondary">Edit task</a>
                                @endif
                                <form method="POST" action="{{ route('recurring-tasks.destroy', $schedule) }}" onsubmit="return confirm('Remove this recurring schedule? The base task will be kept.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-14 text-center text-slate-500">No recurring schedules yet. Enable recurrence when creating or editing a task.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="overflow-x-auto">{{ $recurringTasks->links() }}</div>

    <x-slide-over>
        <x-slot:title>Edit schedule</x-slot>
        <form method="POST" class="space-y-5" :action="'{{ url('/recurring-tasks') }}/' + id">
            @csrf
            @method('PUT')
            <input type="hidden" name="edit_id" :value="id">
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Recurrence type</label>
                <select name="recurrence_type" x-model="recurrenceType" class="form-control">
                    @foreach (\App\Enums\RecurrenceType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('recurrence_type')" />
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Repeat interval</label>
                <input type="number" name="repeat_interval" min="1" x-model="repeatInterval" class="form-control">
                <x-input-error :messages="$errors->get('repeat_interval')" />
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Next run date</label>
                <input type="date" name="next_recurring_date" x-model="nextDate" class="form-control">
                <x-input-error :messages="$errors->get('next_recurring_date')" />
            </div>
            <div class="recurring-slide-actions">
                <button type="button" @click="open = false" class="btn btn-light">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </x-slide-over>
</div>
@endsection
