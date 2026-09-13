@if (session('success') || session('error'))
    <div class="fixed inset-x-4 bottom-5 z-50 mx-auto max-w-sm sm:inset-x-auto sm:right-5 sm:mx-0" role="status" aria-live="polite">
        <div @class(['rounded-xl border px-4 py-3 shadow-lg', 'border-emerald-200 bg-emerald-50 text-emerald-800' => session('success'), 'border-rose-200 bg-rose-50 text-rose-800' => session('error')])><p class="text-sm font-medium">{{ session('success') ?? session('error') }}</p></div>
    </div>
@endif
