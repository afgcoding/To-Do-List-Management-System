<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Manager = 'manager';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Manager => 'Manager',
            self::Employee => 'Employee',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::SuperAdmin => 'violet',
            self::Admin => 'indigo',
            self::Manager => 'sky',
            self::Employee => 'slate',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::SuperAdmin || $this === self::Admin;
    }

    public function spatieName(): string
    {
        return $this->label();
    }

    public static function fromSpatieName(string $name): self
    {
        return match ($name) {
            'Super Admin' => self::SuperAdmin,
            'Admin' => self::Admin,
            'Manager' => self::Manager,
            default => self::Employee,
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
