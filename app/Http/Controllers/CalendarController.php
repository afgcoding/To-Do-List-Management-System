<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCalendarDueDateRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $actor = $request->user();
        $cursor = $this->cursor($request);
        $start = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $tasks = Task::query()
            ->visibleTo($actor)
            ->with(['assignedUsers:id,name,avatar'])
            ->where(function ($query) use ($start, $end): void {
                $query->whereNull('due_date')
                    ->orWhereBetween('due_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
            })
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();

        $days = collect();
        $day = $start->copy();
        while ($day->lte($end)) {
            $days->push($day->copy());
            $day->addDay();
        }

        return view('calendar.index', [
            'pageTitle' => 'Calendar',
            'cursor' => $cursor,
            'days' => $days,
            'previousUrl' => route('calendar.index', [
                'year' => $cursor->copy()->subMonth()->year,
                'month' => $cursor->copy()->subMonth()->month,
            ]),
            'nextUrl' => route('calendar.index', [
                'year' => $cursor->copy()->addMonth()->year,
                'month' => $cursor->copy()->addMonth()->month,
            ]),
            'calendarTasks' => $tasks->map(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'due' => $task->due_date?->toDateString(),
                'priority' => $task->priority->value,
                'priorityLabel' => $task->priority->label(),
                'canMove' => $actor->can('update', $task),
                'url' => route('tasks.show', $task),
            ])->values(),
        ]);
    }

    public function updateDueDate(UpdateCalendarDueDateRequest $request, Task $task): JsonResponse
    {
        $due = Carbon::parse($request->validated('due_date'))->startOfDay();

        if ($task->due_date !== null) {
            $due->setTimeFrom($task->due_date);
        }

        $task->update([
            'due_date' => $due,
        ]);

        return response()->json([
            'ok' => true,
            'due_date' => $task->due_date?->toDateString(),
            'formatted' => format_date($task->due_date),
        ]);
    }

    private function cursor(Request $request): Carbon
    {
        $year = $request->integer('year', (int) now()->year);
        $month = $request->integer('month', (int) now()->month);

        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            return now()->startOfMonth();
        }

        return Carbon::createFromDate($year, $month, 1)->startOfMonth();
    }
}
