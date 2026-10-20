<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
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

if (! function_exists('locale_is_rtl')) {
    /**
     * Pashto and Dari use a right-to-left document direction.
     */
    function locale_is_rtl(): bool
    {
        return SetLocale::isRtl();
    }
}

if (! function_exists('localized_activity')) {
    /**
     * Translate a stored English activity phrase at render time without changing the database value.
     */
    function localized_activity(?string $description): string
    {
        if ($description === null || $description === '') {
            return '';
        }

        $localizeToken = static function (string $token): string {
            $translated = __($token);

            return is_string($translated) ? $translated : $token;
        };

        if (preg_match('/^changed status from (.+) to (.+)$/u', $description, $matches) === 1) {
            return __('changed status from :from to :to', [
                'from' => $localizeToken($matches[1]),
                'to' => $localizeToken($matches[2]),
            ]);
        }

        if (preg_match('/^changed status to (.+)$/u', $description, $matches) === 1) {
            return __('changed status to :to', [
                'to' => $localizeToken($matches[1]),
            ]);
        }

        if (preg_match('/^changed priority from (.+) to (.+)$/u', $description, $matches) === 1) {
            return __('changed priority from :from to :to', [
                'from' => $localizeToken($matches[1]),
                'to' => $localizeToken($matches[2]),
            ]);
        }

        if (preg_match('/^changed due date to (.+)$/u', $description, $matches) === 1) {
            return __('changed due date to :date', [
                'date' => $matches[1],
            ]);
        }

        if (preg_match('/^attached file (.+)$/u', $description, $matches) === 1) {
            return __('attached file :name', [
                'name' => $matches[1],
            ]);
        }

        $translated = __($description);

        return is_string($translated) ? $translated : $description;
    }
}
