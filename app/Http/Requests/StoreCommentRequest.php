<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Attachment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
            'task_id' => ['required', 'exists:tasks,id'],
            'comment' => ['required', 'string', 'max:5000'],
            'files' => ['nullable', 'array', 'max:8'],
            'files.*' => [
                'file',
                'max:'.Attachment::MAX_KILOBYTES,
                'mimes:'.Attachment::ALLOWED_EXTENSIONS,
            ],
        ];
    }
}
