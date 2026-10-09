@extends('layouts.app')
@php
    $pageTitle = 'Departments';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
@php
    $editId = old('edit_id', request('edit'));
    $editing = $editId ? $departments->getCollection()->firstWhere('id', (int) $editId) : null;
    $openForm = $errors->any() || request()->filled('edit');
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
@endphp

<div class="departments-page mx-auto max-w-7xl space-y-5"
    x-data="{
        open: {{ $openForm ? 'true' : 'false' }},
        isEdit: {{ $editing || old('edit_id') ? 'true' : 'false' }},
        id: {{ $editing?->id ?? (old('edit_id') ? (int) old('edit_id') : 'null') }},
        name: @js(old('name', $editing?->name ?? '')),
        code: @js(old('code', $editing?->code ?? '')),
        isActive: {{ filter_var(old('is_active', $editing?->is_active ?? true), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false' }}
    }">

    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
        <div class="min-w-0">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item active">Departments</li>
            </ol>
            <h1 class="workspace-title">Departments</h1>
            <p class="mt-1 text-sm text-gray-500">Manage department names, codes, active status, and team assignments.</p>
        </div>
        <button type="button"
            @click="open = true; isEdit = false; id = null; name = ''; code = ''; isActive = true"
            class="btn btn-primary departments-add">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add department
        </button>
    </div>

    <div class="departments-card w-full rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="nozha-filter-container">
            <form action="{{ route('departments.index') }}" method="GET" class="nozha-filter-form">
                <div class="nozha-input-wrapper">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by name or code..."
                           class="nozha-filter-input">
                </div>

                <div class="nozha-select-wrapper"
                    x-data="{
                        open: false,
                        value: @js(request('active', '')),
                        label: @js(request('active') === '1' ? 'Active' : (request('active') === '0' ? 'Inactive' : 'All statuses'))
                    }"
                    @click.outside="open = false">
                    <span class="nozha-select-label" x-text="label">{{ request('active') === '1' ? 'Active' : (request('active') === '0' ? 'Inactive' : 'All statuses') }}</span>
                    <input type="hidden" name="active" x-model="value" value="{{ request('active') }}">
                    <button type="button" class="nozha-select-trigger" @click="open = ! open" aria-haspopup="listbox" aria-label="Filter by status"></button>
                    <div class="nozha-select-menu" x-show="open" x-cloak>
                        <button type="button" class="nozha-select-option" @click="value = ''; label = 'All statuses'; open = false">All statuses</button>
                        <button type="button" class="nozha-select-option" @click="value = '1'; label = 'Active'; open = false">Active</button>
                        <button type="button" class="nozha-select-option" @click="value = '0'; label = 'Inactive'; open = false">Inactive</button>
                    </div>
                </div>

                <button type="submit" class="nozha-filter-btn">
                    <svg class="nozha-filter-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span>Filter</span>
                </button>
            </form>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[640px] border-collapse text-left">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Code</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Tasks</th>
                        <th class="px-5 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($departments as $department)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4">
                                <a href="{{ route('departments.show', $department) }}" class="text-xs font-semibold text-slate-800 transition-colors hover:text-indigo-600">{{ $department->name }}</a>
                            </td>
                            <td class="px-5 py-4">
                                @if ($department->code)
                                    <span class="rounded-lg bg-indigo-50 px-2.5 py-1 font-mono text-[11px] font-bold text-indigo-700">{{ $department->code }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route('departments.active.toggle', $department) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">
                                        @if ($department->is_active)
                                            <span class="rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-600">Active</span>
                                        @else
                                            <span class="rounded-full border border-slate-200 bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">Inactive</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $department->tasks_count }}</td>
                            <td class="departments-actions-cell px-5 py-4">
                                <div class="departments-actions">
                                    <a href="{{ route('departments.show', $department) }}" class="departments-action">View</a>
                                    <button type="button"
                                        @click="open = true; isEdit = true; id = {{ $department->id }}; name = {{ \Illuminate\Support\Js::from($department->name) }}; code = {{ \Illuminate\Support\Js::from($department->code ?? '') }}; isActive = {{ $department->is_active ? 'true' : 'false' }}"
                                        class="departments-action">Edit</button>
                                    <form method="POST" action="{{ route('departments.destroy', $department) }}" onsubmit="return confirm('Delete this department?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="departments-action departments-action-danger">Delete</button>
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
        <div class="overflow-x-auto border-t border-slate-100 px-3 py-3">{{ $departments->links() }}</div>
    </div>

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
