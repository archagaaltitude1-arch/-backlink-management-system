<?php

namespace App\Http\Requests\Admin;

use App\Models\CreditPricing;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreditPricingRequest extends FormRequest
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
        $pricing = $this->route('creditPricing');
        $pricingId = $pricing instanceof CreditPricing ? $pricing->id : null;

        return [
            'credit_amount' => ['required', 'integer', 'min:1', Rule::unique('credit_pricing', 'credit_amount')->ignore($pricingId)],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
