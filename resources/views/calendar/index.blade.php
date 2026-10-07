@extends('layouts.app')
@php
    $pageTitle = 'Calendar';
    $hideLayoutPageHeader = true;
@endphp

@section('content')
<div
    class="space-y-6"
    x-data="{
        sidebarTab: 'unscheduled',
        tasks: {{ \Illuminate\Support\Js::from($calendarTasks) }},
        draggingId: null,
        error: '',
        csrf: document.querySelector('meta[name=csrf-token]').getAttribute('content'),
        tasksOn(date) {
            return this.tasks.filter((task) => task.due === date);
        },
        unscheduled() {
            return this.tasks.filter((task) => ! task.due);
        },
        startDrag(task) {
            if (! task.canMove) {
                return;
            }
            this.draggingId = task.id;
        },
        statusClass(task) {
            return 'status-' + (task.status || 'todo');
        },
        async dropOn(date, event) {
            const transferredId = event?.dataTransfer?.getData('text/plain');
            const rawId = transferredId || this.draggingId;
            this.draggingId = null;
            const task = this.tasks.find((item) => String(item.id) === String(rawId));
            if (! task || ! task.canMove || task.due === date) {
                return;
            }
            const previous = task.due;
            task.due = date;
            this.error = '';
            try {
                const response = await fetch(@js(route('calendar.tasks.due-date', ['task' => 0])).replace(/\/0$/, '/' + task.id), {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ due_date: date }),
                });
                if (! response.ok) {
                    task.due = previous;
                    const payload = await response.json().catch(() => ({}));
                    this.error = payload.message || (payload.errors && payload.errors.due_date && payload.errors.due_date[0]) || 'Could not update the deadline.';
                }
            } catch (error) {
                task.due = previous;
                this.error = 'Could not update the deadline.';
            }
        },
    }"
>
    <div class="mb-3">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 transition-colors hover:text-indigo-800">
            ← Back to Dashboard
        </a>
    </div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="workspace-title">Calendar</h1>
        <div class="header-actions">
            <a href="{{ $previousUrl }}" class="btn btn-sm btn-secondary">Previous</a>
            <p class="min-w-36 px-2 text-center text-sm font-semibold text-slate-800">{{ $cursor->format('F Y') }}</p>
            <a href="{{ $nextUrl }}" class="btn btn-sm btn-secondary">Next</a>
        </div>
    </div>

    <p x-show="error" x-text="error" class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700" x-cloak></p>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <section class="card flat workspace-card lg:col-span-1">
            <div class="calendar-sidebar-tabs">
                <button type="button" class="btn btn-sm" :class="sidebarTab === 'unscheduled' ? 'btn-primary' : 'btn-secondary'" @click="sidebarTab = 'unscheduled'">
                    Unscheduled
                </button>
                <button type="button" class="btn btn-sm" :class="sidebarTab === 'recurring' ? 'btn-primary' : 'btn-secondary'" @click="sidebarTab = 'recurring'">
                    Recurring Schedules
                </button>
            </div>

            <div x-show="sidebarTab === 'unscheduled'">
                <h2 class="text-sm font-semibold text-slate-900">Unscheduled</h2>
                <p class="mt-1 text-xs text-slate-400">Drop onto a day to set a deadline.</p>
                <div class="mt-3 min-h-24 space-y-2">
                    <template x-for="task in unscheduled()" :key="task.id">
                        <button type="button" class="unscheduled-card mb-2 w-full rounded-lg border p-3 text-left text-sm shadow-xs"
                            :class="statusClass(task)"
                            :draggable="task.canMove"
                            @dragstart="startDrag(task); $event.dataTransfer.setData('text/plain', String(task.id))"
                            @dragend="draggingId = null">
                            <span class="block truncate font-medium" x-text="task.title"></span>
                            <span class="text-[11px] opacity-80" x-text="task.priorityLabel"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div x-show="sidebarTab === 'recurring'" x-cloak>
                <h2 class="text-sm font-semibold text-slate-900">Recurring Schedules</h2>
                <p class="mt-1 text-xs text-slate-400">Active templates. These are not draggable.</p>
                <div class="mt-3 min-h-24 space-y-2">
                    @forelse ($recurringSchedules as $schedule)
                        @continue($schedule->task === null)
                        <a href="{{ route('tasks.show', $schedule->task) }}" class="unscheduled-card mb-2 block w-full rounded-lg border p-3 text-left text-sm shadow-xs status-{{ $schedule->task->status->value }}">
                            <span class="block truncate font-medium">{{ $schedule->task->title }}</span>
                            <span class="block text-[11px] opacity-80">{{ $schedule->frequencyLabel() }}</span>
                            <span class="block text-[11px] opacity-80">Next: {{ format_date($schedule->next_recurring_date) ?? '—' }}</span>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400">No active recurring schedules.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="card flat workspace-card overflow-x-auto lg:col-span-3">
            <div class="min-w-[600px] lg:min-w-0">
            <div class="grid grid-cols-7 gap-px rounded-lg bg-slate-200 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                <div class="bg-white py-2">Mon</div>
                <div class="bg-white py-2">Tue</div>
                <div class="bg-white py-2">Wed</div>
                <div class="bg-white py-2">Thu</div>
                <div class="bg-white py-2">Fri</div>
                <div class="bg-white py-2">Sat</div>
                <div class="bg-white py-2">Sun</div>
            </div>
            <div class="mt-px grid grid-cols-7 gap-px bg-slate-200">
                @foreach ($days as $day)
                    @php
                        $dateKey = $day->toDateString();
                        $inMonth = $day->isSameMonth($cursor);
                    @endphp
                    <div
                        class="min-h-28 bg-white p-1.5 {{ $inMonth ? '' : 'bg-slate-50' }} {{ $day->isToday() ? 'ring-1 ring-inset ring-indigo-200' : '' }}"
                        @dragover.prevent
                        @drop.prevent="dropOn(@js($dateKey), $event)"
                    >
                        <p class="mb-1 text-[11px] font-semibold {{ $inMonth ? 'text-slate-600' : 'text-slate-300' }}">{{ $day->day }}</p>
                        <div class="space-y-1">
                            <template x-for="task in tasksOn(@js($dateKey))" :key="task.id">
                                <a :href="task.url" class="fc-event block truncate rounded-md border px-1.5 py-1 text-[11px] font-medium"
                                    :class="statusClass(task)"
                                    :draggable="task.canMove"
                                    @dragstart.stop="startDrag(task); $event.dataTransfer.setData('text/plain', String(task.id))"
                                    x-text="task.title"></a>
                            </template>
                        </div>
                    </div>
                @endforeach
            </div>
            </div>
        </section>
    </div>
</div>
@endsection
