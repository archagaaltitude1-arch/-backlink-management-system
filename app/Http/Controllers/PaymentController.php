<?php

namespace App\Http\Controllers;

use App\Http\StripeCheckout;
use App\Models\Coupon;
use App\Models\CreditPackage;
use App\Models\CreditReservation;
use App\Models\CreditTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Wallet;
use App\Notifications\OrderPlacedNotification;
use App\Support\CouponCart;
use App\Support\CustomerDiscounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function showOrderPayment(Request $request, Order $order): View|RedirectResponse|JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        if ($order->payment_status === 'paid') {
            if ($request->expectsJson()) {
                return response()->json(['data' => $order->load('items', 'payments')]);
            }

            return $this->afterPayment($order);
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => ['order_number' => $order->order_number, 'amount' => $order->total_amount, 'gateway' => StripeCheckout::usesDemo() ? 'demo' : 'stripe']]);
        }

        $discounts = app(CustomerDiscounts::class);
        $order->loadMissing('items');
        $context = CouponCart::fromNamedItems($order->items);

        if ($discounts->awaitingFirstOrder($request->user())) {
            $quote = $discounts->quote(
                $request->user(),
                $this->orderSubtotal($order),
                (float) $order->package_discount_amount,
                $context,
            );
            $order->update([
                'coupon_code' => $quote['coupon_code'],
                'applied_coupon_codes' => null,
                'offer_type' => $quote['offer_type'],
                'discount_amount' => $quote['offer_discount'],
                'total_amount' => $quote['total'],
            ]);

            return view('orders.payment', [
                'order' => $order->fresh(),
                'couponOffer' => null,
                'appliedCoupons' => collect(),
                'availableCoupons' => collect(),
            ]);
        }

        $order = $order->fresh();
        $subtotal = $this->orderSubtotal($order);
        $rewards = $discounts->orderRewards($request->user())
            ->filter(fn (Coupon $coupon): bool => $coupon->rejectionFor($request->user(), $subtotal, $context) === null)
            ->keyBy(fn (Coupon $coupon): string => $coupon->code);
        $appliedCoupons = collect($order->appliedCouponCodes())
            ->map(fn (string $code): ?Coupon => $rewards->get($code))
            ->filter()
            ->values();
        $appliedCodes = $appliedCoupons->map(fn (Coupon $coupon): string => $coupon->code)->all();
        $availableCoupons = $rewards
            ->reject(fn (Coupon $coupon): bool => in_array($coupon->code, $appliedCodes, true))
            ->values();

        return view('orders.payment', [
            'order' => $order,
            'couponOffer' => null,
            'appliedCoupons' => $appliedCoupons,
            'availableCoupons' => $availableCoupons,
        ]);
    }

    public function applyCoupon(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        if ($blocked = $this->couponChangeBlocked($order)) {
            return back()->withErrors(['coupon_code' => $blocked]);
        }

        if (app(CustomerDiscounts::class)->awaitingFirstOrder($request->user())) {
            return back()->withErrors(['coupon_code' => 'Your first order uses the 10% new-customer offer.']);
        }

        $data = $request->validate(['coupon_code' => ['required', 'string', 'max:40']]);
        $coupon = Coupon::findByCode($data['coupon_code']);
        $subtotal = $this->orderSubtotal($order);
        $message = $coupon === null
            ? 'Invalid coupon code.'
            : $coupon->rejectionFor($request->user(), $subtotal, CouponCart::fromNamedItems($order->items));

        if ($message !== null) {
            return back()->withErrors(['coupon_code' => $message]);
        }

        $previousTotal = (float) $order->total_amount;
        $alreadyApplied = in_array($coupon->code, $order->appliedCouponCodes(), true);
        $offer = app(CustomerDiscounts::class)->combinedOffer(
            $request->user(),
            $subtotal,
            CouponCart::fromNamedItems($order->items),
            [...$order->appliedCouponCodes(), $coupon->code],
        );

        if (! in_array($coupon->code, $offer['codes'], true) || $offer['discount'] <= 0) {
            return back()->withErrors(['coupon_code' => 'This coupon does not reduce the amount due.']);
        }

        $order->update([
            'coupon_code' => $coupon->code,
            'applied_coupon_codes' => $offer['codes'],
            'offer_type' => $offer['offer_type'],
            'discount_amount' => $offer['discount'],
            'total_amount' => $offer['total'],
        ]);
        session(['payment_coupon_choice.'.$order->id => $coupon->code]);

        $amountDue = number_format($offer['total'], 2);
        $message = $alreadyApplied || round($previousTotal, 2) === round($offer['total'], 2)
            ? 'Coupon '.$coupon->code.' is already on this order. Amount due is $'.$amountDue.'.'
            : 'Coupon '.$coupon->code.' applied. Amount due changed from $'.number_format($previousTotal, 2).' to $'.$amountDue.'.';

        return back()->with('success', $message);
    }

    public function removeCoupon(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);

        if ($blocked = $this->couponChangeBlocked($order)) {
            return back()->withErrors(['coupon_code' => $blocked]);
        }

        $remove = Str::upper(trim((string) $request->input('coupon_code', '')));
        $remaining = $remove === ''
            ? []
            : array_values(array_filter(
                $order->appliedCouponCodes(),
                fn (string $code): bool => $code !== $remove,
            ));
        $context = CouponCart::fromNamedItems($order->items);
        $subtotal = $this->orderSubtotal($order);

        if ($remaining === []) {
            $quote = app(CustomerDiscounts::class)->quote(
                $request->user(),
                $subtotal,
                (float) $order->package_discount_amount,
                $context,
                null,
            );
            $order->update([
                'coupon_code' => $quote['coupon_code'],
                'applied_coupon_codes' => null,
                'offer_type' => $quote['offer_type'],
                'discount_amount' => $quote['offer_discount'],
                'total_amount' => $quote['total'],
            ]);
        } else {
            $offer = app(CustomerDiscounts::class)->combinedOffer($request->user(), $subtotal, $context, $remaining);
            $order->update([
                'coupon_code' => $offer['codes'][0] ?? null,
                'applied_coupon_codes' => $offer['codes'],
                'offer_type' => $offer['offer_type'],
                'discount_amount' => $offer['discount'],
                'total_amount' => $offer['total'],
            ]);
        }

        session()->forget('payment_coupon_choice.'.$order->id);
        $skipped = session('skipped_order_coupons', []);
        $skipped[] = $order->id;
        session(['skipped_order_coupons' => array_values(array_unique($skipped))]);

        return back()->with('success', 'Coupon removed. Amount due is $'.number_format((float) $order->fresh()->total_amount, 2).'.');
    }

    public function payOrder(Request $request, Order $order, StripeCheckout $checkout): RedirectResponse|JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);
        if (! StripeCheckout::usesDemo() || $request->input('payment_method') === 'stripe') {
            $url = $checkout->start($request->user(), $order);

            return $this->stripeRedirect($request, $url, route('orders.thanks', $order));
        }

        $data = $request->validate([
            'payment_method' => ['required', 'in:demo_card'],
            'demo_result' => ['required', 'in:success,declined'],
        ]);

        $payment = DB::transaction(function () use ($request, $order, $data): Payment {
            $lockedOrder = $request->user()->orders()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->payment_status === 'paid') {
                return $lockedOrder->payments()->latest()->firstOrFail();
            }

            if ($lockedOrder->payments()->where('gateway', 'stripe')->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages(['payment' => 'A Stripe checkout is pending for this order. Complete it or wait for it to expire.']);
            }

            $isSuccessful = $data['demo_result'] === 'success';
            if ($isSuccessful) {
                $lockedOrder->reservePaymentCoupon($request->user());
            }

            $payment = Payment::create([
                'customer_id' => $request->user()->id,
                'order_id' => $lockedOrder->id,
                'transaction_id' => 'DEMO-'.Str::upper(Str::random(12)),
                'amount' => $lockedOrder->total_amount,
                'payment_method' => 'Demo card',
                'gateway' => 'demo',
                'status' => $isSuccessful ? 'successful' : 'failed',
                'metadata' => ['mode' => 'simulation'],
                'paid_at' => $isSuccessful ? now() : null,
            ]);

            if ($isSuccessful) {
                $lockedOrder->confirmPayment();
            } else {
                $lockedOrder->update(['payment_status' => 'failed', 'order_status' => 'pending']);
                if ($lockedOrder->coupon_code) {
                    $request->user()->couponUsages()->where('order_id', $lockedOrder->id)->delete();
                }
            }

            return $payment;
        });

        if ($payment->wasRecentlyCreated) {
            rescue(fn () => $request->user()->notify(new OrderPlacedNotification($order->fresh(['rewardCoupon']), $payment)));
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => ['payment' => $payment, 'order' => $order->fresh()]], $payment->status === 'successful' ? 200 : 422);
        }

        if ($payment->status !== 'successful') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Demo payment declined. You can try again.');
        }

        return $this->afterPayment($order);
    }

    private function afterPayment(Order $order): RedirectResponse
    {
        return redirect()->route('orders.thanks', $order);
    }

    public function history(Request $request): View|JsonResponse
    {
        $payments = $request->user()->payments()->with(['order', 'creditPackage'])->latest()->paginate(10);

        if ($request->expectsJson()) {
            return response()->json(['data' => $payments]);
        }

        return view('customer.payment-history', compact('payments'));
    }

    public function purchaseCredits(Request $request, CreditPackage $creditPackage, StripeCheckout $checkout): RedirectResponse|JsonResponse
    {
        abort_unless($creditPackage->is_active, 404);

        if (! StripeCheckout::usesDemo() || $request->input('payment_method') === 'stripe') {
            $url = $checkout->start($request->user(), $creditPackage);

            return $this->stripeRedirect($request, $url, route('customer.wallet'));
        }

        $payment = DB::transaction(function () use ($request, $creditPackage): Payment {
            $payment = Payment::create([
                'customer_id' => $request->user()->id,
                'credit_package_id' => $creditPackage->id,
                'transaction_id' => 'DEMO-'.Str::upper(Str::random(12)),
                'amount' => $creditPackage->price,
                'payment_method' => 'Demo card',
                'gateway' => 'demo',
                'status' => 'successful',
                'metadata' => ['mode' => 'simulation'],
                'paid_at' => now(),
            ]);
            $wallet = Wallet::firstOrCreate(['customer_id' => $request->user()->id]);
            $wallet->increment('available_balance', $creditPackage->credits);
            CreditTransaction::create([
                'wallet_id' => $wallet->id,
                'payment_id' => $payment->id,
                'transaction_type' => 'purchase',
                'amount' => $creditPackage->credits,
                'description' => $creditPackage->name.' credit package purchased',
            ]);

            return $payment;
        });

        if ($request->expectsJson()) {
            return response()->json(['data' => ['payment' => $payment, 'wallet' => $request->user()->wallet()->first()]], 201);
        }

        return redirect()->route('customer.wallet')->with('success', $creditPackage->credits.' credits added to your wallet.');
    }

    private function stripeRedirect(Request $request, ?string $url, string $fallback): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['data' => ['checkout_url' => $url, 'return_url' => $fallback]]);
        }

        return $url === null ? redirect()->to($fallback) : redirect()->away($url);
    }

    public function reserveCredits(Request $request): JsonResponse
    {
        $data = $request->validate(['order_item_id' => ['required', 'integer']]);
        $orderItem = OrderItem::query()
            ->whereKey($data['order_item_id'])
            ->whereHas('order', fn ($query) => $query
                ->where('customer_id', $request->user()->id)
                ->where('payment_status', 'paid'))
            ->firstOrFail();

        $reservation = DB::transaction(function () use ($request, $orderItem): ?CreditReservation {
            $wallet = Wallet::firstOrCreate(['customer_id' => $request->user()->id]);
            $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            $existing = CreditReservation::query()
                ->where('wallet_id', $wallet->id)
                ->where('order_item_id', $orderItem->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing->status === 'reserved' ? $existing : null;
            }

            $credits = $orderItem->credit_cost;
            if ($wallet->available_balance < $credits) {
                return null;
            }

            $wallet->decrement('available_balance', $credits);
            $wallet->increment('reserved_balance', $credits);
            $reservation = CreditReservation::create([
                'wallet_id' => $wallet->id,
                'order_item_id' => $orderItem->id,
                'reserved_credits' => $credits,
            ]);
            CreditTransaction::create([
                'wallet_id' => $wallet->id,
                'order_item_id' => $orderItem->id,
                'transaction_type' => 'reserve',
                'amount' => $credits,
                'description' => 'Credits reserved for '.$orderItem->item_name,
            ]);

            return $reservation;
        });

        if (! $reservation) {
            return response()->json(['message' => 'Not enough available credits or this item has already been settled.'], 422);
        }

        return response()->json(['data' => $reservation], 201);
    }

    public function settleCredits(Request $request, OrderItem $orderItem): JsonResponse
    {
        abort_unless($request->user()->status === 'active' && in_array($request->user()->role?->role_name, ['Admin', 'Staff', 'SEO'], true), 403);
        $data = $request->validate(['published' => ['required', 'boolean']]);
        $order = Order::query()->whereKey($orderItem->order_id)->where('payment_status', 'paid')->firstOrFail();

        $result = DB::transaction(function () use ($order, $orderItem, $data): array {
            $wallet = Wallet::query()->where('customer_id', $order->customer_id)->lockForUpdate()->firstOrFail();
            $reservation = CreditReservation::query()
                ->where('wallet_id', $wallet->id)
                ->where('order_item_id', $orderItem->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($reservation->status !== 'reserved') {
                return ['reservation' => $reservation, 'wallet' => $wallet, 'changed' => false];
            }

            $credits = $reservation->reserved_credits;
            $wallet->decrement('reserved_balance', $credits);
            $reservation->update([
                'status' => $data['published'] ? 'captured' : 'refunded',
                'resolved_at' => now(),
            ]);

            if (! $data['published']) {
                $wallet->increment('available_balance', $credits);
            }

            CreditTransaction::create([
                'wallet_id' => $wallet->id,
                'order_item_id' => $orderItem->id,
                'transaction_type' => $data['published'] ? 'deduction' : 'refund',
                'amount' => $credits,
                'description' => $data['published']
                    ? 'Credits charged after backlink verification'
                    : 'Reserved credits released after unsuccessful publication',
            ]);

            return ['reservation' => $reservation->fresh(), 'wallet' => $wallet->fresh(), 'changed' => true];
        });

        return response()->json(['data' => $result]);
    }

    private function couponChangeBlocked(Order $order): ?string
    {
        if ($order->payment_status === 'paid') {
            return 'This order is already paid.';
        }

        if ($order->payments()->where('gateway', 'stripe')->where('status', 'pending')->exists()) {
            return 'A Stripe checkout is pending for this order. Complete it or wait for it to expire.';
        }

        return null;
    }

    private function orderSubtotal(Order $order): float
    {
        return round((float) $order->total_amount + (float) $order->discount_amount, 2);
    }
}
