{{-- Subtask completion bar: percent = completed / total --}}
@props(['percent' => 0, 'label' => null, 'gradient' => false])
@php
    $percent = max(0, min(100, (int) $percent));
    $barClass = match (true) {
        $percent >= 100 => 'bg-emerald-500',
        $percent >= 70 => 'bg-green-500',
        $percent >= 40 => 'bg-amber-500',
        default => 'bg-rose-400',
    };
    $textClass = match (true) {
        $percent >= 100 => 'text-emerald-600',
        $percent >= 70 => 'text-green-600',
        $percent >= 40 => 'text-amber-600',
        default => 'text-rose-500',
    };
@endphp
<div {{ $attributes->merge(['class' => $label === '' ? 'flex items-center gap-2' : 'space-y-1.5']) }}>
    @if ($label === '')
        <div class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full bg-slate-100">
            <div class="h-1.5 rounded-full transition-all duration-500 ease-out {{ $barClass }}" style="width: {{ $percent }}%"></div>
        </div>
        <span class="w-10 shrink-0 text-right text-xs font-semibold {{ $textClass }}">{{ $percent }}%</span>
    @else
        <div class="flex items-center justify-between text-xs">
            <span class="font-semibold tracking-wide text-slate-500">{{ $label ?? 'Progress' }}</span>
            <span class="font-semibold {{ $textClass }}">{{ $percent }}%{{ $percent === 100 ? ' Completed' : '' }}</span>
        </div>
        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-1.5 rounded-full transition-all duration-500 ease-out {{ $barClass }}" style="width: {{ $percent }}%"></div>
        </div>
    @endif
</div>
