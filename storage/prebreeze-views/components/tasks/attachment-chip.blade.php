@props(['attachment', 'canDelete' => false])

@php
    $type = strtolower($attachment->file_type);
    $iconWrap = match ($type) {
        'pdf' => 'bg-rose-100 text-rose-600',
        'doc', 'docx' => 'bg-sky-100 text-sky-600',
        'xls', 'xlsx' => 'bg-emerald-100 text-emerald-600',
        'zip' => 'bg-amber-100 text-amber-600',
        default => 'bg-slate-200 text-slate-600',
    };
@endphp

{{-- Image thumbnail + lightbox, or a compact document row --}}
@if ($attachment->isImage())
    <div x-data="{ lightbox: false }" class="mt-3 w-[280px] max-w-[280px] overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
        <button type="button" @click="lightbox = true" class="block w-full">
            <img src="{{ $attachment->publicUrl() }}" alt="{{ $attachment->file_name }}"
                class="h-40 w-full cursor-pointer object-cover transition hover:opacity-90">
        </button>
        <div class="flex items-center justify-between gap-2 bg-slate-100/60 p-2 text-xs text-slate-500">
            <span class="max-w-[180px] truncate">{{ $attachment->file_name }}</span>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('attachments.download', $attachment) }}" class="text-slate-500 hover:text-indigo-600" title="Download">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12l4.5 4.5m0 0l4.5-4.5M12 16.5V3"/></svg>
                </a>
                @if ($canDelete)
                    <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" onsubmit="return confirm('Remove this file?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="font-medium text-rose-600 hover:text-rose-800">Remove</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Fullscreen image preview --}}
        <div x-show="lightbox" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4" @click="lightbox = false" @keydown.escape.window="lightbox = false">
            <img src="{{ $attachment->publicUrl() }}" alt="{{ $attachment->file_name }}" @click.stop
                class="max-h-[85vh] max-w-[min(90vw,56rem)] rounded-lg object-contain shadow-2xl">
        </div>
    </div>
@else
    <div class="mt-3 flex max-w-md items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-2.5 transition hover:bg-slate-100/80">
        <div class="flex min-w-0 items-center gap-2.5">
            <span class="grid size-9 shrink-0 place-items-center rounded-md text-[10px] font-bold uppercase {{ $iconWrap }}">{{ $type }}</span>
            <div class="min-w-0">
                <p class="max-w-[180px] truncate text-xs font-semibold text-slate-800">{{ $attachment->file_name }}</p>
                <p class="text-[11px] text-slate-500">{{ $attachment->formattedSize() }}</p>
            </div>
        </div>
        <div class="ml-2 flex shrink-0 items-center gap-2">
            <a href="{{ route('attachments.download', $attachment) }}" class="text-slate-500 hover:text-indigo-600" title="Download">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 12l4.5 4.5m0 0l4.5-4.5M12 16.5V3"/></svg>
            </a>
            @if ($canDelete)
                <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" onsubmit="return confirm('Remove this file?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-800">Remove</button>
                </form>
            @endif
        </div>
    </div>
@endif
