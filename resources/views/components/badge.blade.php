@props(['tone' => 'slate'])
@php
    $tones = [
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-700',
        'indigo' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
        'violet' => 'border-violet-200 bg-violet-50 text-violet-700',
        'slate' => 'border-slate-200 bg-slate-100 text-slate-600',
        'blue' => 'border-blue-200 bg-blue-50 text-blue-700',
    ];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold '.$tones[$tone]]) }}>{{ $slot }}</span>
