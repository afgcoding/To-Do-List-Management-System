<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'TaskFlow') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 font-sans text-slate-800 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="{{ route('login') }}" class="mb-8 flex items-center gap-2.5">
                @if (setting('logo'))
                    <img src="{{ setting()->logoUrl() }}" alt="" class="h-10 w-10 rounded-xl border border-slate-200 bg-white object-contain p-1 shadow-sm">
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-sm font-semibold text-white shadow-sm">✓</span>
                @endif
                <span>
                    <span class="block text-sm font-semibold text-slate-900">{{ setting('company_name', config('app.name', 'TaskFlow')) }}</span>
                    <span class="block text-xs text-slate-500">Enterprise workspace</span>
                </span>
            </a>

            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
