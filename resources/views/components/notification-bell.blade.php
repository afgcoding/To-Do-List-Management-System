@php
    $unreadCount = $unreadCount ?? 0;
    $notifications = $notifications ?? [];
@endphp

<div
    class="relative"
    x-data="{
        open: false,
        unreadCount: {{ (int) $unreadCount }},
        notifications: {{ Js::from($notifications) }},
        feedUrl: @js($feedUrl),
        readAllUrl: @js($readAllUrl),
        clearUrl: @js($clearUrl),
        readUrlTemplate: @js($readUrlTemplate),
        hydrated: false,
        csrf() {
            return document.querySelector('meta[name=csrf-token]')?.getAttribute('content')
        },
        async requestPush() {
            if (! ('Notification' in window) || Notification.permission !== 'default') {
                return
            }
            await Notification.requestPermission()
        },
        showPush(item) {
            if (! ('Notification' in window) || Notification.permission !== 'granted' || ! item) {
                return
            }
            new Notification(item.title, { body: item.message, tag: String(item.id) })
        },
        async refresh() {
            const response = await fetch(this.feedUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
            if (! response.ok) {
                return
            }
            const data = await response.json()
            if (this.hydrated && data.unread_count > this.unreadCount && data.notifications?.[0]) {
                this.showPush(data.notifications[0])
            }
            this.unreadCount = data.unread_count
            this.notifications = data.notifications
            this.hydrated = true
        },
        async post(url) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrf(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({}),
            })
            if (! response.ok) {
                return
            }
            const data = await response.json()
            this.unreadCount = data.unread_count
            this.notifications = data.notifications
        },
        markRead(item) {
            this.post(this.readUrlTemplate.replace('__ID__', item.id))
        },
        markAllRead() {
            this.post(this.readAllUrl)
        },
        async clearAll() {
            const response = await fetch(this.clearUrl, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrf(),
                },
                credentials: 'same-origin',
            })
            if (! response.ok) {
                return
            }
            const data = await response.json()
            this.unreadCount = data.unread_count
            this.notifications = data.notifications
        },
        openMenu() {
            this.open = ! this.open
            if (this.open) {
                this.requestPush()
            }
        },
        init() {
            this.refresh()
            setInterval(() => this.refresh(), 25000)
        }
    }"
>
    <button type="button" @click="openMenu()" class="relative rounded-lg p-2 text-slate-500 transition hover:bg-slate-100" aria-label="Notifications">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unreadCount > 0" x-cloak class="absolute -right-0.5 -top-0.5 inline-flex min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold leading-4 text-white ring-2 ring-white" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
    </button>

    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        class="fixed inset-x-3 top-16 z-50 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg sm:absolute sm:inset-x-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-96">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-3 py-2">
            <p class="text-sm font-semibold text-slate-800">Notifications</p>
            <div class="flex items-center gap-2">
                <button type="button" class="text-xs font-medium text-slate-500 hover:text-slate-800" @click="markAllRead()">Mark as read</button>
                <button type="button" class="text-xs font-medium text-rose-600 hover:text-rose-700" @click="clearAll()">Clear all</button>
            </div>
        </div>
        <div class="max-h-96 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <p class="px-3 py-6 text-center text-sm text-slate-500">You are all caught up.</p>
            </template>
            <template x-for="item in notifications" :key="item.id">
                <a :href="item.url" class="flex gap-3 border-b border-slate-50 px-3 py-3 hover:bg-slate-50" :class="item.read_at ? 'opacity-70' : ''" @click="markRead(item)">
                    <img x-show="item.actor_avatar" :src="item.actor_avatar" alt="" class="size-8 rounded-full object-cover">
                    <span x-show="!item.actor_avatar" class="grid size-8 shrink-0 place-items-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700" x-text="(item.actor_name || 'N').charAt(0)"></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-slate-800" x-text="item.title"></span>
                        <span class="mt-0.5 block text-xs text-slate-500" x-text="item.message"></span>
                        <span class="mt-1 block text-[11px] text-slate-400" x-text="item.created_at"></span>
                    </span>
                </a>
            </template>
        </div>
    </div>
</div>
