<?php

namespace App\Http\Requests\Admin;

use App\Models\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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
        $permission = $this->route('permission');
        $permissionId = $permission instanceof Permission ? $permission->id : null;

        return [
            'permission_name' => ['required', 'string', 'max:150', Rule::unique('permissions', 'permission_name')->ignore($permissionId)],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
