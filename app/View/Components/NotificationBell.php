<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Http\Controllers\NotificationController;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NotificationBell extends Component
{
    public function render(): View
    {
        $payload = NotificationController::payloadFor(auth()->user());

        return view('components.notification-bell', [
            'unreadCount' => $payload['unread_count'],
            'notifications' => $payload['notifications'],
            'feedUrl' => route('notifications.feed'),
            'readAllUrl' => route('notifications.read-all'),
            'clearUrl' => route('notifications.clear'),
            'readUrlTemplate' => url('/notifications/__ID__/read'),
        ]);
    }
}
