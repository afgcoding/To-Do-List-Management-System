{{-- Create / edit: title, description, start & due dates --}}
@php
    $task = $task ?? null;
    $assignedUserIds = $assignedUserIds ?? [];
    $selectedTagIds = $selectedTagIds ?? [];
@endphp

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Task Title *</label>
    <input type="text" name="title" value="{{ old('title', $task?->title) }}" required
        class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        placeholder="e.g. Design landing page mockup">
    <x-input-error class="mt-1" :messages="$errors->get('title')" />
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
    <textarea name="description" rows="5"
        class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        placeholder="Detailed instructions for the team...">{{ old('description', $task?->description) }}</textarea>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Start Date</label>
        <input type="date" name="start_date"
            value="{{ old('start_date', $task?->start_date?->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <x-input-error class="mt-1" :messages="$errors->get('start_date')" />
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Due Date</label>
        <input type="date" name="due_date"
            value="{{ old('due_date', $task?->due_date?->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <x-input-error class="mt-1" :messages="$errors->get('due_date')" />
    </div>
</div>

@php
    $schedule = $task?->recurringTask;
    $recurringDefault = old('is_recurring', $schedule !== null);
@endphp
<div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4"
    x-data="{ recurring: {{ $recurringDefault ? 'true' : 'false' }} }">
    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium text-slate-800">
        <input type="checkbox" name="is_recurring" value="1" x-model="recurring"
            @checked($recurringDefault)
            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        Make this a Recurring Task
    </label>
    <p class="mt-1 text-xs text-slate-500">The scheduler copies this task when the next run date is due.</p>

    <div x-show="recurring" x-cloak class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Recurrence type</label>
            <select name="recurrence_type" class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (\App\Enums\RecurrenceType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('recurrence_type', $schedule?->recurrence_type?->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-1" :messages="$errors->get('recurrence_type')" />
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Repeat interval</label>
            <input type="number" name="repeat_interval" min="1" value="{{ old('repeat_interval', $schedule?->repeat_interval ?? 1) }}"
                class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error class="mt-1" :messages="$errors->get('repeat_interval')" />
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Next run date</label>
            <input type="date" name="next_recurring_date"
                value="{{ old('next_recurring_date', $schedule?->next_recurring_date?->format('Y-m-d') ?? now()->addDay()->toDateString()) }}"
                class="w-full rounded-lg border border-slate-200 p-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error class="mt-1" :messages="$errors->get('next_recurring_date')" />
        </div>
    </div>
</div>
