<aside class="hidden w-64 shrink-0 bg-slate-900 text-slate-300 lg:block">
    <div class="sticky top-0 flex min-h-screen flex-col p-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-2 py-2">
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

        <nav class="mt-8 space-y-6" aria-label="Primary navigation">
            <div>
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Main</p>
                <div class="mt-2 space-y-1">
                    @include('layouts.includes.nav-item', ['label' => 'Dashboard', 'path' => '/dashboard'])
                </div>
            </div>

            <div>
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Task hub</p>
                <div class="mt-2 space-y-1">
                    @include('layouts.includes.nav-item', ['label' => 'Tasks', 'path' => '/tasks'])
                    @can('tasks.edit')
                        @include('layouts.includes.nav-item', ['label' => 'Recurring Tasks', 'path' => '/recurring-tasks'])
                    @endcan
                </div>
            </div>

            @canany(['departments.view', 'departments.manage', 'categories.manage', 'tags.manage'])
                <div>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Organization</p>
                    <div class="mt-2 space-y-1">
                        @canany(['departments.view', 'departments.manage'])
                            @include('layouts.includes.nav-item', ['label' => 'Departments', 'path' => '/departments'])
                        @endcanany
                        @can('categories.manage')
                            @include('layouts.includes.nav-item', ['label' => 'Categories', 'path' => '/categories'])
                        @endcan
                        @can('tags.manage')
                            @include('layouts.includes.nav-item', ['label' => 'Tags', 'path' => '/tags'])
                        @endcan
                    </div>
                </div>
            @endcanany

            @canany(['users.view', 'roles.view', 'roles.manage', 'logs.view', 'settings.view'])
                <div>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Administration</p>
                    <div class="mt-2 space-y-1">
                        @can('users.view')
                            @include('layouts.includes.nav-item', ['label' => 'Users & Roles', 'path' => '/users'])
                        @endcan
                        @canany(['roles.view', 'roles.manage'])
                            @include('layouts.includes.nav-item', ['label' => 'Roles & Permissions', 'path' => '/roles'])
                        @endcanany
                        @can('logs.view')
                            @include('layouts.includes.nav-item', ['label' => 'Activity Logs', 'path' => '/activity-logs'])
                        @endcan
                        @can('settings.view')
                            @include('layouts.includes.nav-item', ['label' => 'System Settings', 'path' => '/system-settings'])
                        @endcan
                    </div>
                </div>
            @endcanany
        </nav>

        <div class="mt-auto rounded-xl border border-slate-700 bg-slate-800/70 p-3 text-xs leading-5 text-slate-400">
            Keep work moving with clear ownership, priorities, and due dates.
        </div>
    </div>
</aside>
