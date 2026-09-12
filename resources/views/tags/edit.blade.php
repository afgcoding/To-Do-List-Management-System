@extends('layouts.app')
@php
    $pageTitle = 'Edit Tag';
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
@endphp

@section('content')
<div class="mx-auto max-w-xl space-y-4">
    <x-back-link :href="route('tags.index')">Back to tags</x-back-link>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
            <h1 class="text-lg font-semibold text-slate-900">Edit tag</h1>
            <p class="mt-1 text-sm text-slate-500">Give the tag a unique name.</p>
        </div>

        <form method="POST" action="{{ route('tags.update', $tag) }}" class="space-y-5 p-6">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-semibold text-slate-700">Tag name <span class="text-rose-500">*</span></label>
                <input id="name" name="name" value="{{ old('name', $tag->name) }}" required placeholder="Bug, Feature, Urgent-Fix" class="{{ $field }}">
                <p class="text-xs text-slate-500">Tag names must be unique.</p>
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-5 pb-2">
                <a href="{{ route('tags.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
