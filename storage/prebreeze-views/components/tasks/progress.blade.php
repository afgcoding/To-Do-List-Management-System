{{-- Subtask completion bar: percent = completed / total --}}
@props(['percent' => 0, 'label' => null, 'gradient' => false])
@php($percent = max(0, min(100, (int) $percent)))
<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <div class="flex items-center justify-between text-xs">
        <span class="font-semibold tracking-wide text-slate-500">{{ $label ?? 'Progress' }}</span>
        <span class="font-semibold text-indigo-600">{{ $percent }}%{{ $percent === 100 ? ' Completed' : '' }}</span>
    </div>
    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
        <div @class([
            'h-2 rounded-full transition-all duration-500 ease-out',
            'bg-linear-to-r from-indigo-500 to-blue-500' => $gradient,
            'bg-indigo-600' => ! $gradient,
        ]) style="width: {{ $percent }}%"></div>
    </div>
</div>
