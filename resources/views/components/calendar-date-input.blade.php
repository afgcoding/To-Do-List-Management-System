@props([
    'name',
    'value' => null,
    'picker' => false,
])

@php
    $calendar = calendar_type();
    $isGregorian = $calendar === \App\Support\CalendarDateFormatter::GREGORIAN;
    $display = calendar_input_value($value);
@endphp

@if ($picker)
    @php
        $monthNames = $calendar === \App\Support\CalendarDateFormatter::JALALI
            ? array_map(fn (string $month): string => __($month), \Morilog\Jalali\CalendarUtils::AFGHAN_MONTHS_NAME)
            : ($calendar === \App\Support\CalendarDateFormatter::HIJRI
                ? array_map(fn (string $month): string => __($month), \App\Support\CalendarDateFormatter::HIJRI_MONTHS)
                : collect(range(1, 12))
                    ->map(fn (int $month): string => now()->startOfYear()->month($month)->locale(app()->getLocale())->translatedFormat('F'))
                    ->all());
        $weekdays = [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')];
    @endphp
    <div
        class="task-date-picker"
        x-data="taskDatePicker({
            value: @js($display),
            today: @js(to_calendar_ymd(now()) ?? now()->toDateString()),
            calendar: @js($calendar),
            monthNames: @js(array_values($monthNames)),
            weekdays: @js($weekdays),
            localizeDigits: @js(in_array(app()->getLocale(), ['fa', 'ps'], true)),
        })"
        @keydown.escape.window="open = false"
        @click.outside="open = false"
    >
        <input
            type="text"
            name="{{ $name }}"
            x-model="value"
            readonly
            autocomplete="off"
            dir="ltr"
            inputmode="none"
            placeholder="{{ __('YYYY-MM-DD') }}"
            @click="open = true; showMonthFor(value || today)"
            @focus="open = true; showMonthFor(value || today)"
            {{ $attributes->merge(['class' => 'form-control task-date-picker-input']) }}
        >
        <div class="task-date-picker-pop" x-show="open" x-cloak>
            <div class="task-date-picker-nav">
                <button type="button" class="btn btn-sm btn-secondary" @click="prevMonth">‹</button>
                <p class="task-date-picker-title" x-text="title"></p>
                <button type="button" class="btn btn-sm btn-secondary" @click="nextMonth">›</button>
            </div>
            <div class="task-date-picker-week">
                <template x-for="(weekday, weekdayIndex) in weekdays" :key="weekdayIndex">
                    <span x-text="weekday"></span>
                </template>
            </div>
            <div class="task-date-picker-grid">
                <template x-for="day in days" :key="day.key">
                    <button
                        type="button"
                        class="task-date-picker-day"
                        :class="{
                            'is-muted': ! day.inMonth,
                            'is-today': day.isToday,
                            'is-selected': day.isSelected,
                        }"
                        :disabled="! day.inMonth"
                        @click="pick(day)"
                        x-text="day.label"
                    ></button>
                </template>
            </div>
            <div class="task-date-picker-actions">
                <button type="button" class="btn btn-sm btn-secondary" @click="clear()">{{ __('Clear') }}</button>
                <button type="button" class="btn btn-sm btn-primary" @click="pickToday()">{{ __('Today') }}</button>
            </div>
        </div>
    </div>
@else
    <input
        type="{{ $isGregorian ? 'date' : 'text' }}"
        name="{{ $name }}"
        value="{{ $display }}"
        dir="ltr"
        @if (! $isGregorian)
            inputmode="numeric"
            autocomplete="off"
            placeholder="{{ __('YYYY-MM-DD') }}"
            pattern="[0-9۰-۹٠-٩]{4}-[0-9۰-۹٠-٩]{2}-[0-9۰-۹٠-٩]{2}"
        @endif
        {{ $attributes->merge(['class' => 'form-control']) }}
    >
@endif
