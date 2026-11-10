<?php

namespace App\Http\Requests\Admin;

use App\Models\Website;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebsiteRequest extends FormRequest
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
        $website = $this->route('website');
        $websiteId = $website instanceof Website ? $website->id : null;

        return [
            'website_name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255', Rule::unique('websites', 'domain')->ignore($websiteId)],
            'url' => ['required', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'credit_cost' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive', 'pending'])],
            'niche_ids' => ['nullable', 'array'],
            'niche_ids.*' => ['integer', 'exists:niches,id'],
            'DR' => ['required', 'numeric', 'min:0', 'max:100'],
            'DA' => ['required', 'numeric', 'min:0', 'max:100'],
            'TF' => ['required', 'numeric', 'min:0', 'max:100'],
            'CF' => ['required', 'numeric', 'min:0', 'max:100'],
            'traffic' => ['required', 'integer', 'min:0'],
            'spam_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'backlinks' => ['required', 'integer', 'min:0'],
        ];
    }
}
