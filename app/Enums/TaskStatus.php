<?php

declare(strict_types=1);

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To Do',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Todo => 'slate',
            self::InProgress => 'indigo',
            self::Completed => 'emerald',
            self::Cancelled => 'rose',
        };
    }

    /**
     * Statuses offered on create/edit forms. The show-page dropdown includes Completed.
     *
     * @return list<self>
     */
    public static function manualCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $status): bool => $status !== self::Completed,
        ));
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
