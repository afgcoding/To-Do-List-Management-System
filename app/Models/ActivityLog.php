<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ['task_id', 'user_id', 'action', 'description'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Persist an audit row. Actor is the signed-in user, then the task creator, then the first user.
     */
    public static function record(?int $taskId, string $action, string $description, ?int $userId = null): void
    {
        $userId ??= auth()->id();

        if ($userId === null && $taskId !== null) {
            $userId = Task::query()->whereKey($taskId)->value('creator_id');
        }

        $userId ??= User::query()->orderBy('id')->value('id');

        if ($userId === null) {
            return;
        }

        self::query()->create([
            'task_id' => $taskId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
        ]);
    }
}
