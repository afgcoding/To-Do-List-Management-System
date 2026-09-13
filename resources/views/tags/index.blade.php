@extends('layouts.app')
@php
    $pageTitle = 'Tags';
@endphp

@section('content')
@php
    $editId = old('edit_id', request('edit'));
    $editing = $editId ? $tags->getCollection()->firstWhere('id', (int) $editId) : null;
    $openForm = $errors->any() || request()->filled('edit');
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
@endphp

<div class="mx-auto max-w-7xl space-y-6"
    x-data="{
        open: {{ $openForm ? 'true' : 'false' }},
        isEdit: {{ $editing || old('edit_id') ? 'true' : 'false' }},
        id: {{ $editing?->id ?? (old('edit_id') ? (int) old('edit_id') : 'null') }},
        name: @js(old('name', $editing?->name ?? ''))
    }">

    {{-- Header --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tags</h1>
            <p class="mt-1 text-sm text-slate-500">Unique labels such as Bug, Feature, or Urgent-Fix.</p>
        </div>
        <button type="button"
            @click="open = true; isEdit = false; id = null; name = ''"
            class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">
            Add tag
        </button>
    </div>

    {{-- Table --}}
    <div class="space-y-3 md:hidden">
        @forelse ($tags as $tag)
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $tag->name }}</span>
                <p class="mt-2 text-sm text-slate-600">{{ $tag->tasks_count }} {{ \Illuminate\Support\Str::plural('task', $tag->tasks_count) }}</p>
                <p class="text-xs text-slate-500">{{ $tag->created_at?->format('M d, Y') }}</p>
                <div class="mt-3 flex gap-3">
                    <button type="button"
                        @click="open = true; isEdit = true; id = {{ $tag->id }}; name = {{ \Illuminate\Support\Js::from($tag->name) }}"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                    <form method="POST" action="{{ route('tags.destroy', $tag) }}" onsubmit="return confirm('Delete this tag?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm font-medium text-rose-600 hover:text-rose-800">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-200 bg-white py-14 text-center text-slate-500">No tags created yet.</div>
        @endforelse
    </div>

    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm md:block">
        <div class="w-full overflow-x-auto">
        <table class="w-full min-w-[560px] border-collapse text-left">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-5 py-3">Tag</th>
                    <th class="px-5 py-3">Linked tasks</th>
                    <th class="px-5 py-3">Created</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @forelse ($tags as $tag)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $tag->name }}</span>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $tag->tasks_count }} {{ \Illuminate\Support\Str::plural('task', $tag->tasks_count) }}</td>
                        <td class="px-5 py-4 text-slate-500">{{ $tag->created_at?->format('M d, Y') }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-3">
                                <button type="button"
                                    @click="open = true; isEdit = true; id = {{ $tag->id }}; name = {{ \Illuminate\Support\Js::from($tag->name) }}"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <form method="POST" action="{{ route('tags.destroy', $tag) }}" onsubmit="return confirm('Delete this tag?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-medium text-rose-600 hover:text-rose-800">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-14 text-center text-slate-500">No tags created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="overflow-x-auto">{{ $tags->links() }}</div>

    {{-- Create / edit form (name only) --}}
    <x-slide-over>
        <x-slot:title>
            <span x-show="!isEdit">Add tag</span>
            <span x-show="isEdit">Edit tag</span>
        </x-slot>

        <form method="POST" class="space-y-5" :action="isEdit ? '{{ url('/tags') }}/' + id : '{{ route('tags.store') }}'">
            @csrf
            <template x-if="isEdit">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="edit_id" :value="id">

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Tag name <span class="text-rose-500">*</span></label>
                <input name="name" x-model="name" required placeholder="Bug, Feature, Urgent-Fix" class="{{ $field }}">
                <p class="text-xs text-slate-500">Tag names must be unique.</p>
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 pb-2 sm:flex-row sm:justify-end sm:gap-3">
                <button type="button" @click="open = false" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-slate-100 sm:w-auto">Cancel</button>
                <button class="w-full rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">Save</button>
            </div>
        </form>
    </x-slide-over>
</div>
@endsection
