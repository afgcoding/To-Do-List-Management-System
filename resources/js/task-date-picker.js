function pad(value) {
    return String(value).padStart(2, '0');
}

function westernDigits(value) {
    return String(value).replace(/[۰-۹٠-٩]/g, (digit) => {
        const persian = '۰۱۲۳۴۵۶۷۸۹';
        const arabic = '٠١٢٣٤٥٦٧٨٩';
        const index = persian.indexOf(digit);

        if (index >= 0) {
            return String(index);
        }

        return String(arabic.indexOf(digit));
    });
}

function toYmd(year, month, day) {
    return `${year}-${pad(month)}-${pad(day)}`;
}

function parseYmd(value) {
    const match = /^(\d{4})-(\d{1,2})-(\d{1,2})$/.exec(westernDigits(value || ''));

    if (! match) {
        return null;
    }

    return [Number(match[1]), Number(match[2]), Number(match[3])];
}

function gregorianToJalali(gy, gm, gd) {
    const offsets = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    const gy2 = gm > 2 ? gy + 1 : gy;
    let days = 355666 + (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100)
        + Math.floor((gy2 + 399) / 400) + gd + offsets[gm - 1];
    let jy = -1595 + (33 * Math.floor(days / 12053));
    days %= 12053;
    jy += 4 * Math.floor(days / 1461);
    days %= 1461;

    if (days > 365) {
        jy += Math.floor((days - 1) / 365);
        days = (days - 1) % 365;
    }

    const jm = days < 186 ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30);
    const jd = 1 + (days < 186 ? days % 31 : (days - 186) % 30);

    return [jy, jm, jd];
}

