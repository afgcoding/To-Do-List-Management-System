@php($active = request()->is(ltrim($path, '/') . '*') || ($path === '/' && request()->is('/')))
<a href="{{ url($path) }}"
   @class(['flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition', 'bg-indigo-600 font-semibold text-white shadow-sm' => $active, 'hover:bg-slate-800 hover:text-white' => !$active])>
    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.75A2.25 2.25 0 0 1 6.25 4.5h11.5A2.25 2.25 0 0 1 20 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.25A2.25 2.25 0 0 1 4 17.25V6.75ZM8 9h8M8 13h5" />
    </svg>
    {{ $label }}
</a>
