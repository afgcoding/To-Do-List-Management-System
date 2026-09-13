<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="--brand: {{ brand_color() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ setting('company_name', config('app.name', 'TaskFlow')) }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <aside class="relative hidden overflow-hidden text-white lg:flex lg:flex-col lg:justify-between lg:p-12"
                style="background: linear-gradient(165deg, var(--brand) 0%, #0f172a 78%);">
                <div class="pointer-events-none absolute -right-16 -top-16 size-72 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-20 left-10 size-80 rounded-full bg-white/5 blur-3xl"></div>

                <a href="{{ route('login') }}" class="relative z-10 flex items-center gap-3">
                    @if (setting('logo'))
                        <img src="{{ setting()->logoUrl() }}" alt="" class="h-12 w-12 rounded-2xl border border-white/20 bg-white object-contain p-1 shadow-lg">
                    @else
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-lg font-semibold shadow-lg ring-1 ring-white/30">✓</span>
                    @endif
                    <span>
                        <span class="block text-base font-semibold tracking-tight">{{ setting('company_name', config('app.name', 'TaskFlow')) }}</span>
                        <span class="block text-sm text-white/70">Enterprise workspace</span>
                    </span>
                </a>

                <div class="relative z-10 max-w-md space-y-4">
                    <p class="text-3xl font-semibold tracking-tight">Stay aligned on every task.</p>
                    <p class="text-sm leading-6 text-white/75">Sign in to manage work, collaborate with your team, and keep delivery on track.</p>
                </div>
            </aside>

            <div class="flex items-center justify-center px-4 py-8 sm:px-8 sm:py-10">
                <div class="w-full max-w-md rounded-3xl border border-white/60 bg-white/80 p-5 shadow-xl shadow-slate-200/70 backdrop-blur-xl sm:p-10">
                    <a href="{{ route('login') }}" class="mb-8 flex items-center gap-2.5 lg:hidden">
                        @if (setting('logo'))
                            <img src="{{ setting()->logoUrl() }}" alt="" class="h-10 w-10 rounded-xl border border-slate-200 bg-white object-contain p-1 shadow-sm">
                        @else
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl text-sm font-semibold text-white shadow-sm" style="background-color: var(--brand)">✓</span>
                        @endif
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">{{ setting('company_name', config('app.name', 'TaskFlow')) }}</span>
                            <span class="block text-xs text-slate-500">Enterprise workspace</span>
                        </span>
                    </a>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
