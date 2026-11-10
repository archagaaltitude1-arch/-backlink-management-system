<?php

namespace App\Http\Requests\Admin;

use App\Models\NicheSpecification;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NicheSpecificationRequest extends FormRequest
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
        $specification = $this->route('nicheSpecification');
        $specificationId = $specification instanceof NicheSpecification ? $specification->id : null;

        return [
            'specification_name' => ['required', 'string', 'max:150', Rule::unique('niche_specifications', 'specification_name')->ignore($specificationId)],
            'field_type' => ['required', Rule::in(['number', 'text', 'boolean', 'select'])],
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
