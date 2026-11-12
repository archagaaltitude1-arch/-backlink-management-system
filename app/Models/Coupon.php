<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount_type', 'discount_value', 'minimum_order_amount', 'usage_limit', 'per_customer_limit', 'expires_at', 'is_active', 'customer_id', 'reward_order_id'];

    protected function casts(): array
    {
        return ['discount_value' => 'decimal:2', 'minimum_order_amount' => 'decimal:2', 'expires_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isAvailableTo(User $customer, float $subtotal): bool
    {
        if (($this->customer_id !== null && (int) $this->customer_id !== $customer->id)
            || ! $this->is_active || $this->expires_at?->isPast() || $subtotal < (float) $this->minimum_order_amount) {
            return false;
        }

        if ($this->usage_limit !== null && $this->usages()->count() >= $this->usage_limit) {
            return false;
        }

        return $this->usages()->where('customer_id', $customer->id)->count() < $this->per_customer_limit;
    }
}
