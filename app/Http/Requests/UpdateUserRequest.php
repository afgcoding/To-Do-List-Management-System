<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            ? ($this->user()?->can('update', $user) ?? false)
            : false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('department_id') === '') {
            $this->merge(['department_id' => null]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        $canManageRoles = $this->user()?->can('manageRoles', User::class) ?? false;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'role' => [
                Rule::requiredIf($canManageRoles),
                'nullable',
                'string',
                Rule::exists('roles', 'name'),
                Rule::when(! $this->user()?->hasRole('Super Admin'), Rule::notIn(['Super Admin'])),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
            'department_id' => ['nullable', 'exists:departments,id'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ];
    }
}
