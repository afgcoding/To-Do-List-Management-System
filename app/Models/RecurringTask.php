<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTask extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['repeat_interval' => 'integer', 'next_recurring_date' => 'date', 'is_active' => 'boolean'];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
