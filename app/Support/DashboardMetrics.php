<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class DashboardMetrics
{
    /**
     * @return array{
     *     canViewTeamAnalytics: bool,
     *     kpis: array{active: int, activeTrend: int, assigned: int, pending: int, overdue: int},
     *     distribution: list<array{key: string, label: string, count: int, color: string}>,
     *     velocity: int,
     *     workload: Collection<int, User>,
     *     departments: Collection<int, Department>,
     *     categories: Collection<int, Category>,
     *     upcoming: Collection<int, Task>,
     *     activities: Collection<int, ActivityLog>
     * }
     */
    public function for(User $user): array
    {
        $openStatuses = [TaskStatus::Todo->value, TaskStatus::InProgress->value];
        $counts = Task::query()
            ->visibleTo($user)
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('todo', 'in_progress') THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo")
            ->selectRaw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled")
            ->selectRaw("SUM(CASE WHEN due_date IS NOT NULL AND due_date < ? AND status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) as overdue", [now()->startOfDay()])
            ->first();

        $thisWeek = Task::query()
            ->visibleTo($user)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $previousWeek = Task::query()
            ->visibleTo($user)
            ->where('created_at', '>=', now()->subDays(14))
            ->where('created_at', '<', now()->subDays(7))
            ->count();

        $assigned = Task::query()
            ->visibleTo($user)
            ->assignedTo($user->id)
            ->whereIn('status', $openStatuses)
            ->count();

        $subtaskTotals = Subtask::query()
            ->whereHas('task', fn (Builder $query): Builder => $query->visibleTo($user))
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed')
            ->first();

        $completedTasks = (int) ($counts->completed ?? 0);
        $openAndDone = max(1, (int) ($counts->total ?? 0) - (int) ($counts->cancelled ?? 0));
        $subtaskTotal = (int) ($subtaskTotals->total ?? 0);
        $subtaskCompleted = (int) ($subtaskTotals->completed ?? 0);
        $velocityDenominator = $openAndDone + $subtaskTotal;
        $velocity = $velocityDenominator === 0
            ? 0
            : (int) round((($completedTasks + $subtaskCompleted) / $velocityDenominator) * 100);

        $canViewTeamAnalytics = $user->can('reports.view-team') || $user->can('tasks.view-all');

        $workload = collect();
        $departments = collect();
        $categories = collect();

        if ($canViewTeamAnalytics) {
            $workload = User::query()
                ->select(['id', 'name', 'avatar'])
                ->whereHas('assignedTasks', fn (Builder $query): Builder => $query->visibleTo($user))
                ->withCount([
                    'assignedTasks as assigned_count' => fn (Builder $query): Builder => $query->visibleTo($user),
                    'assignedTasks as completed_count' => fn (Builder $query): Builder => $query->visibleTo($user)->where('status', TaskStatus::Completed->value),
                    'assignedTasks as pending_count' => fn (Builder $query): Builder => $query->visibleTo($user)->whereIn('status', $openStatuses),
                ])
                ->orderByDesc('pending_count')
                ->orderBy('name')
                ->limit(8)
                ->get();

            $departments = Department::query()
                ->select(['id', 'name'])
                ->withCount(['tasks as tasks_count' => fn (Builder $query): Builder => $query->visibleTo($user)])
                ->orderByDesc('tasks_count')
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->filter(fn (Department $department): bool => $department->tasks_count > 0)
                ->values();

            $categories = Category::query()
                ->select(['id', 'name', 'color'])
                ->withCount(['tasks as tasks_count' => fn (Builder $query): Builder => $query->visibleTo($user)])
                ->orderByDesc('tasks_count')
                ->orderBy('name')
                ->limit(8)
                ->get()
                ->filter(fn (Category $category): bool => $category->tasks_count > 0)
                ->values();
        }

        return [
            'canViewTeamAnalytics' => $canViewTeamAnalytics,
            'kpis' => [
                'active' => (int) ($counts->active ?? 0),
                'activeTrend' => $this->trendPercent($thisWeek, $previousWeek),
                'assigned' => $assigned,
                'pending' => (int) ($counts->todo ?? 0) + (int) ($counts->in_progress ?? 0),
                'overdue' => (int) ($counts->overdue ?? 0),
            ],
            'distribution' => [
                ['key' => 'todo', 'label' => TaskStatus::Todo->label(), 'count' => (int) ($counts->todo ?? 0), 'color' => '#64748b'],
                ['key' => 'in_progress', 'label' => TaskStatus::InProgress->label(), 'count' => (int) ($counts->in_progress ?? 0), 'color' => '#4f46e5'],
                ['key' => 'completed', 'label' => TaskStatus::Completed->label(), 'count' => (int) ($counts->completed ?? 0), 'color' => '#059669'],
                ['key' => 'cancelled', 'label' => TaskStatus::Cancelled->label(), 'count' => (int) ($counts->cancelled ?? 0), 'color' => '#e11d48'],
                ['key' => 'overdue', 'label' => 'Overdue', 'count' => (int) ($counts->overdue ?? 0), 'color' => '#be123c'],
            ],
            'velocity' => max(0, min(100, $velocity)),
            'workload' => $workload,
            'departments' => $departments,
            'categories' => $categories,
            'upcoming' => Task::query()
                ->visibleTo($user)
                ->whereNotNull('due_date')
                ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
                ->whereBetween('due_date', [now()->startOfDay(), now()->addDay()->endOfDay()])
                ->orderBy('due_date')
                ->orderByDesc('id')
                ->limit(8)
                ->get(['id', 'title', 'due_date', 'priority', 'status']),
            'activities' => ActivityLog::query()
                ->with(['user:id,name,avatar', 'task:id,title'])
                ->whereHas('task', fn (Builder $query): Builder => $query->visibleTo($user))
                ->latest('id')
                ->limit(20)
                ->get(),
        ];
    }

    private function trendPercent(int $current, int $previous): int
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
