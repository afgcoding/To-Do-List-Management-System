<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\AttachmentObserver;
use Database\Factories\AttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[ObservedBy([AttachmentObserver::class])]
class Attachment extends Model
{
    /** @use HasFactory<AttachmentFactory> */
    use HasFactory;

    public const MAX_KILOBYTES = 10240;

    public const ALLOWED_EXTENSIONS = 'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip,txt';

    /** @var list<string> */
    public const IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    protected $fillable = [
        'task_id',
        'user_id',
        'comment_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    protected function casts(): array
    {
        return ['file_size' => 'integer'];
    }

    protected static function booted(): void
    {
        static::deleting(function (Attachment $attachment): void {
            if (filled($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'task_id', 'task_id');
    }

    public function isImage(): bool
    {
        return in_array(strtolower($this->file_type), self::IMAGE_TYPES, true);
    }

    public function formattedSize(): string
    {
        if ($this->file_size >= 1024) {
            return number_format($this->file_size / 1024, 1).' MB';
        }

        return $this->file_size.' KB';
    }

    public function publicUrl(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
