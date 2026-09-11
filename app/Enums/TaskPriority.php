<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Low => 'slate',
            self::Medium => 'sky',
            self::High => 'amber',
            self::Urgent => 'rose',
        };
    }

    public function sortRank(): int
    {
        return match ($this) {
            self::Urgent => 1,
            self::High => 2,
            self::Medium => 3,
            self::Low => 4,
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
