@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    {{-- ==================== BACK TO TASK LIST ==================== --}}
    <div class="flex items-center">
        <x-back-link :href="route('tasks.index')">Back to tasks</x-back-link>
    </div>

    {{-- ==================== HERO HEADER CARD ==================== --}}
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
    @endphp
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        {{-- Title + Edit / Delete --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <h1 dir="auto" class="bidi-auto min-w-0 text-2xl font-bold tracking-tight text-slate-900">{{ $task->title }}</h1>
            <div class="flex shrink-0 items-center gap-2">
                @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                    <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L8.25 18.002H5.25v-3L16.862 4.487z"/></svg>
                    Edit Task
                </a>
                @endcan
                @can('delete', $task)
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-100 bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-600 transition hover:bg-rose-100" aria-label="Delete task">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>

        {{-- Status, priority, category, tags --}}
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusStyle['wrap'] }}">
                <span @class(['size-1.5 rounded-full', $statusStyle['dot'], 'animate-pulse' => $task->status === \App\Enums\TaskStatus::InProgress])></span>
                {{ $task->status->label() }}
            </span>
            @if($task->status === \App\Enums\TaskStatus::Completed)
                <span class="text-[11px] italic text-slate-400">Automated by subtasks</span>
            @endif
            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $priorityStyle }}">{{ $task->priority->label() }}</span>
            @if($task->is_overdue)
                <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700">Overdue</span>
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
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
        {{-- ==================== LEFT COLUMN: MAIN WORK AREA ==================== --}}
        <div class="min-w-0 space-y-6 overflow-hidden lg:col-span-2">
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

            {{-- ==================== DISCUSSION ==================== --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
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
                        class="bidi-auto w-full rounded-xl border border-slate-300 bg-slate-50/80 px-3.5 py-2.5 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500">{{ old('comment') }}</textarea>
                    <x-input-error :messages="$errors->get('comment')" />
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-slate-500 hover:text-indigo-600">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                            Attach files
                            <input type="file" name="files[]" multiple class="sr-only"
                                accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.zip,.txt"
                                @change="fileCount = $event.target.files.length">
                        </label>
                        <span class="text-[11px] text-slate-400" x-show="fileCount > 0" x-text="fileCount + ' file(s) selected'"></span>
                        <button class="ml-auto rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Post comment</button>
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
            <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
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
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition hover:border-indigo-400 hover:bg-indigo-50/40">
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
                            <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ format_date($task->start_date) ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Due</dt>
                            <dd @class(['mt-0.5 text-sm font-medium', 'text-rose-600' => $task->is_overdue, 'text-slate-800' => ! $task->is_overdue])>
                                {{ format_date($task->due_date) ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Created</dt>
                            <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ format_date($task->created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Updated</dt>
                            <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ format_date($task->updated_at) }}</dd>
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
                                <x-user-avatar :user="$user" size="md" />
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

                {{-- --- Activity & History Timeline --- --}}
                <div class="border-t border-slate-100 pt-5">
                    <h3 class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Activity &amp; History</h3>
                    @if ($task->activityLogs->isEmpty())
                        <p class="text-sm text-slate-400">No activity recorded yet.</p>
                    @else
                        <ol class="relative max-h-96 space-y-0 overflow-y-auto border-l-2 border-slate-200 ps-5">
                            @foreach ($task->activityLogs as $log)
                                <x-tasks.activity-item :log="$log" />
                            @endforeach
                        </ol>
                    @endif
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
