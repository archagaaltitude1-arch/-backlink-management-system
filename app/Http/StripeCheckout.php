<?php

namespace App\Http;

use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StripeCheckout
{
    public static function usesDemo(): bool
    {
        return app()->environment('local', 'testing') && config('services.stripe.demo');
    }

    public static function isConfigured(): bool
    {
        return filled(config('services.stripe.secret')) && filled(config('services.stripe.webhook_secret'));
    }

    public function start(User $customer, Order|CreditPackage $purchase): ?string
    {
        if (! self::isConfigured()) {
            throw ValidationException::withMessages(['payment' => 'Stripe checkout is currently unavailable.']);
        }

        $payment = DB::transaction(function () use ($customer, $purchase): ?Payment {
            User::query()->lockForUpdate()->findOrFail($customer->id);
            $order = $purchase instanceof Order ? Order::query()->lockForUpdate()->findOrFail($purchase->id) : null;
            if ($order?->payment_status === 'paid') {
                return null;
            }

            $target = [
                'order_id' => $order?->id,
                'credit_package_id' => $purchase instanceof CreditPackage ? $purchase->id : null,
            ];
            $pending = $customer->payments()->where($target)->where('gateway', 'stripe')->where('status', 'pending')->first();
            if ($pending) {
                return $pending;
            }

            $order?->reservePaymentCoupon($customer);
            $returnUrl = $order ? route('orders.show', $order) : route('customer.wallet');

            return $customer->payments()->create($target + [
                'transaction_id' => 'STRIPE-'.Str::uuid(),
                'amount' => $order ? $order->total_amount : $purchase->price,
                'payment_method' => 'Stripe Checkout',
                'gateway' => 'stripe',
                'status' => 'pending',
                'metadata' => [
                    'name' => $order ? 'Order '.$order->order_number : $purchase->name,
                    'credits' => $purchase instanceof CreditPackage ? $purchase->credits : null,
                    'success_url' => $returnUrl.'?stripe=returned',
                    'cancel_url' => $order ? route('orders.payment', $order) : $returnUrl,
                ],
            ]);
        });

        if ($payment === null) {
            return null;
        }

        if ($payment->created_at->lte(now()->subHours(23))) {
            throw ValidationException::withMessages(['payment' => 'Your previous checkout is still being reconciled. Please try again later.']);
        }

        try {
            $response = Http::withToken(config('services.stripe.secret'))
                ->withHeaders(['Idempotency-Key' => $payment->transaction_id])
                ->connectTimeout(3)->timeout(12)->asForm()
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'mode' => 'payment',
                    'payment_method_types' => ['card'],
                    'client_reference_id' => $payment->transaction_id,
                    'success_url' => $payment->metadata['success_url'],
                    'cancel_url' => $payment->metadata['cancel_url'],
                    'line_items' => [[
                        'quantity' => 1,
                        'price_data' => [
                            'currency' => 'usd',
                            'unit_amount' => $this->amountInCents($payment),
                            'product_data' => ['name' => $payment->metadata['name']],
                        ],
                    ]],
                ]);
            $url = $response->json('url');
            if ($response->successful() && is_string($url) && str_starts_with($url, 'https://checkout.stripe.com/')) {
                return $url;
            }
        } catch (ConnectionException $exception) {
            report($exception);
        }

        throw ValidationException::withMessages(['payment' => 'Unable to open Stripe Checkout. Please try again.']);
    }

    /** @param array<string, mixed> $session */
    public function handleSession(string $event, array $session): void
    {
        $reference = $session['client_reference_id'] ?? null;
        if (! is_string($reference)) {
            return;
        }

        $confirmedPayment = DB::transaction(function () use ($event, $session, $reference): ?Payment {
            $payment = Payment::query()->where('gateway', 'stripe')->where('transaction_id', $reference)->lockForUpdate()->first();
            if (! $payment || $payment->status !== 'pending') {
                return null;
            }

            if ($event === 'checkout.session.expired') {
                $payment->update(['status' => 'failed']);
                if ($payment->order_id !== null) {
                    $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);
                    if ($order->payment_status !== 'paid') {
                        $order->update(['payment_status' => 'failed']);
                        $payment->customer->couponUsages()->where('order_id', $order->id)->delete();
                    }
                }

                return null;
            }

            $amount = $this->amountInCents($payment);
            $expectedStatus = $amount === 0 ? 'no_payment_required' : 'paid';
            if (($session['payment_status'] ?? null) !== $expectedStatus) {
                return null;
            }

            abort_unless(($session['mode'] ?? null) === 'payment'
                && ($session['status'] ?? null) === 'complete'
                && ($session['currency'] ?? null) === 'usd'
                && ($session['amount_total'] ?? null) === $amount
                && is_string($session['id'] ?? null), 422);

            $payment->update([
                'status' => 'successful',
                'paid_at' => now(),
                'metadata' => $payment->metadata + [
                    'stripe_session_id' => $session['id'],
                    'stripe_payment_intent' => $session['payment_intent'] ?? null,
                ],
            ]);

            if ($payment->order_id !== null) {
                $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);
                if ($order->payment_status !== 'paid') {
                    $order->confirmPayment();

                    return $payment;
                }
            } elseif ((int) ($payment->metadata['credits'] ?? 0) > 0) {
                $wallet = Wallet::firstOrCreate(['customer_id' => $payment->customer_id]);
                $credits = (int) $payment->metadata['credits'];
                $wallet->increment('available_balance', $credits);
                CreditTransaction::create([
                    'wallet_id' => $wallet->id,
                    'payment_id' => $payment->id,
                    'transaction_type' => 'purchase',
                    'amount' => $credits,
                    'description' => $payment->metadata['name'].' credit package purchased',
                ]);
            }

            return null;
        });

        if ($confirmedPayment) {
            rescue(fn () => $confirmedPayment->customer->notify(new OrderPlacedNotification(
                $confirmedPayment->order->load('rewardCoupon'), $confirmedPayment,
            )));
        }
    }

    private function amountInCents(Payment $payment): int
    {
        return (int) str_replace('.', '', $payment->amount);
    }
}
