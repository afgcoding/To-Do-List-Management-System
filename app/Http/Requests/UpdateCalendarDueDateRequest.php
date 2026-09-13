<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class UpdateCalendarDueDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('task');

        return $task instanceof Task
            && ($this->user()?->can('update', $task) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'due_date' => ['required', 'date'],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $task = $this->route('task');

                if (! $task instanceof Task || $validator->errors()->isNotEmpty()) {
                    return;
                }

                $due = Carbon::parse($this->input('due_date'))->startOfDay();

                if ($task->start_date !== null && $due->lt($task->start_date->copy()->startOfDay())) {
                    $validator->errors()->add('due_date', 'The due date must be on or after the start date.');
                }
            },
        ];
    }
}
