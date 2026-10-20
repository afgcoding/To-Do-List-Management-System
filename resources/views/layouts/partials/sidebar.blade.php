@php
    $taskHubOpen = request()->is('tasks*') || request()->is('calendar*') || request()->is('recurring-tasks*');
    $orgOpen = request()->is('departments*') || request()->is('categories*') || request()->is('tags*');
    $adminOpen = request()->is('users*') || request()->is('roles*') || request()->is('reports*') || request()->is('activity-logs*') || request()->is('system-settings*');
    $brandName = setting('company_name', config('app.name', 'TaskFlow'));
@endphp
<div id="dw-s1" class="bmd-layout-drawer bg-faded">
    <div class="container-fluid side-bar-container">
        <header class="sidebar-brand pb-0">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                @if (setting('logo'))
                    <img src="{{ setting()->logoUrl() }}" alt="{{ $brandName }}" class="side-logo side-logo-img">
                @else
                    <img src="{{ asset('svg/logo-8.svg') }}" alt="{{ $brandName }}" class="side-logo side-logo-img">
                @endif
            </a>
        </header>

        <p class="side-comment">{{ __('Main') }}</p>
        <li class="side a-collapse short">
            <a href="{{ route('dashboard') }}" class="side-item{{ request()->routeIs('dashboard') ? ' selected' : '' }}"><i class="fas fa-tachometer-alt"></i><span>{{ __('Dashboard') }}</span></a>
        </li>

        <ul class="side a-collapse{{ $taskHubOpen ? '' : ' short' }}">
            <a class="ul-text px-3 py-2"><i class="fas fa-tasks"></i><span>{{ __('Task hub') }}</span> <i class="fas fa-chevron-down arrow"></i></a>
            <div class="side-item-container{{ $taskHubOpen ? '' : ' hide animated' }}">
                @include('layouts.partials.nav-item', ['label' => __('Tasks'), 'path' => '/tasks', 'iconClass' => 'fas fa-check-square'])
                @include('layouts.partials.nav-item', ['label' => __('Calendar'), 'path' => '/calendar', 'iconClass' => 'fas fa-calendar-alt'])
                @can('tasks.edit')
                    @include('layouts.partials.nav-item', ['label' => __('Recurring Tasks'), 'path' => '/recurring-tasks', 'iconClass' => 'fas fa-sync'])
                @endcan
            </div>
        </ul>

        @canany(['departments.view', 'departments.manage', 'categories.manage', 'tags.manage'])
            <p class="side-comment">{{ __('Organization') }}</p>
            <ul class="side a-collapse{{ $orgOpen ? '' : ' short' }}">
                <a class="ul-text px-3 py-2"><i class="fas fa-sitemap"></i><span>{{ __('Organization') }}</span> <i class="fas fa-chevron-down arrow"></i></a>
                <div class="side-item-container{{ $orgOpen ? '' : ' hide animated' }}">
                    @canany(['departments.view', 'departments.manage'])
                        @include('layouts.partials.nav-item', ['label' => __('Departments'), 'path' => '/departments', 'iconClass' => 'fas fa-building'])
                    @endcanany
                    @can('categories.manage')
                        @include('layouts.partials.nav-item', ['label' => __('Categories'), 'path' => '/categories', 'iconClass' => 'fas fa-folder'])
                    @endcan
                    @can('tags.manage')
                        @include('layouts.partials.nav-item', ['label' => __('Tags'), 'path' => '/tags', 'iconClass' => 'fas fa-tags'])
                    @endcan
                </div>
            </ul>
        @endcanany

        @canany(['users.view', 'roles.view', 'roles.manage', 'logs.view', 'settings.view', 'reports.view-team'])
            <p class="side-comment">{{ __('Administration') }}</p>
            <ul class="side a-collapse{{ $adminOpen ? '' : ' short' }}">
                <a class="ul-text px-3 py-2"><i class="fas fa-cog"></i><span>{{ __('Administration') }}</span> <i class="fas fa-chevron-down arrow"></i></a>
                <div class="side-item-container{{ $adminOpen ? '' : ' hide animated' }}">
                    @can('users.view')
                        @include('layouts.partials.nav-item', ['label' => __('Users & Roles'), 'path' => '/users', 'iconClass' => 'fas fa-users'])
                    @endcan
                    @canany(['roles.view', 'roles.manage'])
                        @include('layouts.partials.nav-item', ['label' => __('Roles & Permissions'), 'path' => '/roles', 'iconClass' => 'fas fa-user-shield'])
                    @endcanany
                    @can('reports.view-team')
                        @include('layouts.partials.nav-item', ['label' => __('Reports'), 'path' => '/reports', 'iconClass' => 'fas fa-chart-bar'])
                    @endcan
                    @can('logs.view')
                        @include('layouts.partials.nav-item', ['label' => __('Activity Logs'), 'path' => '/activity-logs', 'iconClass' => 'fas fa-history'])
                    @endcan
                    @can('settings.view')
                        @include('layouts.partials.nav-item', ['label' => __('System Settings'), 'path' => '/system-settings', 'iconClass' => 'fas fa-sliders-h'])
                    @endcan
                </div>
            </ul>
        @endcanany
    </div>
</div>
