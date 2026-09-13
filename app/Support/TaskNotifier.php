<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\TaskStatus;
use App\Enums\UserStatus;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Notifications\CommentAddedNotification;
use App\Notifications\TaskAlertNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCompletedNotification;
use App\Notifications\TaskReassignedNotification;
use App\Notifications\TaskUpdatedNotification;
use App\Notifications\UserMentionedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

final class TaskNotifier
{
    /**
     * @param  list<int>  $previousIds
     * @param  list<int>  $nextIds
     */
    public static function assigneesChanged(Task $task, array $previousIds, array $nextIds): void
    {
        $added = array_values(array_diff($nextIds, $previousIds));

        if ($added === []) {
            return;
        }

        $notification = $previousIds === []
            ? new TaskAssignedNotification($task, self::actor())
            : new TaskReassignedNotification($task, self::actor());

        self::send(self::usersByIds($added), $notification);
    }

    public static function taskSaved(Task $task): void
    {
        if ($task->wasChanged('status') && $task->status === TaskStatus::Completed) {
            self::send(self::participants($task), new TaskCompletedNotification($task, self::actor()));
        }

        $critical = ['title', 'priority', 'due_date', 'start_date'];

        if ($task->wasChanged($critical)) {
            $changed = collect($critical)
                ->filter(fn (string $field): bool => $task->wasChanged($field))
                ->map(fn (string $field): string => str_replace('_', ' ', $field))
                ->implode(', ');

            self::send(
                self::participants($task),
                new TaskUpdatedNotification(
                    $task,
                    self::actor(),
                    'Updated '.$changed.' on "'.$task->title.'".',
                ),
            );
        }
    }

    public static function commentPosted(Comment $comment): void
    {
        $comment->loadMissing(['task.assignedUsers', 'task.creator', 'user']);
        $task = $comment->task;

        if (! $task instanceof Task) {
            return;
        }

        $mentioned = $comment->mentionedUsers();
        $mentionedIds = $mentioned->pluck('id')->all();

        if ($mentioned->isNotEmpty()) {
            self::send($mentioned, new UserMentionedNotification($task, $comment->user));
        }

        $participants = self::participants($task)
            ->reject(fn (User $user): bool => in_array($user->id, $mentionedIds, true));

        self::send($participants, new CommentAddedNotification($task, $comment->user));
    }

    /**
     * @return Collection<int, User>
     */
    public static function reminderRecipients(Task $task): Collection
    {
        $task->loadMissing(['assignedUsers', 'creator']);

        $assignees = $task->assignedUsers
            ->filter(fn (User $user): bool => $user->isActive())
            ->values();

        if ($assignees->isNotEmpty()) {
            return $assignees;
        }

        $creator = $task->creator;

        return $creator instanceof User && $creator->isActive()
            ? collect([$creator])
            : collect();
    }

    /**
     * @param  iterable<int, User>  $users
     */
    public static function send(iterable $users, TaskAlertNotification $notification): void
    {
        $actorId = auth()->id();

        $recipients = collect($users)
            ->filter(fn (mixed $user): bool => $user instanceof User && $user->isActive())
            ->unique('id')
            ->when(
                $actorId !== null,
                fn (Collection $collection): Collection => $collection->reject(
                    fn (User $user): bool => $user->id === $actorId,
                ),
            )
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, $notification);
    }

    /**
     * @return Collection<int, User>
     */
    private static function participants(Task $task): Collection
    {
        $task->loadMissing(['assignedUsers', 'creator']);

        return collect([$task->creator])
            ->merge($task->assignedUsers)
            ->filter(fn (mixed $user): bool => $user instanceof User)
            ->unique('id')
            ->values();
    }

    /**
     * @param  list<int>  $ids
     * @return Collection<int, User>
     */
    private static function usersByIds(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->where('status', UserStatus::Active)
            ->get();
    }

    private static function actor(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }
}
