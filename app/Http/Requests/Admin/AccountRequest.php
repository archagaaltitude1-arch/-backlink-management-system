<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : null;
        $audience = $this->route('audience') === 'staff' ? 'staff' : 'customer';

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$userId === null ? 'required' : 'nullable', 'string', 'min:8'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ];

        if ($audience === 'staff') {
            $rules['role_id'] = [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($query) => $query->whereNotIn('role_name', ['Customer', 'Admin'])),
            ];
        }

        return $rules;
    }
}
