<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    public const CACHE_KEY = 'system_settings';

    public const DEFAULT_COMPANY_NAME = 'Task Management Enterprise';

    public const DEFAULT_PRIMARY_COLOR = '#4F46E5';

    public const DEFAULT_DATE_FORMAT = 'Y-m-d';

    public const DEFAULT_TIME_ZONE = 'Asia/Kabul';

    /** @var list<string> */
    public const DATE_FORMATS = ['Y-m-d', 'm/d/Y', 'd-m-Y', 'd M Y'];

    protected $fillable = [
        'company_name',
        'logo',
        'primary_color',
        'date_format',
        'time_zone',
    ];

    public static function getSettings(): self
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (! is_array($cached) && ! $cached instanceof self) {
            Cache::forget(self::CACHE_KEY);

            try {
                if (! Schema::hasTable('system_settings')) {
                    return self::fromCachedAttributes(self::defaultAttributes(), exists: false);
                }

                $cached = Cache::rememberForever(self::CACHE_KEY, function (): array {
                    return self::query()->firstOrCreate(
                        ['id' => 1],
                        self::defaultAttributes(),
                    )->attributesToArray();
                });
            } catch (\Throwable) {
                return self::fromCachedAttributes(self::defaultAttributes(), exists: false);
            }
        }

        if ($cached instanceof self) {
            return $cached;
        }

        return self::fromCachedAttributes(is_array($cached) ? $cached : self::defaultAttributes());
    }

    /**
     * @return array<string, mixed>
     */
    private static function defaultAttributes(): array
    {
        return [
            'id' => 1,
            'company_name' => self::DEFAULT_COMPANY_NAME,
            'date_format' => self::DEFAULT_DATE_FORMAT,
            'time_zone' => self::DEFAULT_TIME_ZONE,
            'logo' => null,
            'primary_color' => self::DEFAULT_PRIMARY_COLOR,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private static function fromCachedAttributes(array $attributes, bool $exists = true): self
    {
        $settings = new self;
        $settings->setRawAttributes($attributes, true);
        $settings->exists = $exists;

        return $settings;
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function logoUrl(): ?string
    {
        if (blank($this->logo)) {
            return null;
        }

        return Storage::disk('public')->url($this->logo);
    }
}
