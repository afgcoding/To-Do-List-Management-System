<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Comment;

class CommentObserver
{
    public function created(Comment $comment): void
    {
        ActivityLog::record($comment->task_id, 'added_comment', 'added a comment');
    }
}
