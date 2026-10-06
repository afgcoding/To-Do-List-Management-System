@php
    $path = $path ?? '/';
    $active = request()->is(ltrim($path, '/') . '*') || ($path === '/' && request()->is('/'));
    $iconClass = $iconClass ?? 'fas fa-circle';
@endphp
<li class="side-item{{ $active ? ' selected' : '' }}">
    <a href="{{ url($path) }}" class="px-3 py-2"><i class="{{ $iconClass }}"></i><span>{{ $label }}</span></a>
</li>
