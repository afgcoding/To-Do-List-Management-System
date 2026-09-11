<aside class="hidden w-64 shrink-0 bg-slate-900 text-slate-300 lg:block">
    <div class="sticky top-0 flex min-h-screen flex-col p-4">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 px-2 py-2">
            @if (setting('logo'))
                <img src="{{ setting()->logoUrl() }}" alt=""
                    class="h-9 w-9 min-w-[36px] max-h-[36px] rounded-lg border border-slate-700/60 bg-slate-800 object-contain p-1 shadow-sm">
            @else
                <span class="flex h-9 w-9 min-w-[36px] max-h-[36px] items-center justify-center rounded-lg border border-slate-700/60 bg-slate-800 text-xs font-semibold text-slate-200 shadow-sm">✓</span>
            @endif
            <span class="min-w-0">
                <span dir="auto" class="bidi-auto block max-w-[150px] truncate text-sm font-semibold text-white">{{ setting('company_name', config('app.name', 'TaskFlow')) }}</span>
                <span class="block truncate text-xs font-normal text-slate-400">Enterprise workspace</span>
            </span>
        </a>

        @php($navigation = ['Main' => [['Dashboard', '/']], 'Task hub' => [['Tasks', '/tasks'], ['Recurring Tasks', '/recurring-tasks']], 'Organization' => [['Departments', '/departments'], ['Categories', '/categories'], ['Tags', '/tags']], 'Administration' => [['Users & Roles', '/users'], ['Activity Logs', '/activity-logs'], ['System Settings', '/system-settings']]])

        <nav class="mt-8 space-y-6" aria-label="Primary navigation">
            @foreach ($navigation as $group => $items)
                <div>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $group }}</p>
                    <div class="mt-2 space-y-1">
                        @foreach ($items as [$label, $path])
                            @php($active = request()->is(ltrim($path, '/') . '*') || ($path === '/' && request()->is('/')))
                            <a href="{{ url($path) }}" 
                               @class(['flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition', 'bg-indigo-600 font-semibold text-white shadow-sm' => $active, 'hover:bg-slate-800 hover:text-white' => !$active])>
                                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.75A2.25 2.25 0 0 1 6.25 4.5h11.5A2.25 2.25 0 0 1 20 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.25A2.25 2.25 0 0 1 4 17.25V6.75ZM8 9h8M8 13h5" />
                                </svg>
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="mt-auto rounded-xl border border-slate-700 bg-slate-800/70 p-3 text-xs leading-5 text-slate-400">
            Keep work moving with clear ownership, priorities, and due dates.
        </div>
    </div>
</aside>