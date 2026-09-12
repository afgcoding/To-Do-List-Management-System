<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

trait PreparesTaskFormData
{
    protected function prepareForValidation(): void
    {
        foreach (['department_id', 'category_id', 'assigned_to'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        $this->merge([
            'is_recurring' => $this->boolean('is_recurring'),
        ]);
    }
}
