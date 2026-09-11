@extends('layouts.app')
@php
    $pageTitle = 'Create Department';
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
    $isActive = filter_var(old('is_active', true), FILTER_VALIDATE_BOOLEAN);
@endphp

@section('content')
<div class="mx-auto max-w-xl">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
            <h1 class="text-lg font-semibold text-slate-900">Add department</h1>
            <p class="mt-1 text-sm text-slate-500">Name, code, and active status.</p>
        </div>

        <form method="POST" action="{{ route('departments.store') }}" class="space-y-5 p-6">
            @csrf

            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-semibold text-slate-700">Department name <span class="text-rose-500">*</span></label>
                <input id="name" name="name" value="{{ old('name') }}" required placeholder="Development, HR, Sales" class="{{ $field }}">
                <p class="text-xs text-slate-500">Must be unique.</p>
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="space-y-1.5">
                <label for="code" class="block text-xs font-semibold text-slate-700">Code</label>
                <input id="code" name="code" value="{{ old('code') }}" placeholder="DEV-01" class="{{ $field }} font-mono">
                <p class="text-xs text-slate-500">Optional short code such as DEV-01 or HR-02.</p>
                <x-input-error :messages="$errors->get('code')" />
            </div>

            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked($isActive) class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
                <span>
                    <span class="block text-xs font-semibold text-slate-700">Active</span>
                    <span class="block text-xs text-slate-500">Inactive departments stay in the list but are not used for new work.</span>
                </span>
            </label>
            <x-input-error :messages="$errors->get('is_active')" />

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <a href="{{ route('departments.index') }}" class="rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
