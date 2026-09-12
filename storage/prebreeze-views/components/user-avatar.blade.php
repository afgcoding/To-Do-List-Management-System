{{-- Initials avatar, or a stored/remote photo when `src` is provided --}}
@props(['name', 'size' => 'md', 'src' => null, 'rounded' => 'full'])
@php
    $sizes = [
        'sm' => 'size-7 text-[10px]',
        'md' => 'size-8 text-[11px]',
        'lg' => 'size-10 text-xs',
        'xl' => 'size-16 text-lg',
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
    $shape = $rounded === '2xl' ? 'rounded-2xl' : 'rounded-full';
@endphp
@if (filled($src))
    <img src="{{ $src }}" alt="{{ $name }}" title="{{ $name }}"
        {{ $attributes->merge(['class' => 'inline-block shrink-0 object-cover ring-2 ring-white '.$shape.' '.$sizes[$size]]) }}>
@else
    <div title="{{ $name }}" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center font-bold text-white ring-2 ring-white '.$shape.' '.$sizes[$size].' '.$tone]) }}>
        {{ $initials }}
    </div>
@endif
