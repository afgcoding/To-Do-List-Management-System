@extends('layouts.app')
@php
    $pageTitle = 'Create Role';
@endphp

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Create role</h1>
            <p class="mt-1 text-sm text-slate-500">Name the role and select the permissions it should grant.</p>
        </div>
        <x-back-link :href="route('roles.index')">Back to roles</x-back-link>
    </div>

    <form method="POST" action="{{ route('roles.store') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Role name</label>
            <input type="text" name="name" required value="{{ old('name') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>
        @include('roles.partials.permission-grid', ['permissionGroups' => $permissionGroups, 'selected' => $selected])
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end sm:gap-3">
            <a href="{{ route('roles.index') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50 sm:w-auto">Cancel</a>
            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">Save role</button>
        </div>
    </form>
</div>
@endsection
