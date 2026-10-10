@extends('layouts.app')
@php
    $pageTitle = 'Create Role';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="roles-form-page">
    <div class="roles-form-header">
        <div class="min-w-0">
            <h1 class="workspace-title">Create role</h1>
            <p class="roles-form-lead">Name the role and select the permissions it should grant.</p>
        </div>
        <x-back-link class="roles-back" :href="route('roles.index')">Back to roles</x-back-link>
    </div>

    <form method="POST" action="{{ route('roles.store') }}" class="roles-form">
        @csrf
        <div class="roles-form-field">
            <label class="roles-form-label" for="role-name">Role name <span class="roles-form-required">*</span></label>
            <div class="roles-input-wrap">
                <svg class="roles-input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z"/>
                </svg>
                <input id="role-name" type="text" name="name" required value="{{ old('name') }}" placeholder="Auditor, Team lead" class="roles-form-input">
            </div>
            <p class="roles-form-hint">Shown wherever this role is assigned.</p>
            <x-input-error :messages="$errors->get('name')" />
        </div>
        @include('roles.partials.permission-grid', ['permissionGroups' => $permissionGroups, 'selected' => $selected])
        <div class="roles-form-actions">
            <a href="{{ route('roles.index') }}" class="roles-form-cancel">Cancel</a>
            <button type="submit" class="roles-form-save">Save role</button>
        </div>
    </form>
</div>
@endsection
