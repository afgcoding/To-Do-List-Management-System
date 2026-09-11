@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    {{-- ==================== BACK TO TASK LIST ==================== --}}
    <div class="flex items-center">
        <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50">
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            Back to tasks
        </a>
    </div>

    {{-- ==================== HERO HEADER CARD (Title, Status, Progress) ==================== --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0 space-y-3">
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 dir="auto" class="bidi-auto text-2xl font-bold tracking-tight text-slate-900">{{ $task->title }}</h1>
                    <x-tasks.status-badge :status="$task->status" />
                    @if($task->status === \App\Enums\TaskStatus::Completed)
                        <span class="text-[11px] font-medium text-slate-400">Automated by subtasks</span>
                    @endif
                    <x-tasks.priority-badge :priority="$task->priority" />
                    @if($task->is_overdue)
                        <x-badge tone="rose">Overdue</x-badge>
                    @endif
                    @if($task->category)
                        <x-color-pill :label="$task->category->name" :color="$task->category->color" />
                    @endif
                    @foreach($task->tags as $tag)
                        <x-color-pill :label="$tag->name" :color="$tag->color" />
                    @endforeach
                </div>
                <p class="text-xs text-slate-500">
                    Created {{ $task->created_at->format('M d, Y') }}
                    by <span class="font-medium text-slate-700">{{ $task->creator->name ?? 'System' }}</span>
                    <span class="mx-1.5 text-slate-300">·</span>
                    Updated {{ $task->updated_at->diffForHumans() }}
                </p>
            </div>
            {{-- --- Edit & Delete actions --- --}}
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                    <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L8.25 18.002H5.25v-3L16.862 4.487z"/></svg>
                    Edit Task
                </a>
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-100 bg-rose-50 px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-100" aria-label="Delete task">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
        {{-- --- Overall progress bar (from completed subtasks) --- --}}
        <div class="mt-5 border-t border-slate-100 pt-5">
            <x-tasks.progress :percent="$task->progress" label="Overall progress" :gradient="true" />
        </div>
    </section>

    {{-- ==================== 2-COLUMN LAYOUT (66% main / 33% sidebar) ==================== --}}
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
        {{-- ==================== LEFT COLUMN: MAIN WORK AREA ==================== --}}
        <div class="min-w-0 space-y-6 lg:col-span-2">
            {{-- --- Task Description Section (RTL Supported) --- --}}
            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <span class="grid size-8 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                    </span>
                    <h2 class="text-sm font-semibold text-slate-900">Description</h2>
                </div>
                <div dir="auto" class="bidi-auto whitespace-pre-line text-left text-[15px] leading-relaxed text-slate-700 rtl:text-right">
                    {{ $task->description ?: 'No detailed description provided.' }}
                </div>
            </section>

            {{-- --- Subtask Checklist Card --- --}}
            <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="grid size-8 place-items-center rounded-lg bg-blue-50 text-blue-600">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <h2 class="text-sm font-semibold text-slate-900">Subtasks</h2>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $task->completed_subtasks_count }}/{{ $task->subtasks_count }}</span>
                    </div>
                </div>

                {{-- --- Add New Subtask Form --- --}}
                <form action="{{ route('subtasks.store') }}" method="POST" class="mb-4 flex flex-col gap-2 sm:flex-row">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <input type="text" name="title" placeholder="Add a new subtask..." required dir="auto"
                        class="bidi-auto min-w-0 flex-1 rounded-lg border-slate-200 bg-slate-50/80 text-sm shadow-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                    <select name="assigned_to" class="rounded-lg border-slate-200 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Assignee</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="shrink-0 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">Add Subtask</button>
                </form>
                <x-input-error class="mb-3" :messages="$errors->get('title')" />

                {{-- --- Subtask Items Loop --- --}}
                <div class="space-y-1.5">
                    @forelse($task->subtasks as $subtask)
                        <x-tasks.subtask-item :subtask="$subtask" :assignees="$users" />
                    @empty
                        <p class="rounded-xl bg-slate-50/80 py-8 text-center text-sm text-slate-400">No subtasks yet. Add the first one above.</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- ==================== RIGHT COLUMN: STICKY SIDEBAR ==================== --}}
        <aside class="lg:sticky lg:top-6">
            <div class="space-y-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                {{-- --- Quick Status & Priority Actions --- --}}
                <div>
                    <h3 class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Quick actions</h3>
                    <div class="space-y-3">
                        <form action="{{ route('tasks.status.update', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                            <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach(\App\Enums\TaskStatus::manualCases() as $status)
                                    <option value="{{ $status->value }}" @selected($task->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                                <option value="" disabled @selected($task->status === \App\Enums\TaskStatus::Completed)>
                                    Completed — Automated by Subtasks
                                </option>
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400">Completed is set automatically when every subtask is done.</p>
                        </form>
                        <form action="{{ route('tasks.priority.update', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="mb-1 block text-xs font-medium text-slate-500">Priority</label>
                            <select name="priority" onchange="this.form.submit()" class="w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach(\App\Enums\TaskPriority::cases() as $priority)
                                    <option value="{{ $priority->value }}" @selected($task->priority === $priority)>{{ $priority->label() }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                {{-- --- Task Metadata Properties (Dates, Category, Department) --- --}}
                <div class="border-t border-slate-100 pt-5">
                    <h3 class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Properties</h3>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-4">
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Department</dt>
                            <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ $task->department->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Category</dt>
                            <dd class="mt-0.5">
                                @if($task->category)
                                    <x-color-pill :label="$task->category->name" :color="$task->category->color" />
                                @else
                                    <span class="text-sm font-medium text-slate-800">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Start</dt>
                            <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ $task->start_date?->format('M d, Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Due</dt>
                            <dd @class(['mt-0.5 text-sm font-medium', 'text-rose-600' => $task->is_overdue, 'text-slate-800' => ! $task->is_overdue])>
                                {{ $task->due_date?->format('M d, Y') ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Created</dt>
                            <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ $task->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Updated</dt>
                            <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ $task->updated_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- --- Assigned Team Members List --- --}}
                <div class="border-t border-slate-100 pt-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Assigned team</h3>
                        <button type="button" data-modal-open="assign-team-modal" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Assign / Reassign</button>
                    </div>
                    <div class="space-y-2.5">
                        @forelse($task->assignedUsers as $user)
                            <div class="flex items-center gap-2.5">
                                <x-user-avatar :name="$user->name" size="md" />
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-800">{{ $user->name }}</p>
                                    @if($user->pivot->assigned_at)
                                        <p class="text-[11px] text-slate-400">Assigned {{ \Illuminate\Support\Carbon::parse($user->pivot->assigned_at)->diffForHumans() }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">No assigned members</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

{{-- --- Assign / Reassign modal (opens edit screen) --- --}}
<x-modal id="assign-team-modal" title="Assign / Reassign">
    <p class="text-sm leading-relaxed text-slate-600">Update the people responsible for this task from the edit screen. You can add or remove teammates without changing other task details.</p>
    <div class="mt-5 flex justify-end gap-2">
        <button type="button" data-modal-close="assign-team-modal" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
        <a href="{{ route('tasks.edit', $task) }}" class="rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Open edit task</a>
    </div>
</x-modal>
@endsection
