@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Edit task</h1>
            <p class="text-sm text-slate-500">Update details, deadlines, status, and assignments.</p>
        </div>
        <a href="{{ route('tasks.show', $task) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50">&larr; Back to task</a>
    </div>

    <form action="{{ route('tasks.update', $task) }}" method="POST" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @csrf
        @method('PUT')
        {{-- --- Main fields (title, description, dates) --- --}}
        <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            @include('tasks.partials.form-fields', ['task' => $task])
        </div>
        {{-- --- Sidebar (priority, status, assignment) --- --}}
        <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @include('tasks.partials.form-sidebar', [
                'task' => $task,
                'assignedUserIds' => $assignedUserIds,
                'selectedTagIds' => $selectedTagIds,
            ])
            <div class="flex flex-col gap-2 border-t border-slate-200 pt-4">
                <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">Save changes</button>
                <a href="{{ route('tasks.show', $task) }}" class="w-full rounded-lg border border-slate-300 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
