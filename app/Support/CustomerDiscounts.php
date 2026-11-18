<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\DiscountCampaign;
use App\Models\FirstOrderRedemption;
use App\Models\Order;
use App\Models\User;
use App\Notifications\CouponNotice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerDiscounts
{
    /**
     * @param  array{tiers?: array<int, string>, purchase?: string}  $context
     * @return array{original: float, package_discount: float, subtotal: float, offer_type: ?string, offer_label: string, coupon_code: ?string, offer_discount: float, total: float}
     */
    public function quote(?User $customer, float $subtotal, float $packageDiscount, array $context = [], ?string $code = null): array
    {
        $subtotal = round(max(0, $subtotal), 2);
        $packageDiscount = round(max(0, $packageDiscount), 2);
        $quote = [
            'original' => round($subtotal + $packageDiscount, 2),
            'package_discount' => $packageDiscount,
            'subtotal' => $subtotal,
            'offer_type' => null,
            'offer_label' => '',
            'coupon_code' => null,
            'offer_discount' => 0.0,
            'total' => $subtotal,
        ];

        if ($customer === null || ! $this->tablesReady()) {
            return $quote;
        }

        $code = is_string($code) ? Str::upper(trim($code)) : '';

        if ($code !== '') {
            $coupon = Coupon::findByCode($code);

            if ($coupon !== null && $coupon->rejectionFor($customer, $subtotal, $context) === null) {
                return $this->withOffer($quote, $coupon->type ?: 'promotional', $coupon->discountSummary(), $coupon->code, $coupon->discountAmount($subtotal));
            }
        }

        $campaign = DiscountCampaign::findType('first_order');

        if ($campaign !== null && $this->firstOrderApplies($customer, $subtotal, $packageDiscount, $campaign)) {
            $amount = $this->moneyOff($campaign->discount_type, (float) $campaign->discount_value, $subtotal, null);

            return $this->withOffer($quote, 'first_order', 'First-order discount', null, $amount);
        }

        return $quote;
    }

    public function firstOrderOffer(User $customer): ?DiscountCampaign
    {
        $campaign = DiscountCampaign::findType('first_order');

        if ($campaign === null || ! $campaign->is_enabled) {
            return null;
        }

        if (FirstOrderRedemption::query()->where('customer_id', $customer->id)->exists()) {
            return null;
        }

        if ($this->successfulOrders($customer)->exists()) {
            return null;
        }

        return $campaign;
    }

    public function redeem(Order $order, User $customer): void
    {
        if ($order->offer_type === 'first_order') {
            $this->redeemFirstOrder($order, $customer);

            return;
        }

        $codes = $order->appliedCouponCodes();

        if ($codes === [] || $customer->couponUsages()->where('order_id', $order->id)->exists()) {
            return;
        }

        $subtotal = round((float) $order->total_amount + (float) $order->discount_amount, 2);
        $context = CouponCart::fromNamedItems($order->items);
        $coupons = [];
        $expected = 0.0;

        foreach ($codes as $code) {
            $found = Coupon::findByCode($code);
            $coupon = $found === null ? null : Coupon::query()->whereKey($found->id)->lockForUpdate()->first();
            $message = $coupon === null
                ? 'Invalid coupon code.'
                : $coupon->rejectionFor($customer, $subtotal, $context);

            if ($message !== null) {
                throw ValidationException::withMessages(['coupon_code' => $message]);
            }

            $coupons[] = $coupon;
            $expected = round($expected + $coupon->discountAmount($subtotal), 2);
        }

        $expected = min($subtotal, $expected);

        if (round((float) $order->discount_amount, 2) !== round($expected, 2)) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This discount is no longer available. Start a new checkout to continue.',
            ]);
        }

        foreach ($coupons as $coupon) {
            $customer->couponUsages()->create([
                'coupon_id' => $coupon->id,
                'order_id' => $order->id,
                'discount_amount' => $coupon->discountAmount($subtotal),
            ]);
        }
    }

    /**
     * @param  array{tiers?: array<int, string>, purchase?: string}  $context
     * @param  list<string>  $codes
     * @return array{codes: list<string>, offer_type: string, discount: float, total: float}
     */
    public function combinedOffer(User $customer, float $subtotal, array $context, array $codes): array
    {
        $subtotal = round(max(0, $subtotal), 2);
        $accepted = [];
        $discount = 0.0;
        $offerType = 'order_reward';

        foreach ($codes as $code) {
            $coupon = Coupon::findByCode($code);

            if ($coupon === null || $coupon->rejectionFor($customer, $subtotal, $context) !== null || in_array($coupon->code, $accepted, true)) {
                continue;
            }

            $accepted[] = $coupon->code;
            $discount = round($discount + $coupon->discountAmount($subtotal), 2);
            $offerType = $coupon->type ?: 'promotional';
        }

        $discount = min($subtotal, $discount);

        return [
            'codes' => $accepted,
            'offer_type' => $offerType,
            'discount' => $discount,
            'total' => round($subtotal - $discount, 2),
        ];
    }

    public function rewardAfterPayment(Order $order): void
    {
        $customer = $order->customer;

        if ($customer instanceof User) {
            $this->issueLoyalty($customer);
        }
    }

    public function issueOrderReward(Order $order, User $customer): void
    {
        if (! $this->tablesReady()) {
            return;
        }

        $campaign = DiscountCampaign::findType('order_reward');

        if ($campaign === null || ! $campaign->is_enabled) {
            return;
        }

        $exists = Coupon::query()->where('reward_order_id', $order->id)->lockForUpdate()->exists();

        if ($exists) {
            return;
        }

        try {
            $coupon = Coupon::query()->create([
                'code' => $this->uniqueCode('ORDER'),
                'type' => 'order_reward',
                'discount_type' => $campaign->discount_type,
                'discount_value' => $campaign->discount_value,
                'minimum_order_amount' => $campaign->minimum_order_amount,
                'applicable_packages' => ['all'],
                'purchase_type' => 'both',
                'usage_limit' => 1,
                'per_customer_limit' => 1,
                'expires_at' => now()->addDays(max(1, (int) ($campaign->expiry_days ?? 14))),
                'is_active' => true,
                'customer_id' => $customer->id,
                'reward_order_id' => $order->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            return;
        }

        $customer->notify(new CouponNotice(
            'A '.$coupon->discountSummary().' coupon is ready',
            'Use '.$coupon->code.' on a package or an individual backlink. Save it for later before '.$coupon->expires_at?->format('M j, Y').'.',
        ));
    }

    /**
     * @return Collection<int, Coupon>
     */
    public function orderRewards(User $customer): Collection
    {
        if (! $this->tablesReady()) {
            return collect();
        }

        return Coupon::query()
            ->where('customer_id', $customer->id)
            ->where('type', 'order_reward')
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereDoesntHave('usages')
            ->latest()
            ->get();
    }

    public function issueScheduled(): void
    {
        User::query()->where('account_type', 'customer')->orderBy('id')->each(function (User $customer): void {
            $this->issueLoyalty($customer);
            $this->issueComeback($customer);
        });

        $this->warnExpiring();
    }

    public function issueLoyalty(User $customer): void
    {
        $campaign = DiscountCampaign::findType('loyalty');
        $every = (int) ($campaign->qualifying_orders ?? 0);

        if ($campaign === null || ! $campaign->is_enabled || $every < 1) {
            return;
        }

        $count = $this->successfulOrders($customer)->count();
        $milestones = [];

        if ($campaign->recurring) {
            for ($milestone = $every; $milestone <= $count; $milestone += $every) {
                $milestones[] = $milestone;
            }
        } elseif ($count >= $every) {
            $milestones = [$every];
        }

        foreach ($milestones as $milestone) {
            $exists = Coupon::query()
                ->where('customer_id', $customer->id)
                ->where('type', 'loyalty')
                ->where('milestone', $milestone)
                ->exists();

            if ($exists) {
                continue;
            }

            $coupon = $this->issueCoupon($customer, $campaign, 'loyalty', 'LOYAL', $milestone);
            $customer->notify(new CouponNotice(
                'Loyalty reward earned',
                'Use '.$coupon->code.' for '.$coupon->discountSummary().'. It expires '.$coupon->expires_at?->format('M j, Y').'.',
            ));
        }
    }

    public function issueComeback(User $customer): void
    {
        $campaign = DiscountCampaign::findType('comeback');
        $days = (int) ($campaign->inactivity_days ?? 0);

        if ($campaign === null || ! $campaign->is_enabled || $days < 1) {
            return;
        }

        $latest = $this->successfulOrders($customer)->latest('updated_at')->first();

        if ($latest === null || $latest->updated_at === null || $latest->updated_at->gt(now()->subDays($days))) {
            return;
        }

        $active = Coupon::query()
            ->where('customer_id', $customer->id)
            ->where('type', 'comeback')
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereDoesntHave('usages')
            ->exists();

        if ($active) {
            return;
        }

        $coupon = $this->issueCoupon($customer, $campaign, 'comeback', 'BACK', null);
        $customer->notify(new CouponNotice(
            'A comeback coupon is ready',
            'Use '.$coupon->code.' for '.$coupon->discountSummary().'. It expires '.$coupon->expires_at?->format('M j, Y').'.',
        ));
    }

    /**
     * @param  array{original: float, package_discount: float, subtotal: float, offer_type: ?string, offer_label: string, coupon_code: ?string, offer_discount: float, total: float}  $quote
     * @return array{original: float, package_discount: float, subtotal: float, offer_type: ?string, offer_label: string, coupon_code: ?string, offer_discount: float, total: float}
     */
    private function withOffer(array $quote, string $type, string $label, ?string $code, float $amount): array
    {
        $amount = round(min($quote['subtotal'], max(0, $amount)), 2);
        $quote['offer_type'] = $type;
        $quote['offer_label'] = $label;
        $quote['coupon_code'] = $code;
        $quote['offer_discount'] = $amount;
        $quote['total'] = round($quote['subtotal'] - $amount, 2);

        return $quote;
    }

    private function firstOrderApplies(User $customer, float $subtotal, float $packageDiscount, DiscountCampaign $campaign): bool
    {
        if (! $campaign->is_enabled || $subtotal < (float) $campaign->minimum_order_amount) {
            return false;
        }

        if (! $campaign->combines_with_package_discount && $packageDiscount > 0) {
            return false;
        }

        if (FirstOrderRedemption::query()->where('customer_id', $customer->id)->exists()) {
            return false;
        }

        return ! $this->successfulOrders($customer)->exists();
    }

    public function shopMoreMessage(User $customer): string
    {
        $fallback = 'Shop more packages and check Coupons for your next discount.';

        if (! $this->tablesReady()) {
            return $fallback;
        }

        $campaign = DiscountCampaign::findType('loyalty');
        $every = (int) ($campaign->qualifying_orders ?? 0);

        if ($campaign === null || ! $campaign->is_enabled || $every < 1) {
            return $fallback;
        }

        $count = $this->successfulOrders($customer)->count();
        $next = $campaign->recurring
            ? (intdiv($count, $every) + 1) * $every
            : ($count < $every ? $every : null);

        if ($next === null) {
            return $fallback;
        }

        $remaining = $next - $count;
        $orders = $remaining === 1 ? 'order' : 'orders';
        $progress = 'Shop '.$remaining.' more '.$orders.' to earn '.$campaign->summary().'.';

        if ($campaign->recurring && $count > 0 && $count % $every === 0) {
            return 'Your loyalty reward is in Coupons. '.$progress;
        }

        return $progress;
    }

    /**
     * @return Builder<Order>
     */
    public function successfulOrders(User $customer): Builder
    {
        return Order::query()
            ->where('customer_id', $customer->id)
            ->where('payment_status', 'paid')
            ->whereNotIn('order_status', ['cancelled', 'refunded', 'failed']);
    }

    private function redeemFirstOrder(Order $order, User $customer): void
    {
        if (FirstOrderRedemption::query()->where('customer_id', $customer->id)->lockForUpdate()->exists()) {
            throw ValidationException::withMessages([
                'coupon_code' => 'The first-order discount has already been used.',
            ]);
        }

        $campaign = DiscountCampaign::findType('first_order');
        $subtotal = round((float) $order->total_amount + (float) $order->discount_amount, 2);

        if ($campaign === null || ! $this->firstOrderApplies($customer, $subtotal, (float) $order->package_discount_amount, $campaign)) {
            throw ValidationException::withMessages([
                'coupon_code' => 'The first-order discount is no longer available.',
            ]);
        }

        $expected = $this->moneyOff($campaign->discount_type, (float) $campaign->discount_value, $subtotal, null);

        if (round((float) $order->discount_amount, 2) !== round($expected, 2)) {
            throw ValidationException::withMessages([
                'coupon_code' => 'The first-order discount is no longer available.',
            ]);
        }

        try {
            FirstOrderRedemption::query()->create([
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'discount_amount' => $order->discount_amount,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'coupon_code' => 'The first-order discount has already been used.',
            ]);
        }
    }

    private function issueCoupon(User $customer, DiscountCampaign $campaign, string $type, string $prefix, ?int $milestone): Coupon
    {
        return DB::transaction(function () use ($customer, $campaign, $type, $prefix, $milestone): Coupon {
            return Coupon::query()->create([
                'code' => $this->uniqueCode($prefix),
                'type' => $type,
                'discount_type' => $campaign->discount_type,
                'discount_value' => $campaign->discount_value,
                'minimum_order_amount' => $campaign->minimum_order_amount,
                'usage_limit' => 1,
                'per_customer_limit' => 1,
                'expires_at' => now()->addDays(max(1, (int) ($campaign->expiry_days ?? 30))),
                'is_active' => true,
                'customer_id' => $customer->id,
                'milestone' => $milestone,
                'purchase_type' => 'both',
            ]);
        });
    }

    private function warnExpiring(): void
    {
        Coupon::query()
            ->where('is_active', true)
            ->whereNotNull('customer_id')
            ->whereNull('expiry_notified_at')
            ->whereBetween('expires_at', [now(), now()->addDays(3)])
            ->whereDoesntHave('usages')
            ->with('customer')
            ->each(function (Coupon $coupon): void {
                $customer = User::query()->find($coupon->customer_id);

                if ($customer === null) {
                    return;
                }

                $customer->notify(new CouponNotice(
                    'A coupon is about to expire',
                    $coupon->code.' ('.$coupon->discountSummary().') expires '.$coupon->expires_at?->format('M j, Y').'.',
                ));
                $coupon->update(['expiry_notified_at' => now()]);
            });
    }

    private function moneyOff(string $type, float $value, float $subtotal, ?float $cap): float
    {
        $raw = $type === 'percentage' ? $subtotal * ($value / 100) : $value;

        if ($cap !== null) {
            $raw = min($raw, $cap);
        }

        return max(0, min($subtotal, round($raw, 2)));
    }

    private function uniqueCode(string $prefix): string
    {
        do {
            $code = $prefix.'-'.Str::upper(Str::random(8));
        } while (Coupon::query()->whereRaw('LOWER(code) = ?', [Str::lower($code)])->exists());

        return $code;
    }

    private function tablesReady(): bool
    {
        return Schema::hasTable('discount_campaigns');
    }
}
