@extends('layouts.app')
@php
    $pageTitle = 'Edit Role';
@endphp

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit {{ $role->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Update the role name and the permissions it grants.</p>
        </div>
        <x-back-link :href="route('roles.index')">Back to roles</x-back-link>
    </div>

    <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div>
            <label class="mb-1.5 block text-xs font-semibold text-slate-700">Role name</label>
            <input type="text" name="name" required value="{{ old('name', $role->name) }}" @disabled($role->name === 'Super Admin') class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 disabled:bg-slate-100">
            @if ($role->name === 'Super Admin')
                <input type="hidden" name="name" value="Super Admin">
                <p class="mt-1 text-xs text-slate-500">The Super Admin role name is reserved for the global bypass.</p>
            @endif
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>
        @include('roles.partials.permission-grid', ['permissionGroups' => $permissionGroups, 'selected' => $selected])
        <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
            <a href="{{ route('roles.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancel</a>
            <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save changes</button>
        </div>
    </form>
</div>
@endsection
