<header class="sticky top-0 z-30 w-full border-b border-slate-200/80 bg-white">
    <div class="flex w-full items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button
                type="button"
                class="shrink-0 rounded-lg p-2 text-slate-600 transition hover:bg-slate-100 lg:hidden"
                x-on:click="sidebarOpen = !sidebarOpen"
                :aria-expanded="sidebarOpen.toString()"
                aria-label="Open menu"
            >
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <div class="min-w-0">
                <p class="truncate text-xs font-medium text-slate-500">{{ setting('company_name', 'Workspace') }} / {{ $pageTitle ?? 'Dashboard' }}</p>
                <h1 class="truncate text-sm font-semibold text-slate-800">{{ $pageTitle ?? 'Dashboard' }}</h1>
            </div>
        </div>
        <div class="mx-4 hidden w-full max-w-md md:block">
            <label class="relative block">
                <span class="sr-only">Quick search</span>
                <input data-quick-search type="search" placeholder="Search..."
                    class="w-full rounded-lg border border-slate-300 py-2 pl-3 pr-3 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 sm:pr-16"
                    aria-label="Search tasks, people, or projects">
                <kbd class="absolute right-2 top-1/2 hidden -translate-y-1/2 rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-medium text-slate-500 sm:inline">Ctrl K</kbd>
            </label>
        </div>
        <div class="ml-auto flex items-center gap-3 sm:gap-4">
            <x-notification-bell />
            @can('create', App\Models\Task::class)
            <a href="{{ route('tasks.create') }}"
                class="hidden items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700 sm:inline-flex">+
                Task</a>
            @endcan
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-lg p-1 transition hover:bg-slate-100">
                    <x-user-avatar :user="auth()->user()" size="nav" />
                    <span class="hidden text-left lg:block">
                        <span class="block text-xs font-semibold text-slate-700">{{ auth()->user()?->name ?? 'Guest' }}</span>
                        <span class="block text-[11px] text-slate-500">{{ auth()->user()?->getRoleNames()->first() ?? auth()->user()?->role?->label() ?? 'Account' }}</span>
                    </span>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-30 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-3 py-2 text-left text-sm text-rose-600 hover:bg-rose-50">Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
