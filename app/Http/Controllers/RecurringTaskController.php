<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRecurringTaskRequest;
use App\Models\RecurringTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RecurringTaskController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()?->can('tasks.edit'), 403);

        $recurringTasks = RecurringTask::query()
            ->with('task')
            ->whereHas('task', fn ($query) => $query->visibleTo(request()->user()))
            ->latest('next_recurring_date')
            ->paginate(12);

        return view('recurring-tasks.index', [
            'recurringTasks' => $recurringTasks,
        ]);
    }

    public function update(UpdateRecurringTaskRequest $request, RecurringTask $recurringTask): RedirectResponse
    {
        $this->authorize('update', $recurringTask->task);
        $recurringTask->update($request->validated());

        return redirect()->route('recurring-tasks.index')->with('success', 'Recurring schedule updated.');
    }

    public function toggleActive(RecurringTask $recurringTask): RedirectResponse
    {
        $this->authorize('update', $recurringTask->task);
        $recurringTask->update([
            'is_active' => ! $recurringTask->is_active,
        ]);

        $label = $recurringTask->is_active ? 'resumed' : 'paused';

        return back()->with('success', "Recurring schedule {$label}.");
    }

    public function destroy(RecurringTask $recurringTask): RedirectResponse
    {
        $this->authorize('delete', $recurringTask->task);
        $recurringTask->delete();

        return redirect()->route('recurring-tasks.index')->with('success', 'Recurring schedule deleted.');
    }
}
