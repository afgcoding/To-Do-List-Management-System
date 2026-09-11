<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\CommentObserver;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

#[ObservedBy([CommentObserver::class])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    protected $fillable = ['task_id', 'user_id', 'comment'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'task_id', 'task_id');
    }

    /**
     * Escape the comment, then wrap @mentions in highlight markup.
     *
     * @param  Collection<int, string>|list<string>  $userNames
     */
    public function highlightedHtml(Collection|array $userNames = []): HtmlString
    {
        $safe = e($this->comment);
        $names = Collection::wrap($userNames)
            ->filter()
            ->unique()
            ->sortByDesc(fn (string $name): int => mb_strlen($name))
            ->values();

        foreach ($names as $name) {
            $safe = str_replace(
                '@'.e($name),
                '<span class="rounded bg-indigo-50 px-1.5 py-0.5 font-semibold text-indigo-600">@'.e($name).'</span>',
                $safe,
            );
        }

        $safe = preg_replace(
            '/(?<![\w>])@([\p{L}\p{N}._-]{2,40})/u',
            '<span class="rounded bg-indigo-50 px-1.5 py-0.5 font-semibold text-indigo-600">@$1</span>',
            $safe,
        ) ?? $safe;

        return new HtmlString($safe);
    }
}
