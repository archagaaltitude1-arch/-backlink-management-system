<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponReservation
{
    /**
     * Reserve one eligible coupon for this customer and this order.
     *
     * A single-use code is locked to that customer. Each unpaid order can show its own code.
     *
     * @param  array{tiers?: array<int, string>, purchase?: string}  $context
     */
    public function offer(User $customer, float $subtotal, array $context): ?Coupon
    {
        if (! in_array($context['purchase'] ?? '', ['package', 'individual', 'both'], true)) {
            return null;
        }

        return DB::transaction(function () use ($customer, $subtotal, $context): ?Coupon {
            $reserved = $this->available()
                ->where(function (Builder $query) use ($customer): void {
                    $query->where('customer_id', $customer->id)
                        ->orWhereHas('emailLogs', fn (Builder $logs) => $logs->where('customer_id', $customer->id));
                })
                ->lockForUpdate()
                ->get();

            foreach ($reserved as $coupon) {
                if ($coupon->rejectionFor($customer, $subtotal, $context) === null) {
                    return $coupon;
                }
            }

            $candidates = $this->available()
                ->where(function (Builder $query) use ($customer): void {
                    $query->whereNull('customer_id')->orWhere('customer_id', $customer->id);
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($candidates as $candidate) {
                $coupon = Coupon::query()->whereKey($candidate->id)->lockForUpdate()->first();

                if ($coupon === null || $coupon->rejectionFor($customer, $subtotal, $context) !== null) {
                    continue;
                }

                $sentTo = $coupon->emailLogs()->pluck('customer_id')->map(fn ($id): int => (int) $id)->unique();

                if ($sentTo->contains($customer->id)) {
                    continue;
                }

                $singleUse = (int) $coupon->usage_limit === 1;

                if ($singleUse && ($coupon->customer_id !== null || $sentTo->isNotEmpty() || $coupon->usages()->exists())) {
                    continue;
                }

                if ($coupon->usage_limit !== null && $sentTo->count() >= (int) $coupon->usage_limit) {
                    continue;
                }

                if ($singleUse) {
                    $claimed = Coupon::query()
                        ->whereKey($coupon->id)
                        ->whereNull('customer_id')
                        ->update(['customer_id' => $customer->id]);

                    if ($claimed !== 1) {
                        continue;
                    }

                    $coupon->customer_id = $customer->id;
                }

                $coupon->deliverTo($customer);

                return $coupon;
            }

            return null;
        });
    }

    /**
     * Coupon to show in the payment popup. It is saved on the order only after Apply.
     */
    public function offerForOrder(User $customer, Order $order): ?Coupon
    {
        if ($order->payment_status === 'paid') {
            return null;
        }

        $order->loadMissing('items');
        $subtotal = round((float) $order->total_amount + (float) $order->discount_amount, 2);
        $context = CouponCart::fromNamedItems($order->items);
        $existing = filled($order->coupon_code) ? Coupon::findByCode((string) $order->coupon_code) : null;

        if ($existing !== null && $existing->rejectionFor($customer, $subtotal, $context) === null && ! $this->needsOwnCode($existing, $order)) {
            return null;
        }

        $pendingCode = session('pending_order_coupons.'.$order->id);
        if (is_string($pendingCode) && $pendingCode !== '') {
            $pending = Coupon::findByCode($pendingCode);

            if ($pending !== null && $pending->rejectionFor($customer, $subtotal, $context) === null) {
                return $pending;
            }
        }

        $coupon = $this->selectForOrder($customer, $order, $subtotal, $context);

        if ($coupon === null) {
            return null;
        }

        session(['pending_order_coupons.'.$order->id => $coupon->code]);

        return $coupon;
    }

    /**
     * Put an eligible coupon on an unpaid order. Redemption still waits for successful payment.
     */
    public function applyToOrder(User $customer, Order $order): ?Coupon
    {
        if ($order->payment_status === 'paid') {
            return null;
        }

        $order->loadMissing('items');
        $subtotal = round((float) $order->total_amount + (float) $order->discount_amount, 2);
        $context = CouponCart::fromNamedItems($order->items);
        $coupon = $this->selectForOrder($customer, $order, $subtotal, $context);

        if ($coupon === null) {
            return null;
        }

        return $this->storeCoupon($order, $coupon, $subtotal);
    }

    /**
     * @param  array{tiers?: array<int, string>, purchase?: string}  $context
     */
    private function selectForOrder(User $customer, Order $order, float $subtotal, array $context): ?Coupon
    {
        $existing = filled($order->coupon_code) ? Coupon::findByCode((string) $order->coupon_code) : null;

        if ($existing !== null && $existing->rejectionFor($customer, $subtotal, $context) === null && ! $this->needsOwnCode($existing, $order)) {
            return $existing;
        }

        $template = $this->templateFor($customer, $subtotal, $context) ?? $this->offer($customer, $subtotal, $context);

        if ($template !== null && $template->rejectionFor($customer, $subtotal, $context) === null && ! $this->needsOwnCode($template, $order)) {
            return $template;
        }

        if ($template === null || ($template->usage_limit !== null && (int) $template->usage_limit <= 1)) {
            return $existing !== null && $existing->rejectionFor($customer, $subtotal, $context) === null ? $existing : null;
        }

        return $this->issueForOrder($customer, $template);
    }

    private function storeCoupon(Order $order, Coupon $coupon, float $subtotal): Coupon
    {
        $discount = $coupon->discountAmount($subtotal);
        $order->update([
            'coupon_code' => $coupon->code,
            'discount_amount' => $discount,
            'total_amount' => max(0, round($subtotal - $discount, 2)),
        ]);

        return $coupon;
    }

    /**
     * @param  array{tiers?: array<int, string>, purchase?: string}  $context
     */
    private function templateFor(User $customer, float $subtotal, array $context): ?Coupon
    {
        $coupons = $this->available()
            ->where(function (Builder $query) use ($customer): void {
                $query->whereNull('customer_id')->orWhere('customer_id', $customer->id);
            })
            ->where(function (Builder $query): void {
                $query->whereNull('usage_limit')->orWhere('usage_limit', '>', 1);
            })
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($coupons as $coupon) {
            if ($coupon->usage_limit !== null && $coupon->usages()->count() >= (int) $coupon->usage_limit) {
                continue;
            }

            if ($coupon->fitsCart($subtotal, $context)) {
                return $coupon;
            }
        }

        return null;
    }

    private function needsOwnCode(Coupon $coupon, Order $order): bool
    {
        if ($coupon->usage_limit !== null && (int) $coupon->usage_limit <= 1) {
            return false;
        }

        $perCustomer = max(1, (int) $coupon->per_customer_limit);

        if ($perCustomer > 1) {
            return false;
        }

        $alreadyUsed = $coupon->usages()->where('customer_id', $order->customer_id)->count() >= $perCustomer;
        $heldByAnotherOrder = Order::query()
            ->where('coupon_code', $coupon->code)
            ->where('customer_id', $order->customer_id)
            ->whereKeyNot($order->id)
            ->where('payment_status', '!=', 'paid')
            ->exists();

        return $alreadyUsed || $heldByAnotherOrder;
    }

    private function issueForOrder(User $customer, Coupon $template): Coupon
    {
        return Coupon::query()->create([
            'code' => $this->orderCode(),
            'customer_id' => $customer->id,
            'discount_type' => $template->discount_type,
            'discount_value' => $template->discount_value,
            'maximum_discount_amount' => $template->maximum_discount_amount,
            'minimum_order_amount' => 0,
            'applicable_packages' => null,
            'purchase_type' => 'both',
            'usage_limit' => 1,
            'per_customer_limit' => 1,
            'expires_at' => $template->expires_at,
            'is_active' => true,
        ]);
    }

    private function orderCode(): string
    {
        do {
            $code = 'ORD-'.Str::upper(Str::random(8));
        } while (Coupon::query()->whereRaw('LOWER(code) = ?', [Str::lower($code)])->exists());

        return $code;
    }

    /**
     * @return Builder<Coupon>
     */
    private function available(): Builder
    {
        return Coupon::query()
            ->where('is_active', true)
            ->whereNull('reward_order_id')
            ->where('code', 'not like', 'ORD-%')
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
