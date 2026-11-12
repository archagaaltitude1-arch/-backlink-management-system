<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function rewardCoupon(): HasOne
    {
        return $this->hasOne(Coupon::class, 'reward_order_id');
    }

    public function confirmPayment(): void
    {
        $this->update(['payment_status' => 'paid', 'order_status' => 'awaiting_approval']);

        $this->invoice()->firstOrCreate([], [
            'customer_id' => $this->customer_id,
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'total_amount' => $this->total_amount,
            'issued_at' => now(),
        ]);

        $this->rewardCoupon()->firstOrCreate([], [
            'customer_id' => $this->customer_id,
            'code' => 'THANKYOU-'.Str::upper(Str::random(16)),
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'usage_limit' => 1,
            'per_customer_limit' => 1,
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ]);
    }

    public function reservePaymentCoupon(User $customer): void
    {
        if (! $this->coupon_code || $customer->couponUsages()->where('order_id', $this->id)->exists()) {
            return;
        }

        $coupon = Coupon::query()->where('code', $this->coupon_code)->lockForUpdate()->first();
        $subtotal = (float) $this->total_amount + (float) $this->discount_amount;
        if (! $coupon || ! $coupon->isAvailableTo($customer, $subtotal)) {
            throw ValidationException::withMessages(['coupon_code' => 'This discount is no longer available. Start a new checkout to continue.']);
        }

        $customer->couponUsages()->create([
            'coupon_id' => $coupon->id,
            'order_id' => $this->id,
            'discount_amount' => $this->discount_amount,
        ]);
    }
}
