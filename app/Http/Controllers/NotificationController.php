<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function feed(Request $request): JsonResponse
    {
        return response()->json($this->payload($request));
    }

    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $notification = $request->user()?->notifications()->whereKey($id)->first();

        if ($notification instanceof DatabaseNotification) {
            $notification->markAsRead();
        }

        return $this->respond($request);
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()?->unreadNotifications->markAsRead();

        return $this->respond($request);
    }

    public function destroyAll(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()?->notifications()->delete();

        return $this->respond($request);
    }

    /**
     * @return array{unread_count: int, notifications: list<array<string, mixed>>}
     */
    public static function payloadFor(?User $user): array
    {
        if ($user === null || ! Schema::hasTable('notifications')) {
            return [
                'unread_count' => 0,
                'notifications' => [],
            ];
        }

        $notifications = $user->notifications()->latest()->limit(20)->get();

        return [
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => self::serialize($notifications),
        ];
    }

    /**
     * @return array{unread_count: int, notifications: list<array<string, mixed>>}
     */
    private function payload(Request $request): array
    {
        $user = $request->user();

        return self::payloadFor($user instanceof User ? $user : null);
    }

    /**
     * @param  Collection<int, DatabaseNotification>  $notifications
     * @return list<array<string, mixed>>
     */
    private static function serialize(Collection $notifications): array
    {
        return $notifications->map(function (DatabaseNotification $notification): array {
            $data = $notification->data;

            return [
                'id' => $notification->id,
                'title' => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? '',
                'url' => $data['url'] ?? route('tasks.index'),
                'type' => $data['type'] ?? 'info',
                'actor_name' => $data['actor_name'] ?? null,
                'actor_avatar' => $data['actor_avatar'] ?? null,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at?->diffForHumans(),
            ];
        })->all();
    }

    private function respond(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json($this->payload($request));
        }

        return back();
    }
}
