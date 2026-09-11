<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    // Work classification label. Categories are the only lookup that stores a HEX color.
    protected $fillable = ['name', 'color'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
