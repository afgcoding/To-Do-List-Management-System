@extends('layouts.app')

@php
    $pageTitle = 'Create new task';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="mx-auto min-w-0 max-w-4xl">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-lg font-bold tracking-tight text-slate-800 sm:text-2xl">Create new task</h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">Set ownership, priority, and deadlines before work starts.</p>
        </div>
        <x-back-link :href="route('tasks.index')" class="h-8 w-fit self-end px-2.5 py-1 text-xs sm:h-9 sm:self-auto sm:px-3.5 sm:py-2 sm:text-sm">Back to tasks</x-back-link>
    </div>

    <form action="{{ route('tasks.store') }}" method="POST" class="grid min-w-0 grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
        @csrf
        {{-- --- Main fields (title, description, dates) --- --}}
        <div class="min-w-0 space-y-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-6 lg:col-span-2">
            @include('tasks.partials.form-fields')
        </div>
        {{-- --- Sidebar (priority, status, assignment) --- --}}
        <div class="min-w-0 space-y-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-6">
            @include('tasks.partials.form-sidebar')
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <a href="{{ route('tasks.index') }}" class="inline-flex h-8 shrink-0 items-center justify-center rounded-lg border border-slate-300 px-3 text-xs font-medium text-slate-700 hover:bg-slate-50 sm:h-9 sm:px-3.5 sm:text-sm">Cancel</a>
                <button type="submit" class="inline-flex h-8 items-center justify-center rounded-lg bg-indigo-600 px-4 text-xs font-medium text-white shadow-sm transition hover:bg-indigo-700 sm:h-9 sm:px-5 sm:text-sm">Create task</button>
            </div>
        </div>
    </form>
</div>
@endsection
