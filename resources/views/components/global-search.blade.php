@php
    $searchUrl = route('search.global');
@endphp

<div
    class="nozha-global-search w-100"
    x-data="{
        url: @js($searchUrl),
        query: '',
        open: false,
        loading: false,
        timer: null,
        activeIndex: 0,
        panelStyle: '',
        results: { tasks: [], users: [], tags: [] },
        get items() {
            return [...this.results.tasks, ...this.results.users, ...this.results.tags]
        },
        get hasResults() {
            return this.items.length > 0
        },
        close() {
            this.open = false
            this.activeIndex = 0
        },
        place() {
            const input = this.$refs.input
            if (! input) {
                return
            }
            const rect = input.getBoundingClientRect()
            const width = Math.min(360, window.innerWidth - 24)
            let left = rect.left
            if (left + width > window.innerWidth - 12) {
                left = Math.max(12, window.innerWidth - width - 12)
            }
            this.panelStyle = 'position:fixed;top:' + (rect.bottom + 8) + 'px;left:' + left + 'px;width:' + width + 'px;z-index:9999;'
        },
        onFocus() {
            if (this.query.trim() !== '') {
                this.open = true
                this.place()
            }
        },
        onOutside(event) {
            if (this.$refs.input?.contains(event.target) || this.$el.contains(event.target)) {
                return
            }
            this.close()
        },
        scheduleSearch() {
            clearTimeout(this.timer)
            this.timer = setTimeout(() => this.search(), 250)
        },
        async search() {
            const q = this.query.trim()
            this.place()
            if (q === '') {
                this.results = { tasks: [], users: [], tags: [] }
                this.open = false
                this.loading = false
                return
            }
            this.loading = true
            this.open = true
            const response = await fetch(this.url + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
            if (! response.ok) {
                this.loading = false
                return
            }
            this.results = await response.json()
            this.activeIndex = 0
            this.loading = false
            this.$nextTick(() => this.place())
        },
        move(step) {
            if (! this.hasResults) {
                return
            }
            const total = this.items.length
            this.activeIndex = (this.activeIndex + step + total) % total
        },
        go() {
            const item = this.items[this.activeIndex]
            if (item?.url) {
                window.location.href = item.url
            }
        }
    }"
    @keydown.escape.window="close()"
    @keydown.arrow-down.prevent="move(1)"
    @keydown.arrow-up.prevent="move(-1)"
>
    <label class="mb-0 w-100 nozha-quick-search">
        <span class="sr-only">Quick search</span>
        <input
            data-quick-search
            x-ref="input"
            type="search"
            placeholder="Search..."
            class="form-control app-navbar-search"
            aria-label="Search tasks, people, or projects"
            autocomplete="off"
            x-model="query"
            @input="scheduleSearch()"
            @focus="onFocus()"
            @keydown.enter.prevent="go()"
        >
    </label>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-ref="panel"
            @click.outside="onOutside($event)"
            class="nozha-global-search-panel z-[9999] mt-2 max-h-[380px] w-[360px] overflow-y-auto rounded-2xl border border-slate-100 bg-white shadow-2xl"
            :style="panelStyle"
            role="listbox"
        >
            <p x-show="loading" class="px-4 py-3 text-sm text-slate-500">Searching…</p>
            <p x-show="! loading && ! hasResults" class="px-4 py-3 text-sm text-slate-500">No matches found.</p>

            <template x-if="! loading && results.tasks.length">
                <div class="py-1">
                    <p class="px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">📌 Tasks</p>
                    <template x-for="(task, index) in results.tasks" :key="'task-' + task.id">
                        <a
                            :href="task.url"
                            class="flex items-center justify-between gap-2 px-4 py-2 text-sm hover:bg-slate-50"
                            :class="activeIndex === index ? 'bg-slate-50' : ''"
                            @mouseenter="activeIndex = index"
                        >
                            <span class="min-w-0 truncate font-medium text-slate-800" x-text="task.title"></span>
                            <span class="flex shrink-0 items-center gap-1">
                                <span class="badge" x-text="task.status_label"></span>
                                <span class="text-xs text-slate-500" x-text="task.priority_label"></span>
                            </span>
                        </a>
                    </template>
                </div>
            </template>

            <template x-if="! loading && results.users.length">
                <div class="py-1">
                    <p class="px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">👥 Users/Team</p>
                    <template x-for="(user, index) in results.users" :key="'user-' + user.id">
                        <a
                            :href="user.url"
                            class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50"
                            :class="activeIndex === (results.tasks.length + index) ? 'bg-slate-50' : ''"
                            @mouseenter="activeIndex = results.tasks.length + index"
                        >
                            <img :src="user.avatar" alt="" class="size-8 rounded-full object-cover">
                            <span class="min-w-0 truncate font-medium text-slate-800" x-text="user.name"></span>
                        </a>
                    </template>
                </div>
            </template>

            <template x-if="! loading && results.tags.length">
                <div class="py-1">
                    <p class="px-4 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">🏷️ Tags</p>
                    <template x-for="(tag, index) in results.tags" :key="'tag-' + tag.id">
                        <a
                            :href="tag.url"
                            class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50"
                            :class="activeIndex === (results.tasks.length + results.users.length + index) ? 'bg-slate-50' : ''"
                            @mouseenter="activeIndex = results.tasks.length + results.users.length + index"
                        >
                            <span class="badge" x-text="tag.name"></span>
                        </a>
                    </template>
                </div>
            </template>
        </div>
    </template>
</div>
