<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;
use Throwable;

final class CalendarDateFormatter
{
    public const GREGORIAN = 'gregorian';

    public const JALALI = 'jalali';

    public const HIJRI = 'hijri';

    public const GREGORIAN_YEAR_FLOOR = 1700;

    /**
     * @var list<string>
     */
    public const HIJRI_MONTHS = [
        'Muharram',
        'Safar',
        'Rabi I',
        'Rabi II',
        'Jumada I',
        'Jumada II',
        'Rajab',
        'Shaban',
        'Ramadan',
        'Shawwal',
        'Dhu al-Qadah',
        'Dhu al-Hijjah',
    ];

    /**
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            self::GREGORIAN => 'Miladi (Gregorian)',
            self::JALALI => 'Shamsi (Jalali)',
            self::HIJRI => 'Qamari (Hijri)',
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_keys(self::types());
    }

    public static function active(): string
    {
        $type = (string) setting('calendar_type', SystemSetting::DEFAULT_CALENDAR_TYPE);

        return in_array($type, SystemSetting::CALENDAR_TYPES, true)
            ? $type
            : SystemSetting::DEFAULT_CALENDAR_TYPE;
    }

    public static function format(CarbonInterface $date, string $format, string $calendar): string
    {
        $formatted = match ($calendar) {
            self::JALALI => self::formatJalali($date, $format),
            self::HIJRI => self::formatHijri($date, $format),
            default => $date->format($format),
        };

        return self::localizeDisplay($formatted, $format, $date, $calendar);
    }

    public static function toInputYmd(mixed $date, ?string $calendar = null): ?string
    {
        $carbon = self::carbonFromMixed($date);

        if ($carbon === null) {
            return null;
        }

        $calendar ??= self::active();

        $formatted = match ($calendar) {
            self::JALALI => self::formatJalali($carbon, 'Y-m-d'),
            self::HIJRI => self::formatHijri($carbon, 'Y-m-d'),
            default => $carbon->format('Y-m-d'),
        };

        return self::toWesternDigits($formatted);
    }

    public static function parseToGregorianYmd(?string $value, ?string $calendar = null): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = self::toWesternDigits(trim($value));

        if ($normalized === '') {
            return null;
        }

        $calendar ??= self::active();
        $parts = self::extractYmdParts($normalized);

        if ($parts === null) {
            if ($calendar !== self::GREGORIAN) {
                return null;
            }

            try {
                return Carbon::parse($normalized)->toDateString();
            } catch (Throwable) {
                return null;
            }
        }

        [$year, $month, $day] = $parts;

        $sourceCalendar = $year >= self::GREGORIAN_YEAR_FLOOR
            ? self::GREGORIAN
            : $calendar;

        $carbon = self::carbonFromParts($year, $month, $day, $sourceCalendar);

        return $carbon?->toDateString();
    }

    /**
     * @return array{
     *     year: int,
     *     month: int,
     *     title: string,
     *     previousYear: int,
     *     previousMonth: int,
     *     nextYear: int,
     *     nextMonth: int,
     *     days: Collection<int, array{iso: string, day: int, inMonth: bool, isToday: bool}>
     * }
     */
    public static function monthView(?int $year, ?int $month): array
    {
        $calendar = self::active();
        $todayParts = self::calendarParts(now(), $calendar);

        $year = $year ?: $todayParts[0];
        $month = $month ?: $todayParts[1];

        if (! self::isValidCursor($year, $month, $calendar)) {
            [$year, $month] = $todayParts;
        }

        $first = self::carbonFromParts($year, $month, 1, $calendar) ?? now()->startOfMonth();
        $daysInMonth = self::daysInCalendarMonth($year, $month, $calendar);
        $last = $first->copy()->addDays(max(1, $daysInMonth) - 1);

        $start = $first->copy()->startOfWeek(Carbon::MONDAY);
        $end = $last->copy()->endOfWeek(Carbon::SUNDAY);

        $days = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            [$cellYear, $cellMonth, $cellDay] = self::calendarParts($cursor, $calendar);

            $days->push([
                'iso' => $cursor->toDateString(),
                'day' => $cellDay,
                'inMonth' => $cellYear === $year && $cellMonth === $month,
                'isToday' => $cursor->isToday(),
            ]);

            $cursor->addDay();
        }

