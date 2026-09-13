@php($canToggle = auth()->user()?->can('toggleStatus', $user) ?? false)
<div class="inline-flex items-center gap-2.5">
    <button
        type="button"
        role="switch"
        @if ($canToggle)
            @click="toggleStatus({{ $user->id }})"
            :disabled="row({{ $user->id }})?.busy"
        @else
            disabled
        @endif
        :aria-checked="row({{ $user->id }})?.isActive ? 'true' : 'false'"
        :aria-label="row({{ $user->id }})?.isActive ? 'Set status to Inactive' : 'Set status to Active'"
        @class([
            'relative inline-flex h-6 w-11 shrink-0 rounded-full border-0 p-0.5 shadow-inner transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2',
            $user->isActive() ? 'bg-emerald-500' : 'bg-rose-500',
            $canToggle ? 'cursor-pointer' : 'cursor-not-allowed opacity-60',
        ])
        :class="row({{ $user->id }})?.isActive ? 'bg-emerald-500' : 'bg-rose-500'">
        <span
            @class([
                'pointer-events-none inline-flex size-5 items-center justify-center rounded-full bg-white shadow transition duration-200',
                $user->isActive() ? 'translate-x-5' : 'translate-x-0',
            ])
            :class="{
                'translate-x-5': row({{ $user->id }})?.isActive,
                'translate-x-0': ! row({{ $user->id }})?.isActive,
                'animate-pulse': row({{ $user->id }})?.busy,
            }">
            <svg x-show="row({{ $user->id }})?.busy" x-cloak class="size-3 animate-spin text-slate-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"></path>
            </svg>
        </span>
    </button>
    <span
        @class([
            'text-sm font-medium',
            $user->isActive() ? 'text-emerald-600' : 'text-rose-600',
        ])
        :class="row({{ $user->id }})?.isActive ? 'text-emerald-600' : 'text-rose-600'"
        x-text="row({{ $user->id }})?.isActive ? 'Active' : 'Inactive'">{{ $user->isActive() ? 'Active' : 'Inactive' }}</span>
</div>
