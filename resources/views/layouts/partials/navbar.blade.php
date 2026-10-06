<header class="bmd-layout-header">
    <div class="navbar navbar-light bg-faded animate__animated animate__fadeInDown h-16 max-h-16">
        <button class="navbar-toggler animate__animated animate__wobble animate__delay-1s" type="button" data-toggle="drawer" data-target="#dw-s1">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="d-none d-md-flex flex-grow-1 items-center px-3">
            <label class="mb-0 w-100 nozha-quick-search">
                <span class="sr-only">Quick search</span>
                <input data-quick-search type="search" placeholder="Search..." class="form-control app-navbar-search" aria-label="Search tasks, people, or projects">
            </label>
        </div>
        <ul class="nav navbar-nav items-center">
            <li class="nav-item">
                <x-notification-bell />
            </li>
            @can('create', App\Models\Task::class)
                <li class="nav-item">
                    <a href="{{ route('tasks.create') }}" class="app-navbar-task-btn inline-flex h-9 items-center rounded-full px-4 text-sm font-medium text-white" style="background-color: var(--brand)">
                        <i class="fas fa-plus mr-1"></i> Task
                    </a>
                </li>
            @endcan
            <li class="nav-item">
                <div class="dropdown">
                    <button class="app-navbar-profile-btn m-0 inline-flex items-center gap-2 rounded-full px-1.5 py-1" type="button" id="dropdownMenu4" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @auth
                            <img src="{{ auth()->user()->avatar_url }}" alt="" class="rounded-circle screen-user-profile">
                        @else
                            <img src="{{ asset('img/user-profile.jpg') }}" alt="" class="rounded-circle screen-user-profile">
                        @endauth
                        <span class="d-none d-lg-inline">{{ auth()->user()?->name ?? 'Guest' }}</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu4">
                        <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="far fa-user fa-sm c-main mr-2"></i>Profile</a>
                        <button class="dropdown-item" type="button" onclick="dark()"><i class="fas fa-moon fa-sm c-main mr-2"></i>Dark Mode</button>
                        <button class="dropdown-item" type="button" onclick="rtl()"><i class="fas fa-align-right fa-sm c-main mr-2"></i>RTL</button>
                        @can('settings.view')
                            <a class="dropdown-item" href="{{ route('system-settings.index') }}"><i class="fas fa-cog fa-sm c-main mr-2"></i>Setting</a>
                        @endcan
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item" type="submit"><i class="fas fa-sign-out-alt c-main fa-sm mr-2"></i>Sign Out</button>
                        </form>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</header>
