@extends('layouts.app')
@php
    $pageTitle = 'Departments';
@endphp

@section('content')
@php
    $editId = old('edit_id', request('edit'));
    $editing = $editId ? $departments->getCollection()->firstWhere('id', (int) $editId) : null;
    $openForm = $errors->any() || request()->filled('edit');
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
@endphp

<div class="mx-auto max-w-7xl space-y-6"
    x-data="{
        open: {{ $openForm ? 'true' : 'false' }},
        isEdit: {{ $editing || old('edit_id') ? 'true' : 'false' }},
        id: {{ $editing?->id ?? (old('edit_id') ? (int) old('edit_id') : 'null') }},
        name: @js(old('name', $editing?->name ?? '')),
        code: @js(old('code', $editing?->code ?? '')),
        isActive: {{ filter_var(old('is_active', $editing?->is_active ?? true), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false' }}
    }">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Departments</h1>
            <p class="mt-1 text-sm text-slate-500">Name, code, and active status only.</p>
        </div>
        <button type="button"
            @click="open = true; isEdit = false; id = null; name = ''; code = ''; isActive = true"
            class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">
            Add department
        </button>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('departments.index') }}" class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 md:grid-cols-4">
        <input name="search" value="{{ request('search') }}" placeholder="Search name or code..."
            class="{{ $field }} sm:col-span-2">
        <select name="active" class="{{ $field }}">
            <option value="">All statuses</option>
            <option value="1" @selected(request('active') === '1')>Active</option>
            <option value="0" @selected(request('active') === '0')>Inactive</option>
        </select>
        <button class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 sm:w-auto">Filter</button>
    </form>

    {{-- Table --}}
    <div class="space-y-3 md:hidden">
        @forelse ($departments as $department)
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $department->name }}</p>
                        @if ($department->code)
                            <span class="mt-1 inline-flex rounded-md border border-indigo-100 bg-indigo-50 px-2 py-0.5 font-mono text-xs font-semibold text-indigo-700">{{ $department->code }}</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('departments.active.toggle', $department) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit">
                            <x-badge :tone="$department->is_active ? 'emerald' : 'slate'">{{ $department->is_active ? 'Active' : 'Inactive' }}</x-badge>
                        </button>
                    </form>
                </div>
                <p class="mt-2 text-sm text-slate-500">{{ $department->tasks_count }} tasks</p>
                <div class="mt-3 flex flex-wrap gap-3">
                    <a href="{{ route('departments.show', $department) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">View</a>
                    <button type="button"
                        @click="open = true; isEdit = true; id = {{ $department->id }}; name = {{ \Illuminate\Support\Js::from($department->name) }}; code = {{ \Illuminate\Support\Js::from($department->code ?? '') }}; isActive = {{ $department->is_active ? 'true' : 'false' }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                    <form method="POST" action="{{ route('departments.destroy', $department) }}" onsubmit="return confirm('Delete this department?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm font-medium text-rose-600 hover:text-rose-800">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-200 bg-white py-14 text-center text-slate-500">No departments found.</div>
        @endforelse
    </div>

    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm md:block">
        <div class="w-full overflow-x-auto">
        <table class="w-full min-w-[640px] border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3">Department</th>
                    <th class="px-5 py-3">Code</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Tasks</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($departments as $department)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-4 font-semibold text-slate-800">{{ $department->name }}</td>
                        <td class="px-5 py-4">
                            @if ($department->code)
                                <span class="inline-flex rounded-md border border-indigo-100 bg-indigo-50 px-2 py-0.5 font-mono text-xs font-semibold text-indigo-700">{{ $department->code }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <form method="POST" action="{{ route('departments.active.toggle', $department) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit">
                                    <x-badge :tone="$department->is_active ? 'emerald' : 'slate'">{{ $department->is_active ? 'Active' : 'Inactive' }}</x-badge>
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $department->tasks_count }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('departments.show', $department) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">View</a>
                                <button type="button"
                                    @click="open = true; isEdit = true; id = {{ $department->id }}; name = {{ \Illuminate\Support\Js::from($department->name) }}; code = {{ \Illuminate\Support\Js::from($department->code ?? '') }}; isActive = {{ $department->is_active ? 'true' : 'false' }}"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <form method="POST" action="{{ route('departments.destroy', $department) }}" onsubmit="return confirm('Delete this department?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-rose-600 hover:text-rose-800">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-14 text-center text-slate-500">No departments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="overflow-x-auto">{{ $departments->links() }}</div>

    {{-- Create / edit form --}}
    <x-slide-over>
        <x-slot:title>
            <span x-show="!isEdit">Add department</span>
            <span x-show="isEdit">Edit department</span>
        </x-slot>

        <form method="POST" class="space-y-5" :action="isEdit ? '{{ url('/departments') }}/' + id : '{{ route('departments.store') }}'">
            @csrf
            <template x-if="isEdit">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="edit_id" :value="id">

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Department name <span class="text-rose-500">*</span></label>
                <input name="name" x-model="name" required placeholder="Development, HR, Sales" class="{{ $field }}">
                <p class="text-xs text-slate-500">Must be unique.</p>
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Code</label>
                <input name="code" x-model="code" placeholder="DEV-01" class="{{ $field }} font-mono">
                <p class="text-xs text-slate-500">Optional short code such as DEV-01 or HR-02.</p>
                <x-input-error :messages="$errors->get('code')" />
            </div>

            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-3">
                <input type="hidden" name="is_active" :value="isActive ? 1 : 0">
                <input type="checkbox" x-model="isActive" class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                <span>
                    <span class="block text-xs font-semibold text-slate-700">Active</span>
                    <span class="block text-xs text-slate-500">Inactive departments stay in the list but are not used for new work.</span>
                </span>
            </label>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 pb-2 sm:flex-row sm:justify-end sm:gap-3">
                <button type="button" @click="open = false" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-slate-100 sm:w-auto">Cancel</button>
                <button class="w-full rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">Save</button>
            </div>
        </form>
    </x-slide-over>
</div>
@endsection
