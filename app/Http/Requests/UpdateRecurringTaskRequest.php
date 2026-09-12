<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RecurrenceType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecurringTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recurrence_type' => ['required', Rule::enum(RecurrenceType::class)],
            'repeat_interval' => ['required', 'integer', 'min:1'],
            'next_recurring_date' => ['required', 'date'],
        ];
    }
}
