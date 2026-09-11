<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word().'.pdf';

        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'comment_id' => null,
            'file_name' => $name,
            'file_path' => 'attachments/'.$name,
            'file_type' => 'pdf',
            'file_size' => fake()->numberBetween(12, 480),
        ];
    }
}
