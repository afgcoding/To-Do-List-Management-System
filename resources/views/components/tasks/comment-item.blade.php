@props(['comment', 'mentionNames', 'currentUserId'])

{{-- One comment in the discussion thread --}}
@php
    $isOwner = (int) $comment->user_id === (int) $currentUserId;
    $commentDate = format_date($comment->created_at);
@endphp
<article x-data="{ editing: false }" class="w-full overflow-hidden rounded-xl border border-slate-200/60 bg-white p-4 shadow-sm">
    <div class="flex min-w-0 items-start gap-3">
        <x-user-avatar :user="$comment->user" :name="$comment->user->name ?? 'User'" size="md" />
        <div class="w-full min-w-0 flex-1 overflow-hidden break-words">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <p class="text-sm font-semibold text-slate-800">{{ $comment->user->name ?? 'Unknown' }}</p>
                <time class="text-[11px] text-slate-400" datetime="{{ $comment->created_at?->toIso8601String() }}" title="{{ $commentDate }}">
                    {{ $comment->created_at?->diffForHumans() }}
                    @if ($commentDate)
                        · {{ $commentDate }}
                    @endif
                </time>
                @if ($comment->created_at?->ne($comment->updated_at))
                    <span class="text-[11px] italic text-slate-400">edited</span>
                @endif
                @if ($isOwner)
                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" @click="editing = !editing" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                        <form method="POST" action="{{ route('comments.destroy', $comment) }}" onsubmit="return confirm('Delete this comment?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-800">Delete</button>
                        </form>
                    </div>
                @endif
            </div>

            <div x-show="!editing" dir="auto" class="bidi-auto mt-2 w-full overflow-hidden break-words whitespace-pre-wrap text-sm leading-relaxed text-slate-700">
                {!! $comment->highlightedHtml($mentionNames) !!}
            </div>

            <form x-show="editing" x-cloak method="POST" action="{{ route('comments.update', $comment) }}" class="mt-3 space-y-2">
                @csrf
                @method('PATCH')
                <textarea name="comment" rows="3" required dir="auto"
                    class="bidi-auto w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">{{ $comment->comment }}</textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="editing = false" class="rounded-xl border border-slate-300 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600">Cancel</button>
                    <button class="rounded-xl bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">Save</button>
                </div>
            </form>

            @if ($comment->attachments->isNotEmpty())
                <div class="mt-1 flex w-full min-w-0 flex-col gap-1 overflow-hidden">
                    @foreach ($comment->attachments as $attachment)
                        <x-tasks.attachment-chip :attachment="$attachment" :can-delete="$isOwner && (int) $attachment->user_id === (int) $currentUserId" />
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</article>
