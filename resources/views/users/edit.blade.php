@extends('layouts.app')
@php
    $pageTitle = 'Edit User';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="users-form-header">
        <div class="min-w-0">
            <h1 class="workspace-title">Edit user</h1>
            <p class="mt-1 text-sm text-gray-500">Update profile, role, and account details for {{ $user->name }}.</p>
        </div>
        <x-back-link class="users-back" :href="route('users.index')">Back to users</x-back-link>
    </div>

    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('users.partials.form', ['user' => $user])
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end sm:gap-3">
            <a href="{{ route('users.index') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50 sm:w-auto">Cancel</a>
            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">Save changes</button>
        </div>
    </form>
</div>
@endsection