function jalaliToGregorian(jy, jm, jd) {
    jy += 1595;
    let days = -355668 + (365 * jy) + (Math.floor(jy / 33) * 8) + Math.floor(((jy % 33) + 3) / 4) + jd
        + (jm < 7 ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
    let gy = 400 * Math.floor(days / 146097);
    days %= 146097;

    if (days > 36524) {
        gy += 100 * Math.floor(--days / 36524);
        days %= 36524;

        if (days >= 365) {
            days += 1;
        }
    }

    gy += 4 * Math.floor(days / 1461);
    days %= 1461;

    if (days > 365) {
        gy += Math.floor((days - 1) / 365);
        days = (days - 1) % 365;
    }

    let gd = days + 1;
    const months = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    let gm = 1;

    while (gm < 13 && gd > months[gm]) {
        gd -= months[gm];
        gm += 1;
    }

    return [gy, gm, gd];
}

function intPart(value) {
    return value < -0.0000001 ? Math.ceil(value - 0.0000001) : Math.floor(value + 0.0000001);
}

function gregorianToHijri(year, month, day) {
    let jd;

    if (year > 1582 || (year === 1582 && month > 10) || (year === 1582 && month === 10 && day > 14)) {
        jd = intPart((1461 * (year + 4800 + intPart((month - 14) / 12))) / 4)
            + intPart((367 * (month - 2 - 12 * intPart((month - 14) / 12))) / 12)
            - intPart((3 * intPart((year + 4900 + intPart((month - 14) / 12)) / 100)) / 4)
            + day - 32075;
    } else {
        jd = (367 * year)
            - intPart((7 * (year + 5001 + intPart((month - 9) / 7))) / 4)
            + intPart((275 * month) / 9)
            + day + 1729777;
    }

    let l = jd - 1948440 + 10632;
    const n = intPart((l - 1) / 10631);
    l = l - 10631 * n + 354;
    const j = intPart((10985 - l) / 5316) * intPart((50 * l) / 17719)
        + intPart(l / 5670) * intPart((43 * l) / 15238);
    l = l - intPart((30 - j) / 15) * intPart((17719 * j) / 50)
        - intPart(j / 16) * intPart((15238 * j) / 43) + 29;

    return [30 * n + j - 30, intPart((24 * l) / 709), l - intPart((709 * intPart((24 * l) / 709)) / 24)];
}

function hijriToGregorian(year, month, day) {
    const jd = intPart((11 * year + 3) / 30) + 354 * year + 30 * month
        - intPart((month - 1) / 2) + day + 1948440 - 385;

    let gregorianYear;
    let gregorianMonth;
    let gregorianDay;

    if (jd > 2299160) {
        let l = jd + 68569;
        const n = intPart((4 * l) / 146097);
        l = l - intPart((146097 * n + 3) / 4);
        const i = intPart((4000 * (l + 1)) / 1461001);
        l = l - intPart((1461 * i) / 4) + 31;
        const j = intPart((80 * l) / 2447);
        gregorianDay = l - intPart((2447 * j) / 80);
        l = intPart(j / 11);
        gregorianMonth = j + 2 - 12 * l;
        gregorianYear = 100 * (n - 49) + i + l;
    } else {
        let j = jd + 1402;
        const k = intPart((j - 1) / 1461);
        let l = j - 1461 * k;
        const n = intPart((l - 1) / 365) - intPart(l / 1461);
        const i = l - 365 * n + 30;
        j = intPart((80 * i) / 2447);
        gregorianDay = i - intPart((2447 * j) / 80);
        const i2 = intPart(j / 11);
        gregorianMonth = j + 2 - 12 * i2;
        gregorianYear = 4 * k + n + i2 - 4716;
    }

    return [gregorianYear, gregorianMonth, gregorianDay];
}

function toGregorianParts(calendar, year, month, day) {
    if (calendar === 'jalali') {
        return jalaliToGregorian(year, month, day);
    }

    if (calendar === 'hijri') {
        return hijriToGregorian(year, month, day);
    }

    return [year, month, day];
}

function weekdayMondayIndex(calendar, year, month, day) {
    const [gy, gm, gd] = toGregorianParts(calendar, year, month, day);
    const weekday = new Date(gy, gm - 1, gd).getDay();

    return (weekday + 6) % 7;
}

function daysInMonth(calendar, year, month) {
    if (calendar === 'jalali') {
        if (month <= 6) {
            return 31;
        }

        if (month <= 11) {
            return 30;
        }

        const [gy, gm, gd] = jalaliToGregorian(year, 12, 30);
        const back = gregorianToJalali(gy, gm, gd);

        return back[2] === 30 ? 30 : 29;
    }

    if (calendar === 'hijri') {
        const startParts = hijriToGregorian(year, month, 1);
        const nextMonth = month === 12 ? 1 : month + 1;
        const nextYear = month === 12 ? year + 1 : year;
        const nextParts = hijriToGregorian(nextYear, nextMonth, 1);
        const start = Date.UTC(startParts[0], startParts[1] - 1, startParts[2]);
        const next = Date.UTC(nextParts[0], nextParts[1] - 1, nextParts[2]);

        return Math.max(1, Math.round((next - start) / 86400000));
    }

    return new Date(year, month, 0).getDate();
}

export function registerTaskDatePicker(Alpine) {
    Alpine.data('taskDatePicker', (config) => ({
        open: false,
        value: config.value || '',
        today: config.today,
        calendar: config.calendar,
        monthNames: config.monthNames,
        weekdays: config.weekdays,
        localizeDigits: Boolean(config.localizeDigits),
        year: 0,
        month: 0,

        init() {
            const parts = parseYmd(this.value) || parseYmd(this.today) || parseYmd(new Date().toISOString().slice(0, 10));
            this.year = parts[0];
            this.month = parts[1];
        },

        get title() {
            return this.display(`${this.monthNames[this.month - 1] || ''} ${this.year}`);
        },

        get days() {
            const cells = [];
            const length = daysInMonth(this.calendar, this.year, this.month);
            const offset = weekdayMondayIndex(this.calendar, this.year, this.month, 1);

            for (let index = 0; index < 42; index += 1) {
                const day = index - offset + 1;
                const inMonth = day >= 1 && day <= length;
                const ymd = inMonth ? toYmd(this.year, this.month, day) : '';

                cells.push({
                    key: `${this.year}-${this.month}-${index}`,
                    label: inMonth ? this.display(String(day)) : '',
                    ymd,
                    inMonth,
                    isToday: ymd === this.today,
                    isSelected: ymd !== '' && ymd === this.value,
                });
            }

            return cells;
        },

        display(value) {
            if (! this.localizeDigits) {
                return value;
            }

            return String(value).replace(/[0-9]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
        },

        toggle() {
            this.open = ! this.open;

            if (this.open) {
                this.showMonthFor(this.value || this.today);
            }
        },

        showMonthFor(ymd) {
            const parts = parseYmd(ymd);

            if (! parts) {
                return;
            }

            this.year = parts[0];
            this.month = parts[1];
        },

        prevMonth() {
            if (this.month === 1) {
                this.month = 12;
                this.year -= 1;

                return;
            }

            this.month -= 1;
        },

        nextMonth() {
            if (this.month === 12) {
                this.month = 1;
                this.year += 1;

                return;
            }

            this.month += 1;
        },

        pick(day) {
            if (! day.inMonth) {
                return;
            }

            this.value = day.ymd;
            this.open = false;
        },

        pickToday() {
            this.value = this.today;
            this.showMonthFor(this.today);
            this.open = false;
        },

        clear() {
            this.value = '';
            this.open = false;
        },
    }));
}
