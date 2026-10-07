@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="workspace-title">Edit task</h1>
            <p class="text-sm text-slate-500">Update details, deadlines, status, and assignments.</p>
        </div>
        <x-back-link :href="route('tasks.show', $task)">Back to task</x-back-link>
    </div>

    <form action="{{ route('tasks.update', $task) }}" method="POST" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @csrf
        @method('PUT')
        {{-- --- Main fields (title, description, dates) --- --}}
        <div class="card flat workspace-card space-y-4 lg:col-span-2">
            @include('tasks.partials.form-fields', ['task' => $task])
        </div>
        {{-- --- Sidebar (priority, status, assignment) --- --}}
        <div class="card flat workspace-card space-y-4">
            @include('tasks.partials.form-sidebar', [
                'task' => $task,
                'assignedUserIds' => $assignedUserIds,
                'selectedTagIds' => $selectedTagIds,
            ])
            <div class="flex flex-col gap-2 border-t border-slate-200 pt-4">
                <button type="submit" class="btn btn-primary btn-block">Save changes</button>
                <a href="{{ route('tasks.show', $task) }}" class="btn btn-secondary btn-block">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
