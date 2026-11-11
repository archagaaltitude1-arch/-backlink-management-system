<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['order_number', 'customer_id', 'session_id', 'total_amount', 'discount_amount', 'coupon_code', 'payment_status', 'order_status'];

    protected function casts(): array
    {
        return ['total_amount' => 'decimal:2', 'discount_amount' => 'decimal:2'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
