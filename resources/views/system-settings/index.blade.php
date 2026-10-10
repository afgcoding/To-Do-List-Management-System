@extends('layouts.app')
@php
    $pageTitle = 'System Settings';
    $hideLayoutPageHeader = true;
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
    $sampleDate = \Illuminate\Support\Carbon::parse('2026-09-12');
@endphp

@section('content')
<div class="settings-page mx-auto max-w-3xl space-y-6"
    x-data="{
        preview: @js($settings->logoUrl()),
        removeLogo: false,
        timezoneOpen: false,
        timezoneQuery: '',
        timezone: @js(old('time_zone', $settings->time_zone)),
        zones: @js($timezones),
        get filteredZones() {
            const query = this.timezoneQuery.toLowerCase();
            if (! query) {
                return this.zones;
            }
            return this.zones.filter((name) => name.toLowerCase().includes(query));
        },
        onLogo(event) {
            const file = event.target.files?.[0];
            if (! file) {
                return;
            }
            this.removeLogo = false;
            this.preview = URL.createObjectURL(file);
        },
        clearLogo() {
            this.removeLogo = true;
            this.preview = null;
            this.$refs.logoInput.value = '';
        }
    }">
    <header class="settings-header">
        <div class="settings-intro">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i></a></li>
            </ol>
            <h1 class="workspace-title">System Settings</h1>
            <p class="settings-lead">Brand the workspace and choose how dates and timezones appear across the platform.</p>
        </div>
        <div class="settings-actions">
            <x-back-link class="settings-back" :href="route('tasks.index')">Back to tasks</x-back-link>
        </div>
    </header>

    <form method="POST" action="{{ route('system-settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="remove_logo" :value="removeLogo ? '1' : '0'">

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Branding</h2>
            <p class="mt-1 text-sm text-slate-500">Shown in the sidebar and the top navigation.</p>

            <div class="mt-5 space-y-5">
                <div>
                    <label for="company_name" class="mb-1.5 block text-xs font-semibold text-slate-700">Company name</label>
                    <input id="company_name" name="company_name" type="text" required maxlength="255"
                        value="{{ old('company_name', $settings->company_name) }}"
                        class="{{ $field }}">
                    <x-input-error :messages="$errors->get('company_name')" />
                </div>

                <div>
                    <p class="mb-1.5 text-xs font-semibold text-slate-700">Logo</p>
                    <div class="settings-logo-row">
                        <div class="settings-logo-box">
                            <template x-if="preview">
                                <img :src="preview" alt="Logo preview" class="max-h-full max-w-full object-contain transition hover:scale-105">
                            </template>
                            <template x-if="!preview">
                                <span class="text-xs font-medium text-slate-400">No logo</span>
                            </template>
                        </div>
                        <div class="settings-logo-actions">
                            <label class="settings-btn settings-btn-brand">
                                Upload logo
                                <input x-ref="logoInput" class="sr-only" type="file" name="logo" accept=".png,.jpg,.jpeg,.svg,.webp,image/png,image/jpeg,image/svg+xml,image/webp" @change="onLogo($event)">
                            </label>
                            <button type="button" x-show="preview" @click="clearLogo()" class="settings-btn settings-btn-ghost">
                                Remove logo
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">PNG, JPG, SVG, or WebP up to 2 MB.</p>
                    <x-input-error :messages="$errors->get('logo')" />
                </div>

                <div>
                    <label for="primary_color" class="mb-1.5 block text-xs font-semibold text-slate-700">Primary accent color</label>
                    <div class="settings-color-row">
                        <input id="primary_color" name="primary_color" type="color"
                            value="{{ old('primary_color', $settings->primary_color ?? \App\Models\SystemSetting::DEFAULT_PRIMARY_COLOR) }}"
                            class="settings-color">
                        <p class="text-xs text-slate-500">Used on sign-in and primary actions. Defaults to indigo if unset.</p>
                    </div>
                    <x-input-error :messages="$errors->get('primary_color')" />
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Localization</h2>
            <p class="mt-1 text-sm text-slate-500">Timezone defaults to Asia/Kabul. Date formats include a live preview.</p>

            <div class="mt-5 space-y-5">
                <div class="relative" @click.outside="timezoneOpen = false">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Timezone</label>
                    <input type="hidden" name="time_zone" :value="timezone">
                    <button type="button" @click="timezoneOpen = !timezoneOpen"
                        class="{{ $field }} flex items-center justify-between text-left">
                        <span class="truncate" x-text="timezone"></span>
                        <svg class="size-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="timezoneOpen" x-cloak class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                        <div class="border-b border-slate-100 p-1">
                            <input type="search" x-model="timezoneQuery" placeholder="Search timezones..."
                                class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <ul class="max-h-60 w-full overflow-y-auto p-1 text-sm">
                            <template x-for="zone in filteredZones" :key="zone">
                                <li>
                                    <button type="button" @click="timezone = zone; timezoneOpen = false; timezoneQuery = ''"
                                        class="w-full rounded-md px-3 py-2 text-left hover:bg-indigo-50"
                                        :class="timezone === zone ? 'bg-indigo-50 font-medium text-indigo-700' : 'text-slate-700'"
                                        x-text="zone"></button>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <x-input-error :messages="$errors->get('time_zone')" />
                </div>

                <fieldset>
                    <legend class="mb-2 text-xs font-semibold text-slate-700">Date format</legend>
                    <div class="settings-formats">
                        @foreach (\App\Models\SystemSetting::DATE_FORMATS as $format)
                            <label class="settings-format">
                                <input type="radio" name="date_format" value="{{ $format }}" class="mt-1 text-indigo-600 focus:ring-indigo-500"
                                    @checked(old('date_format', $settings->date_format) === $format)>
                                <span>
                                    <span class="block text-sm font-semibold text-slate-800">{{ $sampleDate->format($format) }}</span>
                                    <span class="mt-0.5 block font-mono text-[11px] text-slate-400">{{ $format }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('date_format')" />
                </fieldset>
            </div>
        </section>

        <div class="settings-save">
            <button type="submit" class="settings-btn settings-btn-brand settings-save-btn">
                Save Changes
            </button>
        </div>
    </form>
</div>

<style>
    .app-page-content .settings-page {
        --settings-radius: 8px;
        width: 100%;
        min-width: 0;
    }

    .app-page-content .settings-page section {
        border-radius: 12px !important;
    }

    .app-page-content .settings-page h2 {
        margin: 0;
        color: #64748b !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.06em;
        line-height: 1.2 !important;
        text-align: start !important;
        text-transform: uppercase;
    }

    .app-page-content .settings-header {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }

    .app-page-content .settings-intro,
    .app-page-content .settings-page form,
    .app-page-content .settings-page section {
        min-width: 0;
    }

    .app-page-content .settings-page h1.workspace-title {
        text-align: start !important;
        overflow-wrap: anywhere;
    }

    .app-page-content .settings-page p {
        display: block !important;
        overflow-wrap: anywhere;
    }

    .app-page-content .settings-lead {
        margin: 0.35rem 0 0;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.45;
    }

    .app-page-content .settings-actions {
        width: 100%;
    }

    .app-page-content a.settings-back,
    .app-page-content .settings-page .settings-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        min-height: 32px;
        margin: 0;
        padding: 0.3rem 0.7rem !important;
        border-radius: var(--settings-radius) !important;
        font-size: 0.75rem !important;
        font-weight: 600;
        line-height: 1.2;
        box-shadow: none;
    }

    .app-page-content .settings-page button,
    .app-page-content .settings-page .settings-format,
    .app-page-content .settings-page .settings-logo-box,
    .app-page-content .settings-page .settings-color {
        border-radius: var(--settings-radius) !important;
    }

    .app-page-content a.settings-back {
        width: 100%;
        gap: 0.35rem;
        border: 1px solid #e2e8f0 !important;
        background: #fff !important;
        color: #475569 !important;
    }

    .app-page-content a.settings-back svg {
        width: 0.85rem;
        height: 0.85rem;
    }

    .app-page-content .settings-btn-brand {
        border: 0 !important;
        background: #4f46e5 !important;
        color: #fff !important;
        cursor: pointer;
    }

    .app-page-content .settings-btn-brand:hover {
        background: #4338ca !important;
        color: #fff !important;
    }

    .app-page-content .settings-btn-ghost {
        border: 1px solid #cbd5e1 !important;
        background: #fff !important;
        color: #475569 !important;
        cursor: pointer;
    }

    .app-page-content .settings-btn-ghost:hover {
        background: #f8fafc !important;
    }

    .app-page-content .settings-page input[type="text"],
    .app-page-content .settings-page input[type="search"] {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        border-radius: var(--settings-radius) !important;
    }

    .app-page-content .settings-logo-box {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3.25rem;
        height: 3.25rem;
        min-width: 3.25rem;
        overflow: hidden;
        box-sizing: border-box;
        padding: 0.35rem;
        border: 1px solid #e2e8f0;
        border-radius: var(--settings-radius) !important;
        background: #f8fafc;
    }

    .app-page-content .settings-logo-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .app-page-content .settings-color {
        width: 2.4rem;
        height: 2rem;
        padding: 0.15rem;
        border: 1px solid #cbd5e1;
        border-radius: var(--settings-radius) !important;
        background: #fff;
        cursor: pointer;
    }

    .app-page-content .settings-format {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        min-width: 0;
        margin: 0;
        box-sizing: border-box;
        padding: 0.65rem 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: var(--settings-radius) !important;
        background: #fff;
        cursor: pointer;
    }

    .app-page-content .settings-format:has(input:checked) {
        border-color: #4f46e5;
        background: rgb(238 242 255 / 0.7);
    }

    .app-page-content .settings-logo-row {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
    }

    .app-page-content .settings-logo-actions {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
        width: 100%;
    }

    .app-page-content .settings-logo-actions label,
    .app-page-content .settings-logo-actions button {
        width: 100%;
    }

    .app-page-content .settings-color-row {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.55rem;
    }

    .app-page-content .settings-color-row p {
        margin: 0;
        color: #64748b;
        font-size: 0.75rem;
        line-height: 1.45;
    }

    .app-page-content .settings-formats {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 0.5rem;
    }

    .app-page-content .settings-save {
        display: flex;
        justify-content: stretch;
    }

    .app-page-content .settings-save-btn {
        width: 100%;
    }

    @media (min-width: 480px) {
        .app-page-content .settings-logo-row {
            flex-direction: row;
            align-items: center;
        }

        .app-page-content .settings-logo-actions {
            flex-direction: row;
            flex-wrap: wrap;
            width: auto;
        }

        .app-page-content .settings-logo-actions label,
        .app-page-content .settings-logo-actions button {
            width: auto;
        }

        .app-page-content .settings-color-row {
            flex-direction: row;
            align-items: center;
        }
    }

    @media (min-width: 640px) {
        .app-page-content a.settings-back,
        .app-page-content .settings-save-btn {
            width: auto;
        }

        .app-page-content .settings-actions {
            width: auto;
        }

        .app-page-content .settings-save {
            justify-content: flex-end;
        }

        .app-page-content .settings-formats {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }
    }

    @media (min-width: 768px) {
        .app-page-content .settings-header {
            flex-direction: row;
            align-items: flex-end;
            justify-content: space-between;
        }
    }
</style>
@endsection
