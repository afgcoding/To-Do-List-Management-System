<!DOCTYPE html>
<html class="no-js h-screen overflow-hidden" lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="--brand: {{ brand_color() }}">
@include('layouts.partials.header')

<body class="h-screen overflow-hidden">
    <div class="bmd-layout-container bmd-drawer-f-l avam-container animated bmd-drawer-in h-screen overflow-hidden">
        @include('layouts.partials.navbar')
        @include('layouts.partials.sidebar')
        <main class="bmd-layout-content overflow-y-auto">
            <div class="container-fluid">
                @unless ($hideLayoutPageHeader ?? false)
                    <div class="mb-4">
                        <div class="page-header breadcrumb-header">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                <div class="page-header-title text-left-rtl">
                                    <div class="d-inline">
                                        <h3 class="lite-text mb-0 text-lg font-semibold">{{ $pageTitle ?? 'Dashboard' }}</h3>
                                        <span class="lite-text text-gray text-xs">{{ setting('company_name', config('app.name', 'TaskFlow')) }}</span>
                                    </div>
                                </div>
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
                                    <li class="breadcrumb-item active">{{ $pageTitle ?? 'Dashboard' }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                @endunless
                <div class="app-page-content">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
                @include('layouts.partials.footer', ['chromeOnly' => true])
            </div>
        </main>
    </div>
    @include('layouts.partials.footer', ['scriptsOnly' => true])
</body>
</html>
