<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['customer_id', 'session_id', 'status', 'coupon_code', 'coupon_discount', 'checkout_coupon_id'];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
