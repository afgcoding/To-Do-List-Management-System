{{-- Simple color field: presets, native picker, HEX text, and a live badge. Expects Alpine `color` and `name`. --}}
@php
    $presets = [
        'Red' => '#EF4444',
        'Blue' => '#3B82F6',
        'Green' => '#22C55E',
        'Yellow' => '#EAB308',
        'Indigo' => '#6366F1',
        'Purple' => '#A855F7',
    ];
@endphp

<div class="space-y-2">
    <label class="block text-xs font-semibold text-slate-700">Color</label>

    <div class="flex flex-wrap gap-2">
        @foreach ($presets as $label => $hex)
            <button type="button" @click="color = '{{ $hex }}'" title="{{ $label }}"
                class="size-8 rounded-full border border-slate-200 shadow-sm"
                :class="color === '{{ $hex }}' ? 'ring-2 ring-indigo-500 ring-offset-2' : ''"
                style="background-color: {{ $hex }}"></button>
        @endforeach
    </div>

    <div class="flex items-center gap-3">
        <input type="color" x-model="color"
            class="h-11 w-14 cursor-pointer rounded-xl border border-slate-300 bg-white p-1 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
        <input type="text" name="color" x-model="color" maxlength="7" placeholder="#6366F1"
            class="w-32 rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 font-mono text-sm uppercase text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
    </div>

    <p class="text-xs text-slate-500">Pick a preset or enter a HEX code such as #EF4444.</p>
    <x-input-error :messages="$errors->get('color')" />

    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
        <p class="mb-1.5 text-xs font-semibold text-slate-500">Preview</p>
        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold text-white"
            :style="'background-color: ' + (color || '#64748B')"
            x-text="name || 'Preview'"></span>
    </div>
</div>
