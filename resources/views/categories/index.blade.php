@extends('layouts.app')
@php
    $pageTitle = 'Categories';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
@php
    $editId = old('edit_id', request('edit'));
    $editing = $editId ? $categories->getCollection()->firstWhere('id', (int) $editId) : null;
    $openForm = $errors->any() || request()->filled('edit');
@endphp

<div class="categories-page mx-auto max-w-7xl space-y-6"
    x-data="{
        open: {{ $openForm ? 'true' : 'false' }},
        isEdit: {{ $editing || old('edit_id') ? 'true' : 'false' }},
        id: {{ $editing?->id ?? (old('edit_id') ? (int) old('edit_id') : 'null') }},
        name: @js(old('name', $editing?->name ?? '')),
        color: @js(old('color', $editing?->color ?? '#6366F1'))
    }">

    <div class="categories-header">
        <div class="min-w-0">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
            </ol>
            <h1 class="workspace-title">Categories</h1>
            <p class="mt-1 text-sm text-gray-500">Group work with a name and a color.</p>
        </div>
        <button type="button"
            @click="open = true; isEdit = false; id = null; name = ''; color = '#6366F1'"
            class="btn btn-primary categories-add">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add category
        </button>
    </div>

    {{-- Table --}}
    <div class="space-y-3 md:hidden">
        @forelse ($categories as $category)
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="size-3 rounded-full border border-slate-200" style="background-color: {{ $category->color ?: '#CBD5E1' }}"></span>
                    <span class="font-semibold text-slate-800">{{ $category->name }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between gap-3">
                    <x-color-pill :label="$category->name" :color="$category->color" />
                    <span class="text-sm text-slate-600">{{ $category->tasks_count }} tasks</span>
                </div>
                <div class="categories-actions">
                    <button type="button"
                        @click="open = true; isEdit = true; id = {{ $category->id }}; name = {{ \Illuminate\Support\Js::from($category->name) }}; color = {{ \Illuminate\Support\Js::from($category->color ?: '#6366F1') }}"
                        class="categories-action">Edit</button>
                    <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="categories-action categories-action-danger">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-200 bg-white py-14 text-center text-slate-500">No categories yet.</div>
        @endforelse
    </div>

    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm md:block">
        <div class="w-full overflow-x-auto">
        <table class="w-full min-w-[560px] border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">Color</th>
                    <th class="px-5 py-3">Tasks</th>
                    <th class="px-5 py-3 text-center">Actions</th>
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
                        <td class="categories-actions-cell px-5 py-4">
                            <div class="categories-actions">
                                <button type="button"
                                    @click="open = true; isEdit = true; id = {{ $category->id }}; name = {{ \Illuminate\Support\Js::from($category->name) }}; color = {{ \Illuminate\Support\Js::from($category->color ?: '#6366F1') }}"
                                    class="categories-action">Edit</button>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="categories-action categories-action-danger">Delete</button>
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
    </div>
    <div class="overflow-x-auto">{{ $categories->links() }}</div>

    {{-- Create / edit form --}}
    <x-slide-over>
        <x-slot:title>
            <span x-show="!isEdit">Add category</span>
            <span x-show="isEdit">Edit category</span>
        </x-slot>

        <form method="POST" class="categories-form" :action="isEdit ? '{{ url('/categories') }}/' + id : '{{ route('categories.store') }}'">
            @csrf
            <template x-if="isEdit">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="edit_id" :value="id">

            <div class="categories-form-field">
                <label class="categories-form-label">Category name <span class="categories-form-required">*</span></label>
                <input name="name" x-model="name" required placeholder="Frontend, Database, Recruitment" class="categories-form-input">
                <p class="categories-form-hint">Shown on tasks as a colored label.</p>
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="categories-color-picker">
                <x-color-picker />
            </div>

            <div class="categories-form-actions">
                <button type="button" @click="open = false" class="categories-form-cancel">Cancel</button>
                <button type="submit" class="categories-form-save">Save</button>
            </div>
        </form>
    </x-slide-over>
</div>
@endsection
