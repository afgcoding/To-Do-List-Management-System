@extends('layouts.app')
@php
    $pageTitle = 'System Settings';
    $hideLayoutPageHeader = true;
    $sampleDate = \Illuminate\Support\Carbon::parse('2026-09-12');
@endphp

@section('content')
<div class="settings-page"
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
            <x-back-link class="btn btn-secondary settings-back" :href="route('tasks.index')">Back to tasks</x-back-link>
        </div>
    </header>

    <form method="POST" action="{{ route('system-settings.update') }}" enctype="multipart/form-data" class="settings-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="remove_logo" :value="removeLogo ? '1' : '0'">

        <section class="card flat workspace-card">
            <h2 class="settings-section-title">Branding</h2>
            <p class="settings-section-copy">Shown in the sidebar and the top navigation.</p>

            <div class="settings-fields">
                <div class="settings-field">
                    <label for="company_name" class="settings-label">Company name</label>
                    <input id="company_name" name="company_name" type="text" required maxlength="255"
                        value="{{ old('company_name', $settings->company_name) }}"
                        class="form-control">
                    <x-input-error :messages="$errors->get('company_name')" />
                </div>

                <div class="settings-field">
                    <p class="settings-label">Logo</p>
                    <div class="settings-logo-row">
                        <div class="settings-logo-box">
                            <template x-if="preview">
                                <img :src="preview" alt="Logo preview">
                            </template>
                            <template x-if="!preview">
                                <span>No logo</span>
                            </template>
                        </div>
                        <div class="settings-logo-actions">
                            <label class="btn btn-primary btn-sm settings-upload">
                                Upload logo
                                <input x-ref="logoInput" class="sr-only" type="file" name="logo" accept=".png,.jpg,.jpeg,.svg,.webp,image/png,image/jpeg,image/svg+xml,image/webp" @change="onLogo($event)">
                            </label>
                            <button type="button" x-show="preview" @click="clearLogo()" class="btn btn-secondary btn-sm">
                                Remove logo
                            </button>
                        </div>
                    </div>
                    <p class="settings-hint">PNG, JPG, SVG, or WebP up to 2 MB.</p>
                    <x-input-error :messages="$errors->get('logo')" />
                </div>

                <div class="settings-field">
                    <label for="primary_color" class="settings-label">Primary accent color</label>
                    <div class="settings-color-row">
                        <input id="primary_color" name="primary_color" type="color"
                            value="{{ old('primary_color', $settings->primary_color ?? \App\Models\SystemSetting::DEFAULT_PRIMARY_COLOR) }}"
                            class="settings-color">
                        <p class="settings-hint">Used on sign-in and primary actions. Defaults to indigo if unset.</p>
                    </div>
                    <x-input-error :messages="$errors->get('primary_color')" />
                </div>
            </div>
        </section>

        <section class="card flat workspace-card">
            <h2 class="settings-section-title">Localization</h2>
            <p class="settings-section-copy">Timezone defaults to Asia/Kabul. Date formats include a live preview.</p>

            <div class="settings-fields">
                <div class="settings-field settings-timezone" @click.outside="timezoneOpen = false">
                    <label class="settings-label">Timezone</label>
                    <input type="hidden" name="time_zone" :value="timezone">
                    <button type="button" class="form-control settings-tz-trigger" @click="timezoneOpen = !timezoneOpen">
                        <span class="settings-tz-value" x-text="timezone"></span>
                        <svg class="settings-tz-caret" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="timezoneOpen" x-cloak class="settings-tz-menu">
                        <input type="search" x-model="timezoneQuery" placeholder="Search timezones..." class="form-control">
                        <ul class="settings-tz-list">
                            <template x-for="zone in filteredZones" :key="zone">
                                <li>
                                    <button type="button" class="settings-tz-option"
                                        @click="timezone = zone; timezoneOpen = false; timezoneQuery = ''"
                                        :class="timezone === zone ? 'is-active' : ''"
                                        x-text="zone"></button>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <x-input-error :messages="$errors->get('time_zone')" />
                </div>

                <fieldset class="settings-field">
                    <legend class="settings-label">Date format</legend>
                    <div class="settings-formats">
                        @foreach (\App\Models\SystemSetting::DATE_FORMATS as $format)
                            <label class="settings-format">
                                <input type="radio" name="date_format" value="{{ $format }}"
                                    @checked(old('date_format', $settings->date_format) === $format)>
                                <span>
                                    <span class="settings-format-preview">{{ $sampleDate->format($format) }}</span>
                                    <span class="settings-format-code">{{ $format }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('date_format')" />
                </fieldset>
            </div>
        </section>

        <div class="settings-save">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
