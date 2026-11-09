<?php

namespace App\Http\Requests\Admin;

use App\Models\Niche;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NicheRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $niche = $this->route('niche');
        $nicheId = $niche instanceof Niche ? $niche->id : null;

        return [
            'niche_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('niches', 'niche_name')->ignore($nicheId),
            ],
            'description' => ['nullable', 'string'],
        ];
    }
}
