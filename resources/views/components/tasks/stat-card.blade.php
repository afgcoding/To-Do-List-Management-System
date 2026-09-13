{{-- Clickable dashboard stat tile --}}
@props(['label', 'value', 'tone' => 'indigo', 'href' => null])
@php
    $tones = [
        'indigo' => [
            'wrap' => 'border-slate-100 hover:border-indigo-100',
            'icon' => 'bg-indigo-50 text-indigo-600',
            'badge' => 'bg-indigo-50 text-indigo-700',
        ],
        'sky' => [
            'wrap' => 'border-slate-100 hover:border-sky-100',
            'icon' => 'bg-sky-50 text-sky-600',
            'badge' => 'bg-sky-50 text-sky-700',
        ],
        'emerald' => [
            'wrap' => 'border-slate-100 hover:border-emerald-100',
            'icon' => 'bg-emerald-50 text-emerald-600',
            'badge' => 'bg-emerald-50 text-emerald-700',
        ],
        'rose' => [
            'wrap' => 'border-slate-100 hover:border-rose-100',
            'icon' => 'bg-rose-50 text-rose-600',
            'badge' => 'bg-rose-50 text-rose-700',
        ],
    ];
    $style = $tones[$tone] ?? $tones['indigo'];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'group rounded-xl border border-slate-100 bg-white p-5 shadow-sm transition hover:shadow-md '.$style['wrap']]) }}>
    <div class="flex items-center justify-between gap-3">
        <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $style['icon'] }}">
            @if ($tone === 'sky')
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 13.5 7.5 9.75m0 0 3.75 3.75M7.5 9.75v9m12.75-3-3.75-3.75m0 0-3.75 3.75M16.5 12v9"/></svg>
            @elseif ($tone === 'emerald')
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            @elseif ($tone === 'rose')
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            @else
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
            @endif
        </span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-2xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
            <p class="truncate text-xs font-medium text-slate-500">{{ $label }}</p>
        </div>
        <span class="hidden shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide sm:inline-flex {{ $style['badge'] }}">{{ $label }}</span>
    </div>
</{{ $tag }}>
