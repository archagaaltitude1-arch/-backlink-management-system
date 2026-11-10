<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
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
        $service = $this->route('service');
        $serviceId = $service instanceof Service ? $service->id : null;

        return [
            'service_name' => ['required', 'string', 'max:150', Rule::unique('services', 'service_name')->ignore($serviceId)],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'credit_cost' => ['required', 'integer', 'min:0'],
        ];
    }
}
