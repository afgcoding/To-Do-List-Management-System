@props(['users'])
@php
    $assignees = collect($users);
    $visible = $assignees->take(3);
    $extra = max(0, $assignees->count() - $visible->count());
@endphp
@if ($assignees->isEmpty())
    <span class="text-xs italic text-slate-400">Unassigned</span>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center']) }}>
        <div class="flex items-center -space-x-2 overflow-hidden p-0.5">
            @foreach ($visible as $user)
                <x-user-avatar :user="$user" size="sm" class="ring-2 ring-white transition hover:z-10 hover:ring-indigo-200" />
            @endforeach
            @if ($extra > 0)
                <span class="inline-flex size-7 items-center justify-center rounded-full bg-slate-200 text-[10px] font-semibold text-slate-600 ring-2 ring-white" title="{{ $assignees->skip(3)->pluck('name')->join(', ') }}">+{{ $extra }}</span>
            @endif
        </div>
    </div>
@endif
