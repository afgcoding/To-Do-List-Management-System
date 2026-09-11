@props(['label', 'color' => null])
@php
    $color = $color ?: '#64748B';
    $hex = ltrim($color, '#');
    $luminance = 160;
    if (strlen($hex) === 6) {
        $luminance = (hexdec(substr($hex, 0, 2)) * 299 + hexdec(substr($hex, 2, 2)) * 587 + hexdec(substr($hex, 4, 2)) * 114) / 1000;
    }
    $text = $luminance > 165 ? '#0f172a' : '#ffffff';
@endphp
{{-- Colored name pill for tags and categories --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold shadow-sm']) }}
    style="background-color: {{ $color }}; color: {{ $text }}">{{ $label }}</span>
