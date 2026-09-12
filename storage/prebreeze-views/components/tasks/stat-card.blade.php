{{-- Clickable dashboard stat tile --}}
@props(['label', 'value', 'tone' => 'indigo', 'href' => null])
@php
    $tones = [
        'indigo' => 'text-indigo-600 bg-indigo-50 border-indigo-100',
        'sky' => 'text-sky-600 bg-sky-50 border-sky-100',
        'emerald' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
        'rose' => 'text-rose-600 bg-rose-50 border-rose-100',
    ];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'rounded-xl border bg-white p-4 shadow-sm transition hover:shadow-md '.$tones[$tone]]) }}>
    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold text-slate-800">{{ $value }}</p>
</{{ $tag }}>
