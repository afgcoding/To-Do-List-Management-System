@extends('layouts.app')

@php
    $pageTitle = $task->title;
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-6">
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
        $cardClass = 'w-full space-y-4 rounded-xl border border-gray-200/80 bg-white p-4 shadow-xs sm:p-5';
    @endphp
    <section class="mb-6 w-full rounded-xl border border-gray-200/80 bg-white p-4 shadow-xs sm:p-5">
        <a href="{{ route('tasks.index') }}" class="mb-3 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 sm:text-sm">
            ← Back to Tasks
        </a>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 dir="auto" class="bidi-auto break-words text-xl font-bold text-gray-900 sm:text-2xl md:text-3xl">{{ $task->title }}</h1>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 sm:text-sm">
                    <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L8.25 18.002H5.25v-3L16.862 4.487z"/></svg>
                    Edit Task
                </a>
                @endcan
                @can('delete', $task)
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 transition hover:bg-rose-50 sm:text-sm" aria-label="Delete task">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>

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
        <p class="mt-3 text-xs text-slate-500">
            Created {{ format_date($task->created_at) }}
            by <span class="font-medium text-slate-700">{{ $task->creator->name ?? 'System' }}</span>
            <span class="mx-1.5 text-slate-300">·</span>
            Updated {{ $task->updated_at->diffForHumans() }}
        </p>

        {{-- Overall progress (from completed subtasks) --}}
        <div class="mt-5 border-t border-slate-100 pt-5">
            <x-tasks.progress :percent="$task->progress" label="Overall progress" />
        </div>
    </section>

    {{-- ==================== 2-COLUMN LAYOUT (66% main / 33% sidebar) ==================== --}}
    <div class="grid w-full grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- ==================== LEFT COLUMN: MAIN WORK AREA ==================== --}}
        <div class="w-full min-w-0 space-y-3.5 lg:col-span-2">
            {{-- --- Task Description Section (RTL Supported) --- --}}
            <section class="{{ $cardClass }}">
                <div class="mb-3 flex items-center gap-2">
                    <span class="grid size-8 place-items-center rounded-lg bg-indigo-50 text-indigo-600">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                    </span>
                    <h2 class="text-sm font-semibold text-slate-900">Description</h2>
                </div>
                <div dir="auto" class="bidi-auto whitespace-pre-line break-words text-left text-sm leading-relaxed text-slate-700 sm:text-[15px] rtl:text-right">
                    {{ $task->description ?: 'No detailed description provided.' }}
                </div>
            </section>

            {{-- --- Subtask Checklist Card --- --}}
            <section class="{{ $cardClass }}">
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
                <form action="{{ route('subtasks.store') }}" method="POST" class="mb-3 block w-full">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">

                    <div style="display: flex; width: 100%; align-items: center; gap: 0.5rem;" class="w-full">
                        {{-- High Width Input --}}
                        <input type="text" name="title" placeholder="Add a new subtask..." required dir="auto"
                            style="flex: 1 1 auto; width: 100%; min-width: 0;"
                            class="h-9 rounded-lg border border-gray-300 px-3 text-xs sm:text-sm focus:ring-2 focus:ring-indigo-500">

                        {{-- Compact Assignee Dropdown --}}
                        <select name="assigned_to"
                            style="flex: 0 0 120px; width: 120px;"
                            class="h-9 shrink-0 rounded-lg border border-gray-300 bg-gray-50 px-2 text-xs sm:text-sm text-gray-700">
                            <option value="">Assignee</option>
                            @foreach($task->assignedUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>

                        {{-- Compact Button --}}
                        <button type="submit"
                            style="flex: 0 0 auto;"
                            class="h-9 shrink-0 whitespace-nowrap rounded-lg bg-indigo-600 px-3.5 text-xs sm:text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                            Add Subtask
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
                <div class="mb-4 flex items-center gap-2">
                    <span class="grid size-8 place-items-center rounded-lg bg-violet-50 text-violet-600">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 8.25h9m-9 3H12m-6.75 3.75h12.75A2.25 2.25 0 0 0 21 12.75v-7.5A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25v10.5A2.25 2.25 0 0 0 5.25 18Z"/></svg>
                    </span>
                    <h2 class="text-sm font-semibold text-slate-900">Discussion</h2>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $task->comments->count() }}</span>
                </div>

                {{-- Composer --}}
                <form method="POST" action="{{ route('comments.store') }}" enctype="multipart/form-data" class="mb-5 space-y-3" x-data="{ fileCount: 0 }">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    <textarea name="comment" rows="3" required dir="auto" placeholder="Write a comment… use @Name to mention someone"
                        class="bidi-auto w-full rounded-lg border border-gray-300 p-3 text-xs placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 sm:text-sm">{{ old('comment') }}</textarea>
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
                        <button type="submit" class="inline-flex h-10 w-full shrink-0 items-center justify-center rounded-lg bg-indigo-600 px-4 text-xs font-semibold whitespace-nowrap text-white shadow-sm hover:bg-indigo-700 sm:w-auto sm:text-sm">Post comment</button>
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
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="grid size-8 place-items-center rounded-lg bg-sky-50 text-sky-600">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </span>
                        <h2 class="text-sm font-semibold text-slate-900">Attachments</h2>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $task->attachments->count() }}</span>
                    </div>
                </div>

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
        <aside class="w-full min-w-0 space-y-3.5 self-start lg:sticky lg:top-22 lg:col-span-1">
            <div class="w-full space-y-4 rounded-xl border border-gray-200/80 bg-white p-4 shadow-xs sm:p-5">
                {{-- --- Quick Status & Priority Actions --- --}}
                <div>
                    <h3 class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Quick actions</h3>
                    <div class="space-y-3">
                        <form action="{{ route('tasks.status.update', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                            <select
                                name="status"
                                @can('updateStatus', $task)
                                    onchange="this.form.submit()"
                                @else
                                    disabled
                                @endcan
                                class="w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ auth()->user()?->can('updateStatus', $task) ? '' : 'cursor-not-allowed bg-slate-50' }}">
                                @foreach(\App\Enums\TaskStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($task->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400">Choosing Completed marks every subtask done. Completing all subtasks sets this to Completed.</p>
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
            </div>

            <div class="w-full space-y-4 rounded-xl border border-gray-200/80 bg-white p-4 shadow-xs sm:p-5">
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

            <div class="w-full space-y-4 rounded-xl border border-gray-200/80 bg-white p-4 shadow-xs sm:p-5">
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
            <div class="w-full space-y-4 rounded-xl border border-gray-200/80 bg-white p-4 shadow-xs sm:p-5">
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
