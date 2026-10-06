<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RecurringTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessRecurringTasks extends Command
{
    protected $signature = 'tasks:process-recurring';

    protected $description = 'Duplicate due recurring tasks and advance their next run dates';

    public function handle(): int
    {
        $processed = 0;

        RecurringTask::query()
            ->with(['task.assignedUsers'])
            ->where('is_active', true)
            ->whereDate('next_recurring_date', '<=', now()->toDateString())
            ->orderBy('id')
            ->each(function (RecurringTask $schedule) use (&$processed): void {
                if ($schedule->task === null) {
                    return;
                }

                DB::transaction(function () use ($schedule, &$processed): void {
                    $locked = RecurringTask::query()->whereKey($schedule->id)->lockForUpdate()->first();

                    if ($locked === null || ! $locked->is_active || $locked->next_recurring_date === null) {
                        return;
                    }

                    if ($locked->next_recurring_date->gt(now()->startOfDay())) {
                        return;
                    }

                    $locked->loadMissing(['task.assignedUsers']);
                    $locked->spawnOccurrence();
                    $locked->advanceNextDate();
                    $processed++;
                });
            });

        $this->info("Processed {$processed} recurring ".($processed === 1 ? 'task' : 'tasks').'.');

        return self::SUCCESS;
    }
}
