<?php

namespace App\Models;

use App\Support\CustomerDiscounts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = ['order_number', 'customer_id', 'session_id', 'total_amount', 'discount_amount', 'package_discount_amount', 'coupon_code', 'applied_coupon_codes', 'offer_type', 'payment_status', 'order_status', 'accepted_at', 'completed_at'];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'package_discount_amount' => 'decimal:2',
            'applied_coupon_codes' => 'array',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return list<string>
     */
    public function appliedCouponCodes(): array
    {
        $stored = $this->applied_coupon_codes;

        if (is_array($stored) && $stored !== []) {
            return array_values(array_filter($stored, fn (mixed $code): bool => is_string($code) && $code !== ''));
        }

        if (filled($this->coupon_code) && $this->offer_type !== 'first_order') {
            return [Str::upper((string) $this->coupon_code)];
        }

        return [];
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
        $needsDetails = $this->items()->where('target_url', '')->exists();
        $this->update(['payment_status' => 'paid', 'order_status' => $needsDetails ? 'awaiting_details' : 'awaiting_approval']);

        $this->invoice()->firstOrCreate([], [
            'customer_id' => $this->customer_id,
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'total_amount' => $this->total_amount,
            'issued_at' => now(),
        ]);

        if ($needsDetails) {
            return;
        }

        app(CustomerDiscounts::class)->rewardAfterPayment($this->fresh());
    }

    public function reservePaymentCoupon(User $customer): void
    {
        app(CustomerDiscounts::class)->redeem($this, $customer);
    }
}
