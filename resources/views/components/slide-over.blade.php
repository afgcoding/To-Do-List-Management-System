@props(['open' => 'open'])

{{-- Simple right-side panel. Parent Alpine owns `open`. --}}
<div x-show="{{ $open }}" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-950/40" @click="{{ $open }} = false"></div>

    <div class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-xl"
        @click.stop
        x-show="{{ $open }}"
        x-transition:enter="transform transition duration-200 ease-out"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition duration-150 ease-in"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:px-6">
            <h2 class="min-w-0 truncate text-lg font-semibold text-slate-900">{{ $title }}</h2>
            <button type="button" @click="{{ $open }} = false" class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Close">×</button>
        </div>
        <div class="flex-1 overflow-y-auto px-4 pt-5 pb-8 sm:px-6">{{ $slot }}</div>
    </div>
</div>
