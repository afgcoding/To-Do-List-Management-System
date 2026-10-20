{{-- Priority pill (Low, Medium, High, Urgent) --}}
@props(['priority'])
@php
    $priority = $priority instanceof \App\Enums\TaskPriority
        ? $priority
        : \App\Enums\TaskPriority::from((string) $priority);
@endphp
<x-badge :tone="$priority->tone()" {{ $attributes }}>{{ __($priority->label()) }}</x-badge>
