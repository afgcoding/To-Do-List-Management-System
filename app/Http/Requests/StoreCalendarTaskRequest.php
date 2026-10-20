<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Http\Requests\Concerns\ConvertsCalendarFormDates;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCalendarTaskRequest extends FormRequest
{
    use ConvertsCalendarFormDates;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Task::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->convertCalendarDateFields();
    }

    /**
     * @return list<string>
     */
    protected function calendarDateFields(): array
    {
        return ['due_date'];
    }

    /**
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'assigned_users' => ['nullable', 'array'],
            'assigned_users.*' => ['integer', 'exists:users,id'],
        ];
    }
}
