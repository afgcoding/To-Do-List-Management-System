@extends('layouts.app')
@php
    $pageTitle = 'Recurring Tasks';
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
    $editId = old('edit_id', request('edit'));
    $editing = $editId ? $recurringTasks->getCollection()->firstWhere('id', (int) $editId) : null;
    $openForm = $errors->any() || request()->filled('edit');
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6"
    x-data="{
        open: {{ $openForm ? 'true' : 'false' }},
        id: {{ $editing?->id ?? (old('edit_id') ? (int) old('edit_id') : 'null') }},
        recurrenceType: @js(old('recurrence_type', $editing?->recurrence_type?->value ?? 'weekly')),
        repeatInterval: @js(old('repeat_interval', $editing?->repeat_interval ?? 1)),
        nextDate: @js(old('next_recurring_date', $editing?->next_recurring_date?->format('Y-m-d') ?? ''))
    }">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Recurring Tasks</h1>
            <p class="mt-1 text-sm text-slate-500">Schedules that automatically create new task copies when their next run date is due.</p>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
            <x-back-link :href="route('tasks.index')">Back to tasks</x-back-link>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                New recurring task
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-left">
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
                            <div class="flex flex-wrap justify-end gap-3">
                                <form method="POST" action="{{ route('recurring-tasks.active.toggle', $schedule) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-sm font-medium text-slate-600 hover:text-indigo-600">
                                        {{ $schedule->is_active ? 'Pause' : 'Resume' }}
                                    </button>
                                </form>
                                <button type="button"
                                    @click="open = true; id = {{ $schedule->id }}; recurrenceType = @js($schedule->recurrence_type->value); repeatInterval = {{ $schedule->repeat_interval }}; nextDate = @js($schedule->next_recurring_date?->format('Y-m-d'))"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit schedule</button>
                                @if ($schedule->task)
                                    <a href="{{ route('tasks.edit', $schedule->task) }}" class="text-sm font-medium text-slate-600 hover:text-indigo-600">Edit task</a>
                                @endif
                                <form method="POST" action="{{ route('recurring-tasks.destroy', $schedule) }}" onsubmit="return confirm('Remove this recurring schedule? The base task will be kept.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-rose-600 hover:text-rose-800">Delete</button>
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
    <div>{{ $recurringTasks->links() }}</div>

    <x-slide-over>
        <x-slot:title>Edit schedule</x-slot>
        <form method="POST" class="space-y-5" :action="'{{ url('/recurring-tasks') }}/' + id">
            @csrf
            @method('PUT')
            <input type="hidden" name="edit_id" :value="id">
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Recurrence type</label>
                <select name="recurrence_type" x-model="recurrenceType" class="{{ $field }}">
                    @foreach (\App\Enums\RecurrenceType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('recurrence_type')" />
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Repeat interval</label>
                <input type="number" name="repeat_interval" min="1" x-model="repeatInterval" class="{{ $field }}">
                <x-input-error :messages="$errors->get('repeat_interval')" />
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Next run date</label>
                <input type="date" name="next_recurring_date" x-model="nextDate" class="{{ $field }}">
                <x-input-error :messages="$errors->get('next_recurring_date')" />
            </div>
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5 pb-2">
                <button type="button" @click="open = false" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </x-slide-over>
</div>
@endsection
