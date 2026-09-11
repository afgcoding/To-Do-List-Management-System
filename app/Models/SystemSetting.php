<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    public const CACHE_KEY = 'system_settings';

    public const DEFAULT_COMPANY_NAME = 'Task Management Enterprise';

    public const DEFAULT_DATE_FORMAT = 'Y-m-d';

    public const DEFAULT_TIME_ZONE = 'Asia/Kabul';

    /** @var list<string> */
    public const DATE_FORMATS = ['Y-m-d', 'm/d/Y', 'd-m-Y', 'd M Y'];

    protected $fillable = [
        'company_name',
        'logo',
        'date_format',
        'time_zone',
    ];

    public static function getSettings(): self
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (! is_array($cached) && ! $cached instanceof self) {
            Cache::forget(self::CACHE_KEY);
            $cached = Cache::rememberForever(self::CACHE_KEY, function (): array {
                return self::query()->firstOrCreate(
                    ['id' => 1],
                    [
                        'company_name' => self::DEFAULT_COMPANY_NAME,
                        'date_format' => self::DEFAULT_DATE_FORMAT,
                        'time_zone' => self::DEFAULT_TIME_ZONE,
                    ],
                )->attributesToArray();
            });
        }

        if ($cached instanceof self) {
            return $cached;
        }

        return self::fromCachedAttributes(is_array($cached) ? $cached : []);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private static function fromCachedAttributes(array $attributes): self
    {
        $settings = new self;
        $settings->setRawAttributes($attributes, true);
        $settings->exists = true;

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
