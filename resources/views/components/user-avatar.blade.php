{{-- Initials avatar with a color derived from the user name --}}
@props(['name', 'size' => 'md'])
@php
    $sizes = [
        'sm' => 'size-7 text-[10px]',
        'md' => 'size-8 text-[11px]',
        'lg' => 'size-9 text-xs',
    ];
    $tones = [
        'bg-indigo-600',
        'bg-sky-600',
        'bg-violet-600',
        'bg-teal-600',
        'bg-amber-500',
        'bg-rose-500',
        'bg-emerald-600',
        'bg-blue-600',
    ];
    $tone = $tones[abs(crc32($name)) % count($tones)];
    $initials = strtoupper(mb_substr($name, 0, 2));
@endphp
<div title="{{ $name }}" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full font-bold text-white ring-2 ring-white '.$sizes[$size].' '.$tone]) }}>
    {{ $initials }}
</div>
