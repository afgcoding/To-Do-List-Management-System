<header
    class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
    <div class="min-w-0">
        <p class="text-xs font-medium text-slate-500">{{ setting('company_name', 'Workspace') }} / {{ $pageTitle ?? 'Dashboard' }}</p>
        <h1 class="truncate text-sm font-semibold text-slate-800">{{ $pageTitle ?? 'Dashboard' }}</h1>
    </div>
    <div class="hidden max-w-md flex-1 md:block"><label class="relative block"><span class="sr-only">Quick
                search</span><input data-quick-search type="search" placeholder="Search tasks, people, or projects..."
                class="w-full rounded-lg border border-slate-300 py-2 pl-3 pr-16 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"><kbd
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-medium text-slate-500">Ctrl
                K</kbd></label></div>
    <div class="flex items-center gap-2"><button type="button"
            class="relative rounded-lg p-2 text-slate-500 transition hover:bg-slate-100"
            aria-label="Notifications">🔔<span
                class="absolute right-1 top-1 size-2 rounded-full bg-rose-500 ring-2 ring-white"></span></button><a
            href="{{ url('/tasks/create') }}"
            class="hidden items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 sm:inline-flex">+
            Task</a><button type="button"
            class="flex items-center gap-2 rounded-lg p-1 transition hover:bg-slate-100"><span
                class="grid size-8 place-items-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">{{ strtoupper(substr(auth()->user()?->name ?? 'Admin', 0, 1)) }}</span><span
                class="hidden text-left sm:block"><span
                    class="block text-xs font-semibold text-slate-700">{{ auth()->user()?->name ?? 'Administrator' }}</span><span
                    class="block text-[11px] text-slate-500">View profile</span></span></button></div>
</header>