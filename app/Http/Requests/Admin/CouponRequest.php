<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActiveAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:40'],
            'generate_code' => ['nullable', 'boolean'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'min:0.01'],
            'applicable_packages' => ['required', 'array', 'min:1'],
            'applicable_packages.*' => ['string', Rule::in(['all', 'starter', 'pro', 'professional', 'enterprise'])],
            'purchase_type' => ['required', Rule::in(['individual', 'package', 'both'])],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'generate_code' => $this->boolean('generate_code'),
            'is_active' => $this->boolean('is_active'),
            'code' => strtoupper(trim((string) $this->input('code', ''))),
            'maximum_discount_amount' => $this->input('maximum_discount_amount') === '' ? null : $this->input('maximum_discount_amount'),
            'usage_limit' => $this->input('usage_limit') === '' ? null : $this->input('usage_limit'),
            'starts_at' => $this->input('starts_at') === '' ? null : $this->input('starts_at'),
            'expires_at' => $this->input('expires_at') === '' ? null : $this->input('expires_at'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $code = (string) $this->input('code', '');

            if ($code === '' && ! $this->boolean('generate_code')) {
                $validator->errors()->add('code', 'Enter a coupon code or generate one.');
            }

            if ($this->input('discount_type') === 'percentage' && (float) $this->input('discount_value') > 100) {
                $validator->errors()->add('discount_value', 'A percentage discount cannot be more than 100.');
            }

            if ($this->filled('starts_at') && $this->filled('expires_at') && strtotime((string) $this->input('expires_at')) < strtotime((string) $this->input('starts_at'))) {
                $validator->errors()->add('expires_at', 'The expiry date must be on or after the start date.');
            }

            if ($code === '') {
                return;
            }

            $existing = Coupon::findByCode($code);
            $current = $this->route('coupon');

            if ($existing !== null && (! $current instanceof Coupon || $existing->id !== $current->id)) {
                $validator->errors()->add('code', 'That coupon code is already in use.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function couponAttributes(): array
    {
        $packages = $this->input('applicable_packages', []);
        $code = (string) $this->input('code', '');

        return [
            'code' => $code !== '' ? $code : Coupon::uniqueCode(),
            'type' => 'promotional',
            'discount_type' => $this->input('discount_type'),
            'discount_value' => $this->input('discount_value'),
            'maximum_discount_amount' => $this->input('maximum_discount_amount'),
            'minimum_order_amount' => $this->input('minimum_order_amount'),
            'starts_at' => $this->input('starts_at'),
            'expires_at' => $this->input('expires_at'),
            'applicable_packages' => in_array('all', $packages, true) ? null : array_values($packages),
            'purchase_type' => $this->input('purchase_type'),
            'usage_limit' => $this->input('usage_limit'),
            'per_customer_limit' => $this->input('per_customer_limit'),
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
