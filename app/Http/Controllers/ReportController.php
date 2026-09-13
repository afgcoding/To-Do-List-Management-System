<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('reports.view-team'), 403);

        return view('reports.index', [
            'pageTitle' => 'Reports',
            'tasks' => $this->reportTasks($request),
            'canExport' => $request->user()->can('reports.export'),
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->can('reports.export'), 403);

        $tasks = $this->reportTasks($request);
        $filename = 'task-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($tasks): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Title', 'Status', 'Priority', 'Due date', 'Department', 'Category', 'Assignees']);

            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->title,
                    $task->status->label(),
                    $task->priority->label(),
                    format_date($task->due_date) ?? '',
                    $task->department->name ?? '',
                    $task->category->name ?? '',
                    $task->assignedUsers->pluck('name')->implode(', '),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function print(Request $request): View
    {
        abort_unless($request->user()?->can('reports.export'), 403);

        return view('reports.print', [
            'pageTitle' => 'Task report',
            'tasks' => $this->reportTasks($request),
            'generatedAt' => format_date(now()),
        ]);
    }

    /**
     * @return Collection<int, Task>
     */
    private function reportTasks(Request $request): Collection
    {
        return Task::query()
            ->visibleTo($request->user())
            ->with(['department:id,name', 'category:id,name', 'assignedUsers:id,name'])
            ->orderByDesc('due_date')
            ->orderByDesc('id')
            ->limit(500)
            ->get();
    }
}
