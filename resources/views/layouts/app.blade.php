<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="--brand: {{ brand_color() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($pageTitle ?? 'Dashboard') . ' · ' . setting('company_name', config('app.name', 'TaskFlow')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen bg-slate-50" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden"
            @click="sidebarOpen = false"
            aria-hidden="true"
        ></div>
        @include('layouts.includes.sidebar')
        <div class="flex min-h-screen min-w-0 flex-1 flex-col bg-slate-50">
            @include('layouts.includes.navbar')
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
                @include('layouts.includes.footer')
            </main>
        </div>
    </div>
    @include('components.alert')
    <script>document.addEventListener('click', event => { const trigger = event.target.closest('[data-modal-open],[data-modal-close]'); if (!trigger) return; const modal = document.getElementById(trigger.dataset.modalOpen || trigger.dataset.modalClose); if (modal) { modal.classList.toggle('hidden', Boolean(trigger.dataset.modalClose)); modal.classList.toggle('flex', Boolean(trigger.dataset.modalOpen)); } }); document.addEventListener('keydown', event => { if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); document.querySelector('[data-quick-search]')?.focus(); } if (event.key === 'Escape') document.querySelectorAll('[data-modal]').forEach(modal => modal.classList.add('hidden')); });</script>
    @stack('scripts')
</body>

</html>
