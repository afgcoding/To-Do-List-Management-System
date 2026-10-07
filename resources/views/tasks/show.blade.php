@extends('layouts.app')

@php
    $pageTitle = $task->title;
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-5">
    @php
        $statusStyles = [
            'todo' => ['wrap' => 'border-slate-200 bg-slate-50 text-slate-700', 'dot' => 'bg-slate-400'],
            'in_progress' => ['wrap' => 'border-indigo-200 bg-indigo-50 text-indigo-700', 'dot' => 'bg-indigo-500'],
            'completed' => ['wrap' => 'border-emerald-200 bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500'],
            'cancelled' => ['wrap' => 'border-rose-200 bg-rose-50 text-rose-700', 'dot' => 'bg-rose-500'],
        ];
        $statusStyle = $statusStyles[$task->status->value] ?? $statusStyles['todo'];
        $priorityStyles = [
            'urgent' => 'border-rose-200 bg-rose-50 text-rose-700',
            'high' => 'border-amber-200 bg-amber-50 text-amber-700',
            'medium' => 'border-sky-200 bg-sky-50 text-sky-700',
            'low' => 'border-slate-200 bg-slate-100 text-slate-600',
        ];
        $priorityStyle = $priorityStyles[$task->priority->value] ?? $priorityStyles['low'];
        $cardClass = 'card flat workspace-card w-full space-y-4 p-4 sm:p-5';
        $iconBadge = 'flex h-7 w-7 items-center justify-center rounded-lg text-xs';
    @endphp
    <div class="task-show-toolbar">
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">
            ← Back to Tasks
        </a>
        <div class="header-actions">
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-secondary btn-sm">
                    Edit Task
                </a>
            @endcan
            @can('delete', $task)
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" aria-label="Delete task">
                        Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>
    <section class="{{ $cardClass }} task-show-header">
        <h1 dir="auto" class="workspace-title bidi-auto">{{ $task->title }}</h1>

        {{-- Status, priority, category, tags --}}
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold sm:text-sm {{ $statusStyle['wrap'] }}">
                <span @class(['size-1.5 rounded-full', $statusStyle['dot'], 'animate-pulse' => $task->status === \App\Enums\TaskStatus::InProgress])></span>
                {{ $task->status->label() }}
            </span>
            @if($task->status === \App\Enums\TaskStatus::Completed)
                <span class="text-[11px] italic text-slate-400">Automated by subtasks</span>
            @endif
            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold sm:text-sm {{ $priorityStyle }}">{{ $task->priority->label() }}</span>
            @if($task->is_overdue)
                <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 sm:text-sm">Overdue</span>
            @endif
            @if($task->category)
                <x-color-pill :label="$task->category->name" :color="$task->category->color" />
            @endif
            @foreach($task->tags as $tag)
                <x-tasks.tag-badge :name="$tag->name" />
            @endforeach
        </div>

        {{-- Created / updated --}}
        <div class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
            <span>Created {{ format_date($task->created_at) }}</span>
            <span>by <strong class="font-medium text-slate-700">{{ $task->creator->name ?? 'System' }}</strong></span>
            <span class="hidden text-slate-300 sm:inline">•</span>
            <span>Updated {{ $task->updated_at->diffForHumans() }}</span>
        </div>

        {{-- Overall progress (from completed subtasks) --}}
        <div class="mt-4 border-t border-gray-100 pt-4">
            <x-tasks.progress :percent="$task->progress" label="Overall progress" />
        </div>
    </section>

    {{-- ==================== 2-COLUMN LAYOUT (66% main / 33% sidebar) ==================== --}}
    <div class="task-show-body grid w-full grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-3">
        {{-- ==================== LEFT COLUMN: MAIN WORK AREA ==================== --}}
        <div class="w-full min-w-0 space-y-4 sm:space-y-5 lg:col-span-2">
            {{-- --- Task Description Section (RTL Supported) --- --}}
            <section class="{{ $cardClass }}">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <span class="{{ $iconBadge }} bg-indigo-50 text-indigo-600">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                    </span>
                    Description
                </h2>
                <div dir="auto" class="bidi-auto whitespace-pre-line break-words text-left text-sm leading-relaxed text-slate-700 sm:text-[15px] rtl:text-right">
                    {{ $task->description ?: 'No detailed description provided.' }}
                </div>
            </section>

            {{-- --- Subtask Checklist Card --- --}}
            <section class="{{ $cardClass }}">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <span class="{{ $iconBadge }} bg-blue-50 text-blue-600">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    Subtasks
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $task->completed_subtasks_count }}/{{ $task->subtasks_count }}</span>
                </h2>

                {{-- --- Add New Subtask Form --- --}}
                <form action="{{ route('subtasks.store') }}" method="POST" class="subtask-create-form">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">

                    <div class="subtask-create-row subtask-add-row">
                        <input type="text" name="title" placeholder="Add a new subtask..." required dir="auto"
                            class="form-control subtask-title">

                        <select name="assigned_to" class="form-control subtask-assignee">
                            <option value="">Assignee</option>
                            @foreach($task->assignedUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-primary btn-sm subtask-submit">
                            + Add Subtask
                        </button>
                    </div>
                </form>
                <x-input-error class="mb-3" :messages="$errors->get('title')" />

                {{-- --- Subtask Items Loop --- --}}
                <div class="space-y-1.5">
                    @forelse($task->subtasks as $subtask)
                        <x-tasks.subtask-item :subtask="$subtask" :assignees="$task->assignedUsers" />
                    @empty
                        <p class="rounded-xl bg-slate-50/80 py-8 text-center text-sm text-slate-400">No subtasks yet. Add the first one above.</p>
                    @endforelse
                </div>
            </section>

            {{-- ==================== DISCUSSION ==================== --}}
            <section class="{{ $cardClass }}">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <span class="{{ $iconBadge }} bg-violet-50 text-violet-600">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-6.75 3.75h12.75A2.25 2.25 0 0 0 21 12.75v-7.5A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25v10.5A2.25 2.25 0 0 0 5.25 18Z"/></svg>
                    </span>
                    Discussion
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $task->comments->count() }}</span>
                </h2>

                {{-- Composer --}}
                <form method="POST" action="{{ route('comments.store') }}" enctype="multipart/form-data" class="mb-5 space-y-3" x-data="{ fileCount: 0 }">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <textarea name="comment" rows="3" required dir="auto" placeholder="Write a comment… use @Name to mention someone"
                        class="form-control bidi-auto">{{ old('comment') }}</textarea>
                    <x-input-error :messages="$errors->get('comment')" />
                    <div class="mt-3 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <label class="inline-flex min-w-0 flex-wrap cursor-pointer items-center gap-2 text-xs font-medium text-gray-500 hover:text-indigo-600">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                            Attach files
                            <input type="file" name="files[]" multiple class="sr-only"
                                accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt"
                                @change="fileCount = $event.target.files.length">
                            <span class="text-[11px] text-gray-400" x-show="fileCount > 0" x-text="fileCount + ' file(s) selected'"></span>
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm">Post comment</button>
                    </div>
                    <x-input-error :messages="$errors->get('files')" />
                    <x-input-error :messages="$errors->get('files.0')" />
                    <p class="text-[11px] text-slate-400">Images, PDF, Word, Excel, ZIP, or TXT. Max 10MB each.</p>
                </form>

                {{-- Thread --}}
                <div class="space-y-3">
                    @forelse ($task->comments as $comment)
                        <x-tasks.comment-item :comment="$comment" :mention-names="$mentionNames" :current-user-id="$currentUserId" />
                    @empty
                        <p class="rounded-xl bg-slate-50/80 py-8 text-center text-sm text-slate-400">No comments yet. Start the discussion above.</p>
                    @endforelse
                </div>
            </section>

            {{-- ==================== ALL TASK ATTACHMENTS ==================== --}}
            <section class="{{ $cardClass }}">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                    <span class="{{ $iconBadge }} bg-sky-50 text-sky-600">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </span>
                    Attachments
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $task->attachments->count() }}</span>
                </h2>

                {{-- Dropzone --}}
                <form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="mb-4">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-4 text-center transition-colors hover:border-indigo-500 sm:p-6">
                        <p class="text-sm font-medium text-slate-700">Drop files here or click to upload</p>
                        <p class="mt-1 text-[11px] text-slate-400">JPG, PNG, GIF, WEBP, PDF, DOC, XLS, ZIP, TXT • 10MB max</p>
                        <input type="file" name="files[]" multiple required class="sr-only"
                            accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt"
                            onchange="this.form.submit()">
                    </label>
                    <x-input-error class="mt-2" :messages="$errors->get('files')" />
                    <x-input-error :messages="$errors->get('files.0')" />
                </form>

                <div class="min-w-0 space-y-1 overflow-hidden">
                    @forelse ($task->attachments as $attachment)
                        <div class="min-w-0">
                            <x-tasks.attachment-chip :attachment="$attachment" :can-delete="(int) $attachment->user_id === (int) $currentUserId" />
                            <p class="mt-1 text-[11px] text-slate-400">
                                {{ $attachment->user->name ?? 'Unknown' }}
                                · {{ format_date($attachment->created_at) }}
                            </p>
                        </div>
                    @empty
                        <p class="rounded-xl bg-slate-50/80 py-6 text-center text-sm text-slate-400">No files attached to this task yet.</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- ==================== RIGHT COLUMN: STICKY SIDEBAR ==================== --}}
        <aside class="w-full min-w-0 space-y-4 self-start sm:space-y-5 lg:sticky lg:top-22 lg:col-span-1">
            <div class="{{ $cardClass }}">
                {{-- --- Quick Status & Priority Actions --- --}}
                <div>
                    <h3 class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Quick actions</h3>
                    <div class="space-y-3">
                        <form action="{{ route('tasks.status.update', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                            @php
                                $isAllSubtasksComplete = $task->subtasks_count > 0
                                    && $task->completed_subtasks_count === $task->subtasks_count;
                                $isTaskLocked = $isAllSubtasksComplete
                                    && $task->status === \App\Enums\TaskStatus::Completed;
                            @endphp
                            <select
                                name="status"
                                @if($isTaskLocked || ! auth()->user()?->can('updateStatus', $task))
                                    disabled
                                @endif
                                @can('updateStatus', $task)
                                    @if(! $isTaskLocked)
                                        onchange="this.form.submit()"
                                    @endif
                                @endcan
                                class="form-control {{ ($isTaskLocked || ! auth()->user()?->can('updateStatus', $task)) ? 'cursor-not-allowed bg-slate-50' : '' }}">
                                @foreach(\App\Enums\TaskStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($task->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            @if($isTaskLocked)
                                <p class="mt-1 text-[11px] text-emerald-600 font-medium">✓ Task is 100% complete. Uncheck any subtask to re-enable manual status change.</p>
                            @else
                                <p class="mt-1 text-[11px] text-slate-400">Choosing Completed marks every subtask done. Completing all subtasks sets this to Completed.</p>
                            @endif
                        </form>
                        <form action="{{ route('tasks.priority.update', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="mb-1 block text-xs font-medium text-slate-500">Priority</label>
                            <select name="priority" onchange="this.form.submit()" class="form-control">
                                @foreach(\App\Enums\TaskPriority::cases() as $priority)
                                    <option value="{{ $priority->value }}" @selected($task->priority === $priority)>{{ $priority->label() }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <div class="{{ $cardClass }}">
                {{-- --- Task Metadata Properties (Dates, Category, Department) --- --}}
                <div>
                    <h3 class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Properties</h3>
                    <dl class="grid grid-cols-1 gap-3 text-xs sm:grid-cols-2 sm:text-sm">
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Department</dt>
                            <dd class="mt-0.5 font-medium break-words text-slate-800">{{ $task->department->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Category</dt>
                            <dd class="mt-0.5">
                                @if($task->category)
                                    <x-color-pill :label="$task->category->name" :color="$task->category->color" />
                                @else
                                    <span class="font-medium text-slate-800">—</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Start</dt>
                            <dd class="mt-0.5 font-medium break-words text-slate-800">{{ format_date($task->start_date) ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Due</dt>
                            <dd @class(['mt-0.5 font-medium break-words', 'text-rose-600' => $task->is_overdue, 'text-slate-800' => ! $task->is_overdue])>
                                {{ format_date($task->due_date) ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Created</dt>
                            <dd class="mt-0.5 font-medium break-words text-slate-800">{{ format_date($task->created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Updated</dt>
                            <dd class="mt-0.5 font-medium break-words text-slate-800">{{ format_date($task->updated_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="{{ $cardClass }}">
                {{-- --- Assigned Team Members List --- --}}
                <div>
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Assigned team</h3>
                        <button type="button" data-modal-open="assign-team-modal" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Assign / Reassign</button>
                    </div>
                    <div>
                        @forelse($task->assignedUsers as $user)
                            <div class="flex items-center justify-between border-b border-gray-100 py-2 last:border-0">
                                <div class="flex min-w-0 items-center">
                                    <x-user-avatar :user="$user" size="md" class="h-7 w-7 rounded-full sm:h-8 sm:w-8" />
                                    <div class="min-w-0 ps-3">
                                        <p class="truncate text-xs font-medium text-gray-800 sm:text-sm">{{ $user->name }}</p>
                                        @if($user->pivot->assigned_at)
                                            <p class="text-xs text-gray-400">Assigned {{ \Illuminate\Support\Carbon::parse($user->pivot->assigned_at)->diffForHumans() }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">No assigned members</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- --- Activity & History Timeline --- --}}
            <div class="{{ $cardClass }}">
                <h3 class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Activity &amp; History</h3>
                @if ($task->activityLogs->isEmpty())
                    <p class="break-words text-xs text-slate-400 sm:text-sm">No activity recorded yet.</p>
                @else
                    <div class="custom-scrollbar max-h-[400px] space-y-4 overflow-y-auto pr-2 [scrollbar-width:thin]">
                        <ol class="ml-3 space-y-3 border-l-2 border-slate-200 pl-4 break-words text-xs sm:text-sm">
                            @foreach ($task->activityLogs as $log)
                                <x-tasks.activity-item :log="$log" />
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>

{{-- --- Assign / Reassign modal (opens edit screen) --- --}}
<x-modal id="assign-team-modal" title="Assign / Reassign">
    <p class="text-sm leading-relaxed text-slate-600">Update the people responsible for this task from the edit screen. You can add or remove teammates without changing other task details.</p>
    <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end sm:gap-2">
        <button type="button" data-modal-close="assign-team-modal" class="w-full rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 sm:w-auto">Cancel</button>
        <a href="{{ route('tasks.edit', $task) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-indigo-700 sm:w-auto">Open edit task</a>
    </div>
</x-modal>
@endsection

@push('styles')
<style>
.app-page-content form.subtask-create-form {
    margin-bottom: 1.5rem !important;
}
.app-page-content .subtask-add-row {
    display: grid !important;
    grid-template-columns: 1fr !important;
    align-items: center !important;
    width: 100% !important;
    gap: 0.5rem !important;
}
.app-page-content .subtask-add-row > .form-control,
.app-page-content .subtask-add-row > .btn {
    width: 100% !important;
    max-width: none !important;
    height: 42px !important;
    margin: 0 !important;
    align-self: center !important;
    box-sizing: border-box !important;
}
.app-page-content .subtask-add-row > .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0.35rem 0.7rem !important;
    white-space: nowrap !important;
}
@media (min-width: 640px) {
    .app-page-content .subtask-add-row {
        grid-template-columns: minmax(0, 1fr) 8rem max-content !important;
        grid-template-rows: 42px !important;
    }
    .app-page-content .subtask-edit-row {
        grid-template-columns: minmax(0, 1fr) 8rem max-content max-content !important;
    }
    .app-page-content .subtask-add-row > .subtask-assignee.form-control {
        max-width: 8rem !important;
    }
    .app-page-content .subtask-add-row > .btn {
        width: max-content !important;
        max-width: max-content !important;
    }
}
</style>
@endpush
