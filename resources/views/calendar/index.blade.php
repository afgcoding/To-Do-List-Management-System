@extends('layouts.app')
@php $pageTitle = 'Calendar'; @endphp

@section('content')
<div
    class="space-y-6"
    x-data="{
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
        async dropOn(date) {
            const task = this.tasks.find((item) => item.id === this.draggingId);
            this.draggingId = null;
            if (! task || ! task.canMove || task.due === date) {
                return;
            }
            const previous = task.due;
            task.due = date;
            this.error = '';
            try {
                const response = await fetch(@js(url('/calendar/tasks')) + '/' + task.id, {
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
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Calendar</h1>
            <p class="mt-1 text-sm text-slate-500">Drag a task onto a day to reschedule its due date. Existing reminders and activity logging stay in place.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ $previousUrl }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Previous</a>
            <p class="min-w-36 text-center text-sm font-semibold text-slate-800">{{ $cursor->format('F Y') }}</p>
            <a href="{{ $nextUrl }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Next</a>
        </div>
    </div>

    <p x-show="error" x-text="error" class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700" x-cloak></p>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <section class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm lg:col-span-1">
            <h2 class="text-sm font-semibold text-slate-900">Unscheduled</h2>
            <p class="mt-1 text-xs text-slate-400">Drop onto a day to set a deadline.</p>
            <div class="mt-3 min-h-24 space-y-2">
                <template x-for="task in unscheduled()" :key="task.id">
                    <button type="button" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-left text-sm"
                        :draggable="task.canMove"
                        @dragstart="startDrag(task); $event.dataTransfer.setData('text/plain', String(task.id))"
                        @dragend="draggingId = null">
                        <span class="block truncate font-medium text-slate-800" x-text="task.title"></span>
                        <span class="text-[11px] text-slate-400" x-text="task.priorityLabel"></span>
                    </button>
                </template>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm lg:col-span-3">
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
                        @drop.prevent="dropOn(@js($dateKey))"
                    >
                        <p class="mb-1 text-[11px] font-semibold {{ $inMonth ? 'text-slate-600' : 'text-slate-300' }}">{{ $day->day }}</p>
                        <div class="space-y-1">
                            <template x-for="task in tasksOn(@js($dateKey))" :key="task.id">
                                <a :href="task.url" class="block truncate rounded bg-indigo-50 px-1.5 py-1 text-[11px] font-medium text-indigo-800"
                                    :draggable="task.canMove"
                                    @dragstart.stop="startDrag(task); $event.dataTransfer.setData('text/plain', String(task.id))"
                                    x-text="task.title"></a>
                            </template>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
