@php
    $query = request()->except('layout');
    $tabQuery = request()->except(['status', 'page']);
    $listUrl = route('dashboard', array_merge($query, ['layout' => 'list']));
    $gridUrl = route('dashboard', array_merge($query, ['layout' => 'grid']));
    $assigneeHeading = $tasks->getCollection()->contains(
        fn ($task): bool => $task->assignedUsers->count() > 1,
    ) ? 'Team' : 'Assignee';
    $searchControl = 'form-control workspace-search';
    $selectControl = 'form-control workspace-select';
    $activeFilterCount = collect(['status', 'priority', 'assigned_user_id', 'category_id', 'due_from', 'due_to'])
        ->filter(fn (string $key): bool => filled(request($key)))
        ->count();
    if (filled(request('sort_by')) && request('sort_by') !== 'created_at') {
        $activeFilterCount++;
    }
    if (filled(request('sort_order')) && request('sort_order') !== 'desc') {
        $activeFilterCount++;
    }
    $activeStatus = request('status');
@endphp

<section class="card flat workspace-card space-y-3 sm:space-y-4">
    <div>
        <h2 class="workspace-title-sm">
            Task Management Workspace
        </h2>
        <p class="mt-0.5 hidden text-[11px] text-gray-500 md:block">Search, filter, and manage every assignment without leaving the dashboard.</p>
    </div>

    <div class="flex flex-col gap-2 border-b border-gray-100 pb-3 sm:gap-3 sm:pb-4 md:flex-row md:items-center md:justify-between">
        <div class="no-scrollbar flex items-center gap-3 overflow-x-auto text-[11px] font-medium sm:gap-4 sm:text-sm">
            <a href="{{ route('dashboard', $tabQuery) }}" @class(['shrink-0 pb-1.5 sm:pb-2.5', 'border-b-2 border-indigo-600 text-indigo-600' => ! $activeStatus, 'text-slate-500 hover:text-slate-700' => (bool) $activeStatus])>All tasks</a>
            <a href="{{ route('dashboard', array_merge($tabQuery, ['status' => 'overdue'])) }}" @class(['shrink-0 pb-1.5 sm:pb-2.5', 'border-b-2 border-indigo-600 text-indigo-600' => $activeStatus === 'overdue', 'text-slate-500 hover:text-slate-700' => $activeStatus !== 'overdue'])>Overdue</a>
            <a href="{{ route('dashboard', array_merge($tabQuery, ['status' => 'completed'])) }}" @class(['shrink-0 pb-1.5 sm:pb-2.5', 'border-b-2 border-indigo-600 text-indigo-600' => $activeStatus === 'completed', 'text-slate-500 hover:text-slate-700' => $activeStatus !== 'completed'])>Completed</a>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="flex w-full flex-col gap-2 md:w-auto md:flex-row md:items-center">
            <input type="hidden" name="layout" value="{{ $layout }}">
            <label class="relative w-full min-w-0 md:w-auto">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 sm:pl-3">
                    <svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"/></svg>
                </span>
                <span class="sr-only">Search tasks</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..." class="{{ $searchControl }}">
            </label>

            <div class="flex w-full items-center gap-2 md:w-auto">
            <div class="relative min-w-0 flex-1 md:flex-none" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                <button type="button" @click="open = ! open" class="flex h-8 w-full items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-gray-50 px-2.5 text-[11px] font-medium text-gray-700 hover:bg-gray-100 sm:h-9 sm:gap-2 sm:px-3 sm:text-xs md:w-auto">
                    <svg class="size-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 4.5h18l-6.75 7.89v4.86L9.75 19.5v-7.11L3 4.5Z"/></svg>
                    Filters
                    <span class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700 sm:px-2 sm:text-xs">{{ $activeFilterCount }}</span>
                </button>

                <div x-show="open" x-cloak x-transition class="absolute left-0 right-0 z-30 mt-2 max-h-[min(22rem,70vh)] w-full overflow-y-auto rounded-lg border border-gray-200 bg-white p-3 text-xs shadow-lg sm:left-auto sm:right-0 sm:w-72 sm:max-w-[calc(100vw-2rem)] sm:p-4 md:w-80">
                    <div class="mb-2 flex items-center justify-between sm:mb-3">
                        <p class="text-xs font-semibold text-gray-900 sm:text-sm">Filters</p>
                        <a href="{{ route('dashboard', ['layout' => $layout]) }}" class="text-[11px] font-medium text-gray-500 hover:text-gray-700 sm:text-xs">Reset</a>
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:gap-3">
                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-gray-500 sm:text-xs">Status</span>
                            <select name="status" class="{{ $selectControl }}">
                                <option value="">All statuses</option>
                                @foreach(\App\Enums\TaskStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                                <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-gray-500 sm:text-xs">Priority</span>
                            <select name="priority" class="{{ $selectControl }}">
                                <option value="">All priorities</option>
                                @foreach(\App\Enums\TaskPriority::cases() as $priority)
                                    <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-gray-500 sm:text-xs">Assignee</span>
                            <select name="assigned_user_id" class="{{ $selectControl }}">
                                <option value="">All assignees</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected((string) request('assigned_user_id') === (string) $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-gray-500 sm:text-xs">Category</span>
                            <select name="category_id" class="{{ $selectControl }}">
                                <option value="">All categories</option>
                                @foreach($filterCategories as $category)
                                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="grid grid-cols-2 gap-2 sm:gap-3">
                            <label class="block min-w-0">
                                <span class="mb-1 block text-[11px] font-medium text-gray-500 sm:text-xs">Due from</span>
                                <input type="date" name="due_from" value="{{ request('due_from') }}" class="{{ $selectControl }}">
                            </label>
                            <label class="block min-w-0">
                                <span class="mb-1 block text-[11px] font-medium text-gray-500 sm:text-xs">Due to</span>
                                <input type="date" name="due_to" value="{{ request('due_to') }}" class="{{ $selectControl }}">
                            </label>
                        </div>
                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-gray-500 sm:text-xs">Sort by</span>
                            <select name="sort_by" class="{{ $selectControl }}">
                                <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>Created</option>
                                <option value="due_date" @selected(request('sort_by') === 'due_date')>Due date</option>
                                <option value="priority" @selected(request('sort_by') === 'priority')>Priority</option>
                                <option value="status" @selected(request('sort_by') === 'status')>Status</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-[11px] font-medium text-gray-500 sm:text-xs">Order</span>
                            <select name="sort_order" class="{{ $selectControl }}">
                                <option value="desc" @selected(request('sort_order', 'desc') === 'desc')>Newest / high first</option>
                                <option value="asc" @selected(request('sort_order') === 'asc')>Oldest / low first</option>
                            </select>
                        </label>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-2 sm:mt-4">
                        <a href="{{ route('dashboard', ['layout' => $layout]) }}" class="inline-flex h-8 items-center px-2 text-[11px] font-medium text-gray-500 hover:text-gray-700 sm:text-xs">Reset</a>
                        <button type="submit" class="btn btn-sm btn-primary">Apply filters</button>
                    </div>
                </div>
            </div>

            <div class="inline-flex h-8 shrink-0 items-center rounded-lg border border-gray-200 bg-gray-100 p-0.5 sm:h-9">
                <a href="{{ $listUrl }}" @class(['inline-flex h-full items-center rounded-md px-3 text-xs font-semibold', 'bg-white text-gray-900 shadow-xs' => $layout === 'list', 'text-gray-500 hover:text-gray-700' => $layout !== 'list'])>List</a>
                <a href="{{ $gridUrl }}" @class(['inline-flex h-full items-center rounded-md px-3 text-xs font-semibold', 'bg-white text-gray-900 shadow-xs' => $layout === 'grid', 'text-gray-500 hover:text-gray-700' => $layout !== 'grid'])>Grid</a>
            </div>
            </div>
        </form>
    </div>

    @if($layout === 'grid')
        <div class="grid grid-cols-1 items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($tasks as $task)
                @include('tasks.partials.card', ['task' => $task])
            @empty
                <div class="col-span-full py-16 text-center text-gray-500">No tasks match these filters.</div>
            @endforelse
        </div>
    @else
        <div class="w-full overflow-x-auto overflow-y-visible rounded-xl border border-gray-200/80">
            <table class="w-full min-w-[720px] border-collapse text-left text-xs sm:min-w-[880px] sm:text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-[10px] font-semibold uppercase tracking-wider text-gray-500 sm:text-[11px]">
                        <th class="min-w-[180px] whitespace-nowrap px-2 py-2.5 sm:min-w-[220px] sm:px-3 sm:py-3">Task</th>
                        <th class="min-w-[88px] whitespace-nowrap px-2 py-2.5 sm:min-w-[120px] sm:px-3 sm:py-3">{{ $assigneeHeading }}</th>
                        <th class="min-w-[72px] whitespace-nowrap px-2 py-2.5 sm:min-w-[110px] sm:px-3 sm:py-3">Priority</th>
                        <th class="min-w-[88px] whitespace-nowrap px-2 py-2.5 sm:min-w-[120px] sm:px-3 sm:py-3">Status</th>
                        <th class="min-w-[96px] whitespace-nowrap px-2 py-2.5 sm:min-w-[120px] sm:px-3 sm:py-3">Due Date</th>
                        <th class="min-w-[120px] whitespace-nowrap px-2 py-2.5 sm:min-w-[140px] sm:px-3 sm:py-3">Progress</th>
                        <th class="sticky right-0 min-w-[56px] whitespace-nowrap bg-gray-50 px-2 py-2.5 text-right sm:min-w-[72px] sm:px-3 sm:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tasks as $task)
                        @include('tasks.partials.row', ['task' => $task])
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">No tasks created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="flex flex-col gap-2 overflow-x-auto border-t border-gray-200 bg-white pt-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:pt-4">
        <p class="text-[11px] font-medium leading-7 text-gray-600 sm:text-sm sm:leading-9">
            Showing {{ $tasks->firstItem() ?? 0 }} to {{ $tasks->lastItem() ?? 0 }} of {{ $tasks->total() }} results
        </p>
        <div class="flex items-center">{{ $tasks->links('pagination.task-table') }}</div>
    </div>
</section>
