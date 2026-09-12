<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RecurringTask;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('tasks:process-recurring')]
#[Description('Duplicate due recurring tasks and advance their next run dates')]
class ProcessRecurringTasks extends Command
{
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
