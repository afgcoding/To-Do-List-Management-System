{{-- Stored photo when `src` or `$user->avatar` exists; otherwise name-based initials --}}
@props(['name' => 'User', 'size' => 'md', 'src' => null, 'rounded' => 'full', 'user' => null])
@php
    if ($user instanceof \App\Models\User) {
        $name = $user->name ?: $name;
        $src = filled($user->avatar) ? $user->avatar_url : null;
    }

    $sizes = [
        'sm' => 'size-7 text-[10px]',
        'md' => 'size-8 text-[11px]',
        'nav' => 'h-9 w-9 text-[11px]',
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
    $words = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: ['U'];
    $initials = strtoupper(mb_substr($words[0], 0, 1).(isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
    $shape = $rounded === '2xl' ? 'rounded-2xl' : 'rounded-full';
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp
@if (filled($src))
    <img src="{{ $src }}" alt="{{ $name }}" title="{{ $name }}"
        {{ $attributes->merge(['class' => 'inline-block shrink-0 overflow-hidden object-cover border border-slate-200 shadow-sm ring-2 ring-white '.$shape.' '.$sizeClass]) }}>
@else
    <div title="{{ $name }}" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center font-bold text-white ring-2 ring-white '.$shape.' '.$sizeClass.' '.$tone]) }}>{{ $initials }}</div>
@endif
