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

<div class="grid grid-cols-2 gap-4">
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
