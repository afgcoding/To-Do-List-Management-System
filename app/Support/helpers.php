<?php

declare(strict_types=1);

use App\Models\SystemSetting;
use Carbon\Carbon;
use Carbon\CarbonInterface;

if (! function_exists('setting')) {
    /**
     * Read the singleton system settings row (cached forever until updated).
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $settings = SystemSetting::getSettings();

        if ($key === null) {
            return $settings;
        }

        return $settings->getAttribute($key) ?? $default;
    }
}

if (! function_exists('format_date')) {
    /**
     * Format a timestamp with the saved system timezone and date format.
     */
    function format_date(mixed $date, ?string $format = null): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            $carbon = $date instanceof CarbonInterface
                ? $date->copy()
                : Carbon::parse($date);
        } catch (Throwable) {
            return null;
        }

        $timezone = (string) setting('time_zone', SystemSetting::DEFAULT_TIME_ZONE);
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = SystemSetting::DEFAULT_TIME_ZONE;
        }

        $format ??= (string) setting('date_format', SystemSetting::DEFAULT_DATE_FORMAT);
        if (! in_array($format, SystemSetting::DATE_FORMATS, true)) {
            $format = SystemSetting::DEFAULT_DATE_FORMAT;
        }

        return $carbon->timezone($timezone)->format($format);
    }
}

if (! function_exists('brand_color')) {
    /**
     * Hex accent color from system settings, falling back to indigo.
     */
    function brand_color(): string
    {
        $color = (string) setting('primary_color', SystemSetting::DEFAULT_PRIMARY_COLOR);

        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1
            ? $color
            : SystemSetting::DEFAULT_PRIMARY_COLOR;
    }
}
