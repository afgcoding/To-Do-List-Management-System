<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends StoreTaskRequest
{
    /**
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $task = $this->route('task');
        $isAutomatedComplete = $task?->status === TaskStatus::Completed;

        // Completed tasks may omit status on edit; still never accept Completed from input.
        $rules['status'] = [
            Rule::requiredIf(! $isAutomatedComplete),
            'nullable',
            Rule::enum(TaskStatus::class)->except(TaskStatus::Completed),
        ];

        return $rules;
    }
}
