<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Support\DashboardMetrics;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetrics $metrics) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $actor = $request->user();

        return view('dashboard', [
            'pageTitle' => 'Dashboard',
            ...$this->metrics->for($actor),
            'workspaceTasks' => $this->workspaceTasks($request, $actor),
            'workspaceUsers' => User::query()->orderBy('name')->get(['id', 'name']),
            'workspaceCategories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'workspaceLayout' => $request->string('layout', 'list')->toString() === 'grid' ? 'grid' : 'list',
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Task>
     */
    private function workspaceTasks(Request $request, User $actor): LengthAwarePaginator
    {
        return Task::query()
            ->visibleTo($actor)
            ->with(['creator', 'assignedUsers', 'category', 'department', 'tags'])
            ->withCount([
                'subtasks',
                'subtasks as completed_subtasks_count' => fn (Builder $query) => $query->where('is_completed', true),
            ])
            ->search($request->string('search')->toString() ?: null)
            ->statusIs($request->string('status')->toString() ?: null)
            ->priorityIs($request->string('priority')->toString() ?: null)
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->assignedTo(
                $request->filled('assigned_user_id')
                    ? $request->integer('assigned_user_id')
                    : ($request->filled('user_id') ? $request->integer('user_id') : null),
            )
            ->dueBetween(
                $request->string('due_from')->toString() ?: null,
                $request->string('due_to')->toString() ?: null,
            )
            ->sortedBy(
                $request->string('sort_by', 'created_at')->toString(),
                $request->string('sort_order', 'desc')->toString(),
            )
            ->paginate(10)
            ->withQueryString();
    }
}
