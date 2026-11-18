<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'discount_type',
        'discount_value',
        'maximum_discount_amount',
        'minimum_order_amount',
        'starts_at',
        'applicable_packages',
        'purchase_type',
        'usage_limit',
        'per_customer_limit',
        'expires_at',
        'expiry_notified_at',
        'saved_for_later_at',
        'is_active',
        'customer_id',
        'milestone',
        'reward_order_id',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'maximum_discount_amount' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'expiry_notified_at' => 'datetime',
            'saved_for_later_at' => 'datetime',
            'is_active' => 'boolean',
            'milestone' => 'integer',
            'applicable_packages' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function rewardOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'reward_order_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(CouponEmailLog::class);
    }

    public function deliverTo(User $customer): void
    {
        $this->emailLogs()->create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'sent_at' => now(),
        ]);
    }

    public static function findByCode(string $code): ?self
    {
        $normalized = Str::upper(trim($code));

        if ($normalized === '') {
            return null;
        }

        return static::query()->whereRaw('LOWER(code) = ?', [Str::lower($normalized)])->first();
    }

    public static function uniqueCode(): string
    {
        do {
            $code = 'SAVE-'.Str::upper(Str::random(8));
        } while (static::query()->whereRaw('LOWER(code) = ?', [Str::lower($code)])->exists());

        return $code;
    }

    /**
     * @param  array{tiers?: array<int, string>, purchase?: string}  $context
     */
    public function isAvailableTo(User $customer, float $subtotal, array $context = []): bool
    {
        return $this->rejectionFor($customer, $subtotal, $context) === null;
    }

    /**
     * @param  array{tiers?: array<int, string>, purchase?: string}  $context
     */
    public function rejectionFor(?User $customer, float $subtotal, array $context = []): ?string
    {
        if (! $this->is_active || ($this->starts_at !== null && $this->starts_at->isFuture())) {
            return 'Invalid coupon code.';
        }

        if ($this->customer_id !== null && ($customer === null || (int) $this->customer_id !== $customer->id)) {
            return 'Invalid coupon code.';
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return 'Coupon has expired.';
        }

        if ($this->usage_limit !== null && $this->usages()->count() >= $this->usage_limit) {
            return 'Coupon usage limit reached.';
        }

        if ($customer !== null && $this->usages()->where('customer_id', $customer->id)->count() >= $this->per_customer_limit) {
            return 'You have already used this coupon.';
        }

        if (! $this->matchesCart($context)) {
            return 'Coupon is not applicable to this package.';
        }

        if ($subtotal < (float) $this->minimum_order_amount) {
            return 'Minimum order amount not reached.';
        }

        return null;
    }

    /**
     * Whether this coupon matches an order, ignoring usage limits.
     *
     * @param  array{tiers?: array<int, string>, purchase?: string}  $context
     */
    public function fitsCart(float $subtotal, array $context = []): bool
    {
        if (! $this->is_active || ($this->starts_at !== null && $this->starts_at->isFuture())) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        if (! $this->matchesCart($context)) {
            return false;
        }

        return $subtotal >= (float) $this->minimum_order_amount;
    }

    public function discountAmount(float $subtotal): float
    {
        $raw = $this->discount_type === 'percentage'
            ? $subtotal * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        if ($this->maximum_discount_amount !== null) {
            $raw = min($raw, (float) $this->maximum_discount_amount);
        }

        return max(0, min($subtotal, round($raw, 2)));
    }

    public function discountSummary(): string
    {
        if ($this->discount_type === 'percentage') {
            $summary = rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'% off';
        } else {
            $summary = '$'.number_format((float) $this->discount_value, 2).' off';
        }

        if ($this->maximum_discount_amount !== null) {
            $summary .= ', up to $'.number_format((float) $this->maximum_discount_amount, 2);
        }

        return $summary;
    }

    public function packageLabel(): string
    {
        $packages = $this->applicable_packages;

        if (! is_array($packages) || $packages === [] || in_array('all', $packages, true)) {
            return 'All packages';
        }

        $labels = [
            'starter' => 'Starter',
            'pro' => 'Pro',
            'professional' => 'Professional',
            'enterprise' => 'Enterprise',
        ];

        return implode(', ', array_map(fn (string $package): string => $labels[$package] ?? $package, $packages));
    }

    /**
     * @param  array{tiers?: array<int, string>, purchase?: string}  $context
     */
    private function matchesCart(array $context): bool
    {
        $purchase = (string) ($context['purchase'] ?? '');
        $tiers = $context['tiers'] ?? [];

        if ($purchase === '' && $tiers === []) {
            return true;
        }

        $allowed = $this->purchase_type ?: 'both';

        if ($allowed !== 'both' && $purchase !== $allowed) {
            return false;
        }

        $restricted = $this->applicable_packages;

        if (! is_array($restricted) || $restricted === [] || in_array('all', $restricted, true)) {
            return true;
        }

        if ($tiers === []) {
            return false;
        }

        foreach ($tiers as $tier) {
            if (! in_array($tier, $restricted, true)) {
                return false;
            }
        }

        return true;
    }
}
