@extends('layouts.app')

@php
    $pageTitle = 'Create new task';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="mx-auto min-w-0 max-w-4xl">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="workspace-title">Create new task</h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">Set ownership, priority, and deadlines before work starts.</p>
        </div>
        <x-back-link :href="route('tasks.index')" class="h-8 w-fit self-end px-2.5 py-1 text-xs sm:h-9 sm:self-auto sm:px-3.5 sm:py-2 sm:text-sm">Back to tasks</x-back-link>
    </div>

    <form action="{{ route('tasks.store') }}" method="POST" class="grid min-w-0 grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
        @csrf
        {{-- --- Main fields (title, description, dates) --- --}}
        <div class="card flat workspace-card min-w-0 space-y-4 lg:col-span-2">
            @include('tasks.partials.form-fields')
        </div>
        {{-- --- Sidebar (priority, status, assignment) --- --}}
        <div class="card flat workspace-card min-w-0 space-y-4">
            @include('tasks.partials.form-sidebar')
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-sm btn-primary">Create task</button>
            </div>
        </div>
    </form>
</div>
@endsection
