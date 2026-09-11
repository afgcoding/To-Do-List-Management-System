<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Attachment;

class AttachmentObserver
{
    public function created(Attachment $attachment): void
    {
        ActivityLog::record(
            $attachment->task_id,
            'uploaded_attachment',
            'attached file '.$attachment->file_name,
        );
    }
}
