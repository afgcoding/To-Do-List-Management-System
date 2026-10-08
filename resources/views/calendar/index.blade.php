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
        didDrag: false,
        error: '',
        popover: null,
        quickViewOpen: false,
        quickView: null,
        popoverStyle: '',
        openedBy: null,
        overTask: false,
        overPopover: false,
        hoverCloseTimer: null,
        keyboardOpen: false,
        createOpen: false,
        createDate: '',
        createTitle: '',
        createPriority: 'medium',
        createAssigneeIds: [],
        createSaving: false,
        canCreateTask: {{ $canCreateTask ? 'true' : 'false' }},
        calendarUsers: {{ \Illuminate\Support\Js::from($calendarUsers) }},
        storeUrl: @js(route('calendar.tasks.store')),
        csrf: document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').getAttribute('content') : '',
        statusOptions: {{ \Illuminate\Support\Js::from(collect(\App\Enums\TaskStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])) }},
        priorityOptions: {{ \Illuminate\Support\Js::from(collect(\App\Enums\TaskPriority::cases())->map(fn ($priority) => ['value' => $priority->value, 'label' => $priority->label()])) }},
        statusUrl: @js(route('tasks.status.update', ['task' => 0])),
        priorityUrl: @js(route('tasks.priority.update', ['task' => 0])),
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
            this.didDrag = true;
            this.draggingId = task.id;
        },
        jsEvent(info) {
            return (info && info.jsEvent) ? info.jsEvent : info;
        },
        anchorFrom(info) {
            if (! info) {
                return null;
            }
            if (info.el) {
                return info.el;
            }
            return info.currentTarget || info.target || null;
        },
        isMobile() {
            return window.matchMedia('(max-width: 639px)').matches;
        },
        previewTask(task, info, openedBy) {
            if (this.didDrag || this.draggingId) {
                return;
            }
            this.clearHoverTimer();
            this.createOpen = false;
            this.createTitle = '';
            this.createAssigneeIds = [];
            this.keyboardOpen = false;
            this.quickView = task;
            this.quickViewOpen = true;
            this.popover = 'preview';
            this.openedBy = openedBy || 'click';
            this.$nextTick(() => this.anchorPopover(this.anchorFrom(info)));
        },
        closeAll() {
            this.clearHoverTimer();
            this.popover = null;
            this.quickViewOpen = false;
            this.quickView = null;
            this.createOpen = false;
            this.createTitle = '';
            this.createAssigneeIds = [];
            this.createSaving = false;
            this.popoverStyle = '';
            this.openedBy = null;
            this.overTask = false;
            this.overPopover = false;
            this.keyboardOpen = false;
        },
        closeQuickView() {
            this.closeAll();
        },
        closeCreate() {
            this.closeAll();
        },
        applyPopoverStyle(top, left) {
            this.popoverStyle = 'top: ' + Math.round(top) + 'px; left: ' + Math.round(left) + 'px; position: fixed; z-index: 9999; pointer-events: auto;';
        },
        clearHoverTimer() {
            if (this.hoverCloseTimer) {
                clearTimeout(this.hoverCloseTimer);
                this.hoverCloseTimer = null;
            }
        },
        scheduleHoverClose() {
            this.clearHoverTimer();
            this.hoverCloseTimer = setTimeout(() => {
                if (this.openedBy === 'hover' && ! this.overTask && ! this.overPopover) {
                    this.closeAll();
                }
            }, 120);
        },
        sidebarMinLeft() {
            const drawer = document.querySelector('.bmd-layout-drawer');
            if (! drawer) {
                return 8;
            }
            const rect = drawer.getBoundingClientRect();
            if (rect.width <= 0 || rect.right > window.innerWidth / 2) {
                return 8;
            }
            return Math.round(rect.right) + 8;
        },
        anchorPopover(el) {
            if (! el || this.isMobile()) {
                this.popoverStyle = '';
                return;
            }
            const info = { el };
            const rect = info.el.getBoundingClientRect();
            const width = 280;
            const minLeft = this.sidebarMinLeft();
            let left = rect.right + 10;
            let top = rect.top;
            if (left + width > window.innerWidth - 8) {
                left = rect.left - 290;
            }
            if (left < minLeft) {
                left = minLeft;
            }
            if (left + width > window.innerWidth - 8) {
                left = Math.max(minLeft, window.innerWidth - width - 8);
            }
            this.applyPopoverStyle(top, left);
            this.$nextTick(() => {
                const popover = this.$refs.calendarPopover;
                const height = (popover && popover.getBoundingClientRect().height) || 260;
                if (top + height > window.innerHeight - 8) {
                    top = Math.max(8, rect.top - height);
                    if (top + height > window.innerHeight - 8) {
                        top = Math.max(8, window.innerHeight - height - 8);
                    }
                    this.applyPopoverStyle(top, left);
                }
            });
        },
        eventMouseEnter(task, info) {
            if (this.isMobile() || this.popover === 'create') {
                return;
            }
            if (task && task.status === 'in_progress') {
                this.overTask = true;
                this.clearHoverTimer();
                this.previewTask(task, info, 'hover');
            }
        },
        eventMouseLeave() {
            this.overTask = false;
            this.scheduleHoverClose();
        },
        popoverMouseEnter() {
            this.overPopover = true;
            this.clearHoverTimer();
        },
        popoverMouseLeave() {
            this.overPopover = false;
            this.scheduleHoverClose();
        },
        eventClick(task, info) {
            const jsEvent = this.jsEvent(info);
            if (! jsEvent) {
                return;
            }
            if (jsEvent.metaKey || jsEvent.ctrlKey || jsEvent.shiftKey || jsEvent.button === 1) {
                return;
            }
            jsEvent.preventDefault();
            jsEvent.stopPropagation();
            if (this.didDrag) {
                this.didDrag = false;
                return;
            }
            this.overTask = false;
            this.previewTask(task, info, 'click');
        },
        handleEventEnter(task, info) {
            this.eventMouseEnter(task, info);
        },
        handleEventClick(task, info) {
            this.eventClick(task, info);
        },
        openCreate(event, date) {
            if (! this.canCreateTask) {
                return;
            }
            const jsEvent = this.jsEvent(event);
            if (jsEvent) {
                jsEvent.stopPropagation();
            }
            if (this.didDrag) {
                this.didDrag = false;
                return;
            }
            this.quickViewOpen = false;
            this.quickView = null;
            this.createDate = date;
            this.createTitle = '';
            this.createPriority = 'medium';
            this.createAssigneeIds = [];
            this.createOpen = true;
            this.popover = 'create';
            this.openedBy = 'create';
            this.keyboardOpen = false;
            this.$nextTick(() => this.anchorPopover(this.anchorFrom(event)));
        },
        toggleAssignee(id) {
            const value = Number(id);
            if (this.createAssigneeIds.includes(value)) {
                this.createAssigneeIds = this.createAssigneeIds.filter((item) => item !== value);
            } else {
                this.createAssigneeIds = [...this.createAssigneeIds, value];
            }
        },
        async saveQuickCreate() {
            const title = this.createTitle.trim();
            if (! title || this.createSaving) {
                return;
            }
            this.createSaving = true;
            this.error = '';
            try {
                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        title,
                        due_date: this.createDate,
                        priority: this.createPriority,
                        assigned_users: this.createAssigneeIds,
                    }),
                });
                const payload = await response.json().catch(() => ({}));
                if (! response.ok) {
                    this.error = payload.message || (payload.errors && payload.errors.title && payload.errors.title[0]) || 'Could not create the task.';
                    return;
                }
                this.tasks.push(payload.task);
                this.closeCreate();
            } catch (error) {
                this.error = 'Could not create the task.';
            } finally {
                this.createSaving = false;
            }
        },
        optionLabel(options, value) {
            const match = options.find((item) => item.value === value);
            return match ? match.label : value;
        },
        async saveQuickField(field, value) {
            const task = this.quickView;
            if (! task) {
                return;
            }
            if (field === 'priority' && ! task.canUpdatePriority) {
                return;
            }
            const previous = task[field];
            const previousLabel = field === 'status' ? task.statusLabel : task.priorityLabel;
            task[field] = value;
            if (field === 'status') {
                task.statusLabel = this.optionLabel(this.statusOptions, value);
            } else {
                task.priorityLabel = this.optionLabel(this.priorityOptions, value);
            }
            const template = field === 'status' ? this.statusUrl : this.priorityUrl;
            try {
                const response = await fetch(template.replace(/\/0\//, '/' + task.id + '/'), {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(field === 'status' ? { status: value } : { priority: value }),
                });
                if (! response.ok) {
                    task[field] = previous;
                    if (field === 'status') {
                        task.statusLabel = previousLabel;
                    } else {
                        task.priorityLabel = previousLabel;
                    }
                    this.error = 'Could not update the task.';
                }
            } catch (error) {
                task[field] = previous;
                if (field === 'status') {
                    task.statusLabel = previousLabel;
                } else {
                    task.priorityLabel = previousLabel;
                }
                this.error = 'Could not update the task.';
            }
        },
        statusClass(task) {
            if (! task) {
                return 'status-todo';
            }
            return 'status-' + (task.status || 'todo');
        },
        assigneeInitials(name) {
            const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            if (! parts.length) {
                return '?';
            }
            return (parts[0].charAt(0) + (parts[1] ? parts[1].charAt(0) : '')).toUpperCase();
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
        <div class="calendar-month-nav">
            <a href="{{ $previousUrl }}" class="btn btn-sm btn-secondary">Previous</a>
            <p class="calendar-month-label">{{ $cursor->format('F Y') }}</p>
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
                            @dragend="draggingId = null"
                            @mouseenter="eventMouseEnter(task, $event)"
                            @mouseleave="eventMouseLeave()"
                            @click="eventClick(task, $event)">
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
                        class="calendar-day-cell min-h-28 bg-white p-1.5 {{ $inMonth ? '' : 'bg-slate-50' }} {{ $day->isToday() ? 'ring-1 ring-inset ring-indigo-200' : '' }}"
                        @dragover.prevent
                        @drop.prevent="dropOn(@js($dateKey), $event)"
                        @click="openCreate($event, @js($dateKey))"
                    >
                        <div class="mb-1 flex items-center justify-between gap-1">
                            <p class="text-[11px] font-semibold {{ $inMonth ? 'text-slate-600' : 'text-slate-300' }}">{{ $day->day }}</p>
                            @if ($canCreateTask)
                                <button type="button" class="calendar-day-add" aria-label="Add task on {{ $dateKey }}" @click="openCreate($event, @js($dateKey))">+</button>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <template x-for="task in tasksOn(@js($dateKey))" :key="task.id">
                                <a :href="task.url" class="fc-event block truncate rounded-md border px-1.5 py-1 text-[11px] font-medium"
                                    :class="statusClass(task)"
                                    :draggable="task.canMove"
                                    @dragstart.stop="startDrag(task); $event.dataTransfer.setData('text/plain', String(task.id))"
                                    @mouseenter="eventMouseEnter(task, $event)"
                                    @mouseleave="eventMouseLeave()"
                                    @click.stop="eventClick(task, $event)"
                                    x-text="task.title"></a>
                            </template>
                        </div>
                    </div>
                @endforeach
            </div>
            </div>
        </section>
    </div>

    <template x-teleport="body">
        <div
            x-show.important="popover"
            x-cloak
            class="nozha-calendar-popover-root"
            :class="{ 'is-hover': openedBy === 'hover', 'is-sheet': isMobile() }"
            @keydown.escape.window="closeAll()"
        >
            <div class="nozha-calendar-popover-backdrop" @click="closeAll()"></div>
            <div
                x-ref="calendarPopover"
                class="nozha-calendar-popover"
                :class="[(popover === 'preview' ? statusClass(quickView) : 'status-todo'), keyboardOpen ? 'is-keyboard' : '']"
                :style="popoverStyle"
                role="dialog"
                aria-modal="true"
                @click.stop
                @mouseenter="popoverMouseEnter()"
                @mouseleave="popoverMouseLeave()"
            >
                <div x-show="popover === 'preview' && quickView" class="calendar-preview-body">
                    <div class="calendar-preview-head">
                        <div class="flex min-w-0 items-center gap-2">
                            <h2 class="calendar-preview-title min-w-0 truncate text-sm font-bold text-slate-900" x-text="quickView?.title"></h2>
                            <span class="calendar-preview-pill shrink-0" :class="statusClass(quickView)" x-text="quickView?.statusLabel"></span>
                        </div>
                        <button type="button" class="nozha-calendar-popover-close flex h-7 w-7 items-center justify-center rounded-full text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600" @click.stop="closeAll()" aria-label="Close preview">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="calendar-preview-grid">
                        <div class="min-w-0">
                            <p class="calendar-preview-label text-[10px] font-semibold uppercase tracking-wider text-slate-400">Team</p>
                            <div class="calendar-preview-avatars" x-show="quickView?.assignees && quickView.assignees.length">
                                <template x-for="user in (quickView?.assignees || [])" :key="user.id">
                                    <span class="calendar-preview-avatar" :title="user.name" x-text="assigneeInitials(user.name)"></span>
                                </template>
                            </div>
                            <p class="text-xs font-medium text-slate-700" x-show="! quickView?.assignees || ! quickView.assignees.length">Unassigned</p>
                        </div>
                        <div class="min-w-0">
                            <p class="calendar-preview-label text-[10px] font-semibold uppercase tracking-wider text-slate-400">Start</p>
                            <p class="truncate text-xs font-medium text-slate-700" x-text="quickView?.startLabel"></p>
                        </div>
                        <div class="min-w-0">
                            <p class="calendar-preview-label text-[10px] font-semibold uppercase tracking-wider text-slate-400">Due</p>
                            <p class="truncate text-xs font-medium text-slate-700" x-text="quickView?.dueLabel"></p>
                        </div>
                        <div class="min-w-0">
                            <p class="calendar-preview-label text-[10px] font-semibold uppercase tracking-wider text-slate-400">Progress</p>
                            <p class="text-xs font-medium text-slate-700"><span x-text="quickView?.completedSubtasks || 0"></span>/<span x-text="quickView?.subtasksCount || 0"></span></p>
                        </div>
                    </div>
                    <div class="progress nano">
                        <div class="progress-bar bg-success" :style="'width:' + (quickView?.progress || 0) + '%'"></div>
                    </div>
                    <div class="my-2.5 border-t border-slate-100"></div>
                    <div class="calendar-preview-grid">
                        <div class="min-w-0">
                            <label class="calendar-preview-label text-[10px] font-semibold uppercase tracking-wider text-slate-400">Status</label>
                            <select class="form-control nozha-calendar-popover-select" :value="quickView?.status" :disabled="! quickView?.canUpdateStatus" @change="saveQuickField('status', $event.target.value)">
                                <template x-for="option in statusOptions" :key="option.value">
                                    <option :value="option.value" :selected="option.value === quickView?.status" x-text="option.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="min-w-0">
                            <label class="calendar-preview-label text-[10px] font-semibold uppercase tracking-wider text-slate-400">Priority</label>
                            <select class="form-control nozha-calendar-popover-select" :class="! quickView?.canUpdatePriority ? 'cursor-not-allowed bg-slate-100 opacity-75' : ''" :value="quickView?.priority" :disabled="! quickView?.canUpdatePriority" @change="saveQuickField('priority', $event.target.value)">
                                <template x-for="option in priorityOptions" :key="option.value">
                                    <option :value="option.value" :selected="option.value === quickView?.priority" x-text="option.label"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400" x-show="! quickView?.canUpdatePriority">Only admins can change priority.</p>
                        </div>
                    </div>
                    <div class="calendar-preview-actions">
                        <a :href="quickView?.url" class="btn btn-primary btn-sm">View Full Details</a>
                    </div>
                </div>

                <div x-show="popover === 'create'" class="calendar-create-popover">
                    <div class="calendar-preview-head">
                        <span class="calendar-date-badge" x-text="createDate"></span>
                        <button type="button" class="nozha-calendar-popover-close flex h-7 w-7 items-center justify-center rounded-full text-slate-400 transition-all hover:bg-slate-100 hover:text-slate-600" @click.stop="closeAll()" aria-label="Close create">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <input type="text" class="form-control nozha-calendar-popover-input" placeholder="Task title" x-model="createTitle" @focus="keyboardOpen = true" @blur="keyboardOpen = false" @keydown.enter.prevent="saveQuickCreate()">
                    <div>
                        <p class="calendar-preview-label">Priority</p>
                        <div class="calendar-icon-toggles">
                            <template x-for="option in priorityOptions" :key="option.value">
                                <button type="button" class="btn btn-sm" :class="createPriority === option.value ? 'btn-primary' : 'btn-light'" @click="createPriority = option.value" x-text="option.label"></button>
                            </template>
                        </div>
                    </div>
                    <div>
                        <p class="calendar-preview-label">Assignees</p>
                        <div class="calendar-icon-toggles">
                            <template x-for="user in calendarUsers" :key="user.id">
                                <button type="button" class="calendar-assignee-chip" :class="createAssigneeIds.includes(Number(user.id)) ? 'is-active' : ''" :title="user.name" @click="toggleAssignee(user.id)" x-text="user.name.charAt(0).toUpperCase()"></button>
                            </template>
                        </div>
                    </div>
                    <div class="calendar-preview-actions">
                        <button type="button" class="btn btn-light btn-sm" @click.stop="closeAll()">Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" :disabled="createSaving" @click="saveQuickCreate()">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
