<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Support\CalendarDateFormatter;

trait ConvertsCalendarFormDates
{
    protected function convertCalendarDateFields(): void
    {
        $converted = [];

        foreach ($this->calendarDateFields() as $field) {
            $raw = $this->input($field);

            if (! is_string($raw) || $raw === '') {
                continue;
            }

            $parsed = CalendarDateFormatter::parseToGregorianYmd($raw);

            if ($parsed !== null) {
                $converted[$field] = $parsed;
            }
        }

        if ($converted !== []) {
            $this->merge($converted);
        }
    }

    /**
     * @return list<string>
     */
    protected function calendarDateFields(): array
    {
        return ['start_date', 'due_date', 'next_recurring_date'];
    }
}
