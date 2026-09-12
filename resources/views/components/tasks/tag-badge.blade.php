@props(['name'])
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md border border-slate-200/60 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200/50']) }}>#{{ ltrim((string) $name, '#') }}</span>
