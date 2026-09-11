<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\SystemSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSystemSettingRequest extends FormRequest
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
            'company_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'date_format' => ['required', 'string', Rule::in(SystemSetting::DATE_FORMATS)],
            'time_zone' => ['required', 'timezone:all'],
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }
}
