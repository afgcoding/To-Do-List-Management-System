{{-- Create / edit: title, description, start & due dates --}}
@php
    $task = $task ?? null;
    $assignedUserIds = $assignedUserIds ?? [];
    $selectedTagIds = $selectedTagIds ?? [];
@endphp

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Task Title') }} *</label>
    <input type="text" name="title" value="{{ old('title', $task?->title) }}" required
        class="form-control"
        placeholder="{{ __('e.g. Design landing page mockup') }}">
    <x-input-error class="mt-1" :messages="$errors->get('title')" />
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
    <textarea name="description" rows="5"
        class="form-control"
        placeholder="{{ __('Detailed instructions for the team...') }}">{{ old('description', $task?->description) }}</textarea>
</div>

<div class="grid grid-cols-1 gap-4 min-[400px]:grid-cols-2">
    <div class="min-w-0">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Start Date') }}</label>
        <x-calendar-date-input name="start_date" :value="old('start_date', $task?->start_date)" :picker="true" class="form-control" />
        <x-input-error class="mt-1" :messages="$errors->get('start_date')" />
    </div>
    <div class="min-w-0">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Due Date') }}</label>
        <x-calendar-date-input name="due_date" :value="old('due_date', $task?->due_date)" :picker="true" class="form-control" />
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
        {{ __('Make this a Recurring Task') }}
    </label>
    <p class="mt-1 text-xs text-slate-500">{{ __('The scheduler copies this task when the next run date is due.') }}</p>

    <div x-show="recurring" x-cloak class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Recurrence type') }}</label>
            <select name="recurrence_type" class="form-control">
                @foreach (\App\Enums\RecurrenceType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('recurrence_type', $schedule?->recurrence_type?->value) === $type->value)>{{ __($type->label()) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-1" :messages="$errors->get('recurrence_type')" />
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Repeat interval') }}</label>
            <input type="number" name="repeat_interval" min="1" value="{{ old('repeat_interval', $schedule?->repeat_interval ?? 1) }}"
                class="form-control">
            <x-input-error class="mt-1" :messages="$errors->get('repeat_interval')" />
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Next run date') }}</label>
            <x-calendar-date-input name="next_recurring_date" :value="old('next_recurring_date', $schedule?->next_recurring_date ?? now()->addDay())" class="form-control" />
            <x-input-error class="mt-1" :messages="$errors->get('next_recurring_date')" />
        </div>
    </div>
</div>
