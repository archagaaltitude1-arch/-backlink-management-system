<?php

namespace App\Http\Requests\Admin;

use App\Models\Package;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
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
        $package = $this->route('package');
        $packageId = $package instanceof Package ? $package->id : null;

        return [
            'package_name' => ['required', 'string', 'max:150', Rule::unique('packages', 'package_name')->ignore($packageId)],
            'price' => ['required', 'numeric', 'min:0'],
            'credit_amount' => ['required', 'integer', 'min:0'],
        ];
    }
}
