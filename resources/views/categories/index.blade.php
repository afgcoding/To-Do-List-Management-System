@extends('layouts.app')
@php
    $pageTitle = 'Categories';
@endphp

@section('content')
@php
    $editId = old('edit_id', request('edit'));
    $editing = $editId ? $categories->getCollection()->firstWhere('id', (int) $editId) : null;
    $openForm = $errors->any() || request()->filled('edit');
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
@endphp

<div class="mx-auto max-w-7xl space-y-6"
    x-data="{
        open: {{ $openForm ? 'true' : 'false' }},
        isEdit: {{ $editing || old('edit_id') ? 'true' : 'false' }},
        id: {{ $editing?->id ?? (old('edit_id') ? (int) old('edit_id') : 'null') }},
        name: @js(old('name', $editing?->name ?? '')),
        color: @js(old('color', $editing?->color ?? '#6366F1'))
    }">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Categories</h1>
            <p class="mt-1 text-sm text-slate-500">Group work with a name and a color.</p>
        </div>
        <button type="button"
            @click="open = true; isEdit = false; id = null; name = ''; color = '#6366F1'"
            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
            Add category
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">Color</th>
                    <th class="px-5 py-3">Tasks</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($categories as $category)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span class="size-3 rounded-full border border-slate-200" style="background-color: {{ $category->color ?: '#CBD5E1' }}"></span>
                                <span class="font-semibold text-slate-800">{{ $category->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <x-color-pill :label="$category->name" :color="$category->color" />
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $category->tasks_count }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-3">
                                <button type="button"
                                    @click="open = true; isEdit = true; id = {{ $category->id }}; name = {{ \Illuminate\Support\Js::from($category->name) }}; color = {{ \Illuminate\Support\Js::from($category->color ?: '#6366F1') }}"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-rose-600 hover:text-rose-800">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-14 text-center text-slate-500">No categories yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $categories->links() }}</div>

    {{-- Create / edit form --}}
    <x-slide-over>
        <x-slot:title>
            <span x-show="!isEdit">Add category</span>
            <span x-show="isEdit">Edit category</span>
        </x-slot>

        <form method="POST" class="space-y-5" :action="isEdit ? '{{ url('/categories') }}/' + id : '{{ route('categories.store') }}'">
            @csrf
            <template x-if="isEdit">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="edit_id" :value="id">

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Category name <span class="text-rose-500">*</span></label>
                <input name="name" x-model="name" required placeholder="Frontend, Database, Recruitment" class="{{ $field }}">
                <p class="text-xs text-slate-500">Shown on tasks as a colored label.</p>
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <x-color-picker />

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <button type="button" @click="open = false" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </x-slide-over>
</div>
@endsection
