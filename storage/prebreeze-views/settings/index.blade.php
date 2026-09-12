@extends('layouts.app')
@php
    $pageTitle = 'System Settings';
    $field = 'w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500';
    $sampleDate = \Illuminate\Support\Carbon::parse('2026-09-12');
@endphp

@section('content')
<div class="mx-auto max-w-3xl space-y-6"
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
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">System Settings</h1>
        <p class="mt-1 text-sm text-slate-500">Brand the workspace and choose how dates and timezones appear across the platform.</p>
    </div>

    <form method="POST" action="{{ route('system-settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="remove_logo" :value="removeLogo ? '1' : '0'">

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
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
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex h-20 w-20 min-w-[80px] items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 shadow-sm">
                            <template x-if="preview">
                                <img :src="preview" alt="Logo preview" class="max-h-full max-w-full object-contain transition hover:scale-105">
                            </template>
                            <template x-if="!preview">
                                <span class="text-xs font-medium text-slate-400">No logo</span>
                            </template>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <label class="inline-flex cursor-pointer items-center rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                                Upload logo
                                <input x-ref="logoInput" class="sr-only" type="file" name="logo" accept=".png,.jpg,.jpeg,.svg,.webp,image/png,image/jpeg,image/svg+xml,image/webp" @change="onLogo($event)">
                            </label>
                            <button type="button" x-show="preview" @click="clearLogo()" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                Remove logo
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">PNG, JPG, SVG, or WebP up to 2 MB.</p>
                    <x-input-error :messages="$errors->get('logo')" />
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
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
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach (\App\Models\SystemSetting::DATE_FORMATS as $format)
                            <label @class(['flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60', 'border-slate-200 hover:border-slate-300'])>
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

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
