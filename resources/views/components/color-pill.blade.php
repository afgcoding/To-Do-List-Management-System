@props(['label', 'color' => null])
@php
    $color = $color ?: '#64748B';
    if (! str_starts_with($color, '#')) {
        $color = '#'.$color;
    }
@endphp
<span
    {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium']) }}
    style="background: {{ $color }}15; border: 1px solid {{ $color }}40; color: {{ $color }};">
    <span class="mr-1 inline-block h-1.5 w-1.5 rounded-full" style="background-color: {{ $color }}"></span>{{ $label }}
</span>