        $previousMonth = $month === 1 ? 12 : $month - 1;
        $previousYear = $month === 1 ? $year - 1 : $year;
        $nextMonth = $month === 12 ? 1 : $month + 1;
        $nextYear = $month === 12 ? $year + 1 : $year;

        return [
            'year' => $year,
            'month' => $month,
            'title' => self::monthTitle($first, $year, $month, $calendar),
            'previousYear' => $previousYear,
            'previousMonth' => $previousMonth,
            'nextYear' => $nextYear,
            'nextMonth' => $nextMonth,
            'days' => $days,
        ];
    }

    public static function localizeDigits(string $formatted): string
    {
        if (! in_array(app()->getLocale(), ['fa', 'ps'], true)) {
            return $formatted;
        }

        return strtr($formatted, [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
        ]);
    }

    public static function toWesternDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public static function calendarParts(CarbonInterface $date, ?string $calendar = null): array
    {
        $calendar ??= self::active();

        if ($calendar === self::JALALI) {
            [$year, $month, $day] = CalendarUtils::toJalali((int) $date->year, (int) $date->month, (int) $date->day);

            return [(int) $year, (int) $month, (int) $day];
        }

        if ($calendar === self::HIJRI) {
            return self::islamicCivilParts($date);
        }

        return [(int) $date->year, (int) $date->month, (int) $date->day];
    }

    private static function localizeDisplay(string $formatted, string $format, CarbonInterface $date, string $calendar): string
    {
        if ($calendar === self::GREGORIAN && $format === 'd M Y') {
            $month = $date->format('M');
            $translated = __($month);
            if (is_string($translated) && $translated !== '') {
                $formatted = str_replace($month, $translated, $formatted);
            }
        }

        if ($calendar === self::JALALI && $format === 'd M Y') {
            foreach (CalendarUtils::AFGHAN_MONTHS_NAME as $monthName) {
                $translated = __($monthName);
                if (is_string($translated) && $translated !== $monthName) {
                    $formatted = str_replace($monthName, $translated, $formatted);
                }
            }
        }

        if ($calendar === self::HIJRI && $format === 'd M Y') {
            foreach (self::HIJRI_MONTHS as $monthName) {
                $translated = __($monthName);
                if (is_string($translated) && $translated !== $monthName) {
                    $formatted = str_replace($monthName, $translated, $formatted);
                }
            }
        }

        return self::localizeDigits($formatted);
    }

    private static function formatJalali(CarbonInterface $date, string $format): string
    {
        $strftime = match ($format) {
            'Y-m-d' => '%Y-%m-%d',
            'm/d/Y' => '%m/%d/%Y',
            'd-m-Y' => '%d-%m-%Y',
            'd M Y' => '%d %B %Y',
            default => '%Y-%m-%d',
        };

        try {
            CalendarUtils::useAfghanMonthsName();

            return Jalalian::fromDateTime($date)->format($strftime);
        } catch (Throwable) {
            return $date->format($format);
        }
    }

    private static function formatHijri(CarbonInterface $date, string $format): string
    {
        [$year, $month, $day] = self::islamicCivilParts($date);
        $monthName = self::HIJRI_MONTHS[$month - 1] ?? 'Muharram';

        return match ($format) {
            'm/d/Y' => sprintf('%02d/%02d/%04d', $month, $day, $year),
            'd-m-Y' => sprintf('%02d-%02d-%04d', $day, $month, $year),
            'd M Y' => sprintf('%02d %s %04d', $day, $monthName, $year),
            default => sprintf('%04d-%02d-%02d', $year, $month, $day),
        };
    }

    /**
     * Tabular Islamic (civil / Kuwaiti) calendar conversion.
     *
     * @return array{0: int, 1: int, 2: int}
     */
    private static function islamicCivilParts(CarbonInterface $date): array
    {
        return self::gregorianToHijri((int) $date->year, (int) $date->month, (int) $date->day);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function hijriToGregorianParts(int $year, int $month, int $day): array
    {
        return self::hijriToGregorian($year, $month, $day);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function gregorianToHijri(int $year, int $month, int $day): array
    {
        if ($year > 1582 || ($year === 1582 && $month > 10) || ($year === 1582 && $month === 10 && $day > 14)) {
            $jd = self::intPart((1461 * ($year + 4800 + self::intPart(($month - 14) / 12))) / 4)
                + self::intPart((367 * ($month - 2 - 12 * self::intPart(($month - 14) / 12))) / 12)
                - self::intPart((3 * self::intPart(($year + 4900 + self::intPart(($month - 14) / 12)) / 100)) / 4)
                + $day - 32075;
        } else {
            $jd = 367 * $year
                - self::intPart((7 * ($year + 5001 + self::intPart(($month - 9) / 7))) / 4)
                + self::intPart((275 * $month) / 9)
                + $day + 1729777;
        }

        $l = $jd - 1948440 + 10632;
        $n = self::intPart(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = self::intPart((10985 - $l) / 5316) * self::intPart((50 * $l) / 17719)
            + self::intPart($l / 5670) * self::intPart((43 * $l) / 15238);
        $l = $l - self::intPart((30 - $j) / 15) * self::intPart((17719 * $j) / 50)
            - self::intPart($j / 16) * self::intPart((15238 * $j) / 43) + 29;
        $hijriMonth = self::intPart((24 * $l) / 709);
        $hijriDay = $l - self::intPart((709 * $hijriMonth) / 24);
        $hijriYear = 30 * $n + $j - 30;

        return [$hijriYear, $hijriMonth, $hijriDay];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function hijriToGregorian(int $year, int $month, int $day): array
    {
        $jd = self::intPart((11 * $year + 3) / 30) + 354 * $year + 30 * $month
            - self::intPart(($month - 1) / 2) + $day + 1948440 - 385;

        if ($jd > 2299160) {
            $l = $jd + 68569;
            $n = self::intPart((4 * $l) / 146097);
            $l = $l - self::intPart((146097 * $n + 3) / 4);
            $i = self::intPart((4000 * ($l + 1)) / 1461001);
            $l = $l - self::intPart((1461 * $i) / 4) + 31;
            $j = self::intPart((80 * $l) / 2447);
            $gregorianDay = $l - self::intPart((2447 * $j) / 80);
            $l = self::intPart($j / 11);
            $gregorianMonth = $j + 2 - 12 * $l;
            $gregorianYear = 100 * ($n - 49) + $i + $l;
        } else {
            $j = $jd + 1402;
            $k = self::intPart(($j - 1) / 1461);
            $l = $j - 1461 * $k;
            $n = self::intPart(($l - 1) / 365) - self::intPart($l / 1461);
            $i = $l - 365 * $n + 30;
            $j = self::intPart((80 * $i) / 2447);
            $gregorianDay = $i - self::intPart((2447 * $j) / 80);
            $i = self::intPart($j / 11);
            $gregorianMonth = $j + 2 - 12 * $i;
            $gregorianYear = 4 * $k + $n + $i - 4716;
        }

        return [$gregorianYear, $gregorianMonth, $gregorianDay];
    }

    private static function intPart(float|int $value): int
    {
        return $value < -0.0000001
            ? (int) ceil($value - 0.0000001)
            : (int) floor($value + 0.0000001);
    }

    private static function monthTitle(CarbonInterface $firstDay, int $year, int $month, string $calendar): string
    {
        if ($calendar === self::GREGORIAN) {
            return $firstDay->copy()->locale(app()->getLocale())->translatedFormat('F Y');
        }

        if ($calendar === self::JALALI) {
            CalendarUtils::useAfghanMonthsName();
            $title = Jalalian::fromDateTime($firstDay)->format('%B %Y');

            foreach (CalendarUtils::AFGHAN_MONTHS_NAME as $monthName) {
                $translated = __($monthName);
                if (is_string($translated) && $translated !== $monthName) {
                    $title = str_replace($monthName, $translated, $title);
                }
            }

            return self::localizeDigits($title);
        }

        $monthName = self::HIJRI_MONTHS[$month - 1] ?? 'Muharram';
        $translated = __($monthName);
        $label = (is_string($translated) ? $translated : $monthName).' '.$year;

        return self::localizeDigits($label);
    }

    private static function daysInCalendarMonth(int $year, int $month, string $calendar): int
    {
        if ($calendar === self::JALALI) {
            try {
                return (int) (new Jalalian($year, $month, 1))->getMonthDays();
            } catch (Throwable) {
                return 29;
            }
        }

        if ($calendar === self::HIJRI) {
            $start = self::carbonFromParts($year, $month, 1, self::HIJRI);
            $nextMonth = $month === 12 ? 1 : $month + 1;
            $nextYear = $month === 12 ? $year + 1 : $year;
            $next = self::carbonFromParts($nextYear, $nextMonth, 1, self::HIJRI);

            if ($start === null || $next === null) {
                return 30;
            }

            return max(1, (int) $start->diffInDays($next));
        }

        return (int) Carbon::createFromDate($year, $month, 1)->daysInMonth;
    }

    private static function isValidCursor(int $year, int $month, string $calendar): bool
    {
        if ($month < 1 || $month > 12) {
            return false;
        }

        return match ($calendar) {
            self::JALALI => $year >= 1200 && $year <= 1600,
            self::HIJRI => $year >= 1300 && $year <= 1600,
            default => $year >= 1970 && $year <= 2100,
        };
    }

    private static function carbonFromParts(int $year, int $month, int $day, string $calendar): ?Carbon
    {
        try {
            [$gregorianYear, $gregorianMonth, $gregorianDay] = match ($calendar) {
                self::JALALI => CalendarUtils::toGregorian($year, $month, $day),
                self::HIJRI => self::hijriToGregorianParts($year, $month, $day),
                default => [$year, $month, $day],
            };
        } catch (Throwable) {
            return null;
        }

        $gregorianYear = (int) $gregorianYear;
        $gregorianMonth = (int) $gregorianMonth;
        $gregorianDay = (int) $gregorianDay;

        if (! checkdate($gregorianMonth, $gregorianDay, $gregorianYear)) {
            return null;
        }

        return Carbon::createFromDate($gregorianYear, $gregorianMonth, $gregorianDay)->startOfDay();
    }

    private static function carbonFromMixed(mixed $date): ?Carbon
    {
        if ($date === null || $date === '') {
            return null;
        }

        if ($date instanceof CarbonInterface) {
            return Carbon::instance($date->toDateTime());
        }

        try {
            return Carbon::parse((string) $date);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array{0: int, 1: int, 2: int}|null
     */
    private static function extractYmdParts(string $value): ?array
    {
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $matches) === 1) {
            return [(int) $matches[1], (int) $matches[2], (int) $matches[3]];
        }

        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $value, $matches) === 1) {
            return [(int) $matches[3], (int) $matches[2], (int) $matches[1]];
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $matches) === 1) {
            $format = (string) setting('date_format', SystemSetting::DEFAULT_DATE_FORMAT);

            if ($format === 'm/d/Y') {
                return [(int) $matches[3], (int) $matches[1], (int) $matches[2]];
            }

            return [(int) $matches[3], (int) $matches[2], (int) $matches[1]];
        }

        return null;
    }
}
