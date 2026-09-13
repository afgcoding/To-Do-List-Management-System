@props(['log'])

@php
    $action = (string) $log->action;
    $isComplete = $action === 'changed_status' && str_contains((string) $log->description, 'to Completed');
    $badge = match (true) {
        $isComplete => 'bg-emerald-100 text-emerald-700',
        $action === 'created_task' => 'bg-indigo-100 text-indigo-700',
        $action === 'changed_status' => 'bg-sky-100 text-sky-700',
        $action === 'changed_priority' => 'bg-amber-100 text-amber-800',
        $action === 'changed_deadline' => 'bg-blue-100 text-blue-700',
        $action === 'updated_assignment' => 'bg-violet-100 text-violet-700',
        $action === 'added_comment' => 'bg-purple-100 text-purple-700',
        $action === 'uploaded_attachment' => 'bg-slate-100 text-slate-600',
        default => 'bg-slate-100 text-slate-600',
    };
    $dot = match (true) {
        $isComplete => 'bg-emerald-500',
        $action === 'changed_status' => 'bg-sky-500',
        $action === 'changed_priority' => 'bg-amber-500',
        $action === 'updated_assignment' => 'bg-violet-500',
        $action === 'added_comment' => 'bg-purple-500',
        default => 'bg-slate-400',
    };
    $name = $log->user->name ?? 'System';
    $when = $log->created_at;
    $absoluteTime = format_date($when);
@endphp

<li class="relative">
    <span @class(['absolute top-3 -left-[23px] size-2.5 rounded-full ring-4 ring-white', $dot])></span>
    <div class="flex items-start gap-2.5">
        <div class="relative shrink-0">
            <x-user-avatar :user="$log->user" :name="$name" size="sm" />
            <span @class(['absolute -right-1 -bottom-0.5 inline-flex size-4 items-center justify-center rounded-full ring-2 ring-white', $badge])>
                @if ($isComplete)
                    <svg class="size-2.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.5 7.57a1 1 0 0 1-1.43-.01l-3.5-3.5A1 1 0 1 1 5.7 9.35l2.79 2.79 6.79-6.856a1 1 0 0 1 1.424.006Z" clip-rule="evenodd"/></svg>
                @elseif ($action === 'added_comment')
                    <svg class="size-2.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M18 10c0 3.866-3.582 7-8 7a8.84 8.84 0 0 1-4.33-1.134L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7Z"/></svg>
                @elseif ($action === 'uploaded_attachment')
                    <svg class="size-2.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M8 2a2 2 0 0 0-2 2v10a4 4 0 1 0 8 0V6a1 1 0 1 0-2 0v8a2 2 0 1 1-4 0V4a4 4 0 1 1 8 0v10a6 6 0 0 1-12 0V6a1 1 0 0 0-2 0v8a8 8 0 1 0 16 0V4a6 6 0 0 0-6-6H8Z"/></svg>
                @else
                    <svg class="size-2.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v4.59L7.3 13.24a.75.75 0 1 0 1.4.52l2.25-2.4A.75.75 0 0 0 11.25 11V6.75Z" clip-rule="evenodd"/></svg>
                @endif
            </span>
        </div>
        <div class="min-w-0 flex-1 pt-0.5">
            <p class="text-xs leading-relaxed text-slate-600">
                <span dir="auto" class="bidi-auto font-semibold text-slate-800">{{ $name }}</span>
                <span dir="auto" class="bidi-auto"> {{ $log->description }}</span>
            </p>
            @if ($when)
                <time datetime="{{ $when->toIso8601String() }}" class="mt-0.5 block text-[11px] text-slate-400" title="{{ $absoluteTime }}">
                    {{ $when->diffForHumans() }}
                </time>
            @endif
        </div>
    </div>
</li>
