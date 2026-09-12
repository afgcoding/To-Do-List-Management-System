{{-- Status pill (To Do, In Progress, Completed, Cancelled) --}}
@props(['status'])
@php
    $status = $status instanceof \App\Enums\TaskStatus
        ? $status
        : \App\Enums\TaskStatus::from((string) $status);
@endphp
<x-badge :tone="$status->tone()" {{ $attributes }}>{{ $status->label() }}</x-badge>
