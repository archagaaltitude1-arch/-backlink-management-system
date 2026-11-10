<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount_type', 'discount_value', 'minimum_order_amount', 'usage_limit', 'per_customer_limit', 'expires_at', 'is_active'];

    protected function casts(): array
    {
        return ['discount_value' => 'decimal:2', 'minimum_order_amount' => 'decimal:2', 'expires_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }
}
