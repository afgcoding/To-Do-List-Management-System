@props(['id', 'title' => 'Dialog'])
<div id="{{ $id }}" data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
    <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-slate-200 bg-white p-4 shadow-xl sm:p-6">
        <div class="mb-5 flex items-center justify-between gap-4">
            <h2 id="{{ $id }}-title" class="text-lg font-semibold text-slate-800">{{ $title }}</h2>
            <button type="button" data-modal-close="{{ $id }}" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Close dialog">×</button>
        </div>
        {{ $slot }}
    </div>
</div>
