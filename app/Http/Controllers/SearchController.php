<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));
        $actor = $request->user();

        if ($term === '' || $actor === null) {
            return response()->json($this->emptyPayload());
        }

        return response()->json([
            'tasks' => $this->tasks($actor, $term),
            'users' => $this->users($actor, $term),
            'tags' => $this->tags($actor, $term),
        ]);
    }

    /**
     * @return array{tasks: list<array<string, mixed>>, users: list<array<string, mixed>>, tags: list<array<string, mixed>>}
     */
    private function emptyPayload(): array
    {
        return [
            'tasks' => [],
            'users' => [],
            'tags' => [],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tasks(User $actor, string $term): array
    {
        if (! $actor->can('viewAny', Task::class)) {
            return [];
        }

        return Task::query()
            ->visibleTo($actor)
            ->search($term)
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'status', 'priority'])
            ->map(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'status_label' => $task->status->label(),
                'priority' => $task->priority->value,
                'priority_label' => $task->priority->label(),
                'url' => route('tasks.show', $task),
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function users(User $actor, string $term): array
    {
        if (! $actor->can('viewAny', User::class)) {
            return [];
        }

        $like = $this->like($term);

        return User::query()
            ->where('name', 'like', $like)
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'avatar'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar_url,
                'url' => route('users.show', $user),
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function tags(User $actor, string $term): array
    {
        if (! $actor->can('viewAny', Tag::class)) {
            return [];
        }

        $like = $this->like($term);

        return Tag::query()
            ->where('name', 'like', $like)
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name'])
            ->map(fn (Tag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'url' => route('tags.show', $tag),
            ])
            ->all();
    }

    private function like(string $term): string
    {
        return '%'.addcslashes($term, '%_\\').'%';
    }
}
