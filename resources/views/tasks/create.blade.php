@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Create new task</h1>
            <p class="text-sm text-slate-500">Set ownership, priority, and deadlines before work starts.</p>
        </div>
        <x-back-link :href="route('tasks.index')">Back to tasks</x-back-link>
    </div>

    <form action="{{ route('tasks.store') }}" method="POST" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @csrf
        {{-- --- Main fields (title, description, dates) --- --}}
        <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
            @include('tasks.partials.form-fields')
        </div>
        {{-- --- Sidebar (priority, status, assignment) --- --}}
        <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @include('tasks.partials.form-sidebar')
            <div class="flex flex-col gap-2 border-t border-slate-200 pt-4">
                <button type="submit" class="w-full rounded-lg bg-indigo-600 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">Create task</button>
                <a href="{{ route('tasks.index') }}" class="w-full rounded-lg border border-slate-300 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
