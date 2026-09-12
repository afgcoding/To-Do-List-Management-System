<?php

namespace Database\Factories;

use App\Enums\RecurrenceType;
use App\Models\RecurringTask;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTask>
 */
class RecurringTaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'recurrence_type' => RecurrenceType::Weekly,
            'repeat_interval' => 1,
            'next_recurring_date' => now()->toDateString(),
            'is_active' => true,
        ];
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
