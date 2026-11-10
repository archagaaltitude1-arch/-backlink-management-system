<?php

namespace App\Http\Controllers;

use App\Models\CreditPackage;
use App\Models\CreditReservation;
use App\Models\CreditTransaction;
use App\Models\CustomerProject;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Wallet;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

            return redirect()->route('orders.show', $order);
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => ['order_number' => $order->order_number, 'amount' => $order->total_amount, 'gateway' => 'demo']]);
        }

        return view('orders.payment', compact('order'));
    }

    public function payOrder(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 404);
        $data = $request->validate([
            'payment_method' => ['required', 'in:demo_card'],
            'demo_result' => ['required', 'in:success,declined'],
        ]);

        $payment = DB::transaction(function () use ($request, $order, $data): Payment {
            $lockedOrder = $request->user()->orders()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->payment_status === 'paid') {
                return $lockedOrder->payments()->latest()->firstOrFail();
            }

            $isSuccessful = $data['demo_result'] === 'success';
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

            $lockedOrder->update([
                'payment_status' => $isSuccessful ? 'paid' : 'failed',
                'order_status' => $isSuccessful ? 'processing' : 'pending',
            ]);

            if ($isSuccessful) {
                Invoice::firstOrCreate(['order_id' => $lockedOrder->id], [
                    'customer_id' => $request->user()->id,
                    'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                    'total_amount' => $lockedOrder->total_amount,
                    'issued_at' => now(),
                ]);

                CustomerProject::firstOrCreate(['order_id' => $lockedOrder->id], [
                    'customer_id' => $request->user()->id,
                    'name' => 'Backlink project '.$lockedOrder->order_number,
                    'status' => 'processing',
                    'target_url' => $lockedOrder->items->first()?->target_url,
                ]);
            } elseif ($lockedOrder->coupon_code) {
                $request->user()->couponUsages()->where('order_id', $lockedOrder->id)->delete();
            }

            return $payment;
        });

        if ($payment->wasRecentlyCreated) {
            $request->user()->notify(new OrderPlacedNotification($order->fresh(['items']), $payment));
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => ['payment' => $payment, 'order' => $order->fresh()]], $payment->status === 'successful' ? 200 : 422);
        }

        return redirect()->route('orders.show', $order)
            ->with($payment->status === 'successful' ? 'success' : 'error', $payment->status === 'successful' ? 'Demo payment successful. Your order is processing.' : 'Demo payment declined. You can try again.');
    }

    public function history(Request $request): View|JsonResponse
    {
        $payments = $request->user()->payments()->with(['order', 'creditPackage'])->latest()->paginate(10);

        if ($request->expectsJson()) {
            return response()->json(['data' => $payments]);
        }

        return view('customer.payment-history', compact('payments'));
    }

    public function purchaseCredits(Request $request, CreditPackage $creditPackage): RedirectResponse|JsonResponse
    {
        abort_unless($creditPackage->is_active, 404);

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
        abort_unless(in_array($request->user()->role, ['admin', 'staff', 'seo'], true), 403);
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
}
