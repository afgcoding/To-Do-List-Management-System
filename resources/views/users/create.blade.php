@extends('layouts.app')
@php
    $pageTitle = 'Add User';
@endphp

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Create user</h1>
            <p class="mt-1 text-sm text-slate-500">Add a teammate with role, department, and an optional profile photo.</p>
        </div>
        <x-back-link :href="route('users.index')">Back to users</x-back-link>
    </div>

    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @include('users.partials.form')
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end sm:gap-3">
            <a href="{{ route('users.index') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50 sm:w-auto">Cancel</a>
            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">Save user</button>
        </div>
    </form>
</div>
@endsection
