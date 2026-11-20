@extends('layouts.customer')

@section('title', 'Payment')

@section('content')
    @php
        $demo = \App\Http\StripeCheckout::usesDemo();
        $stripe = \App\Http\StripeCheckout::isConfigured();
    @endphp
    <div class="mx-auto max-w-3xl space-y-6">
        <h1 class="text-3xl font-semibold">Complete payment</h1>
        @if (session('success'))
            <p class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <p class="rounded-xl bg-red-50 p-4 text-sm text-red-800" role="alert">{{ $errors->first() }}</p>
        @endif
        <section class="panel space-y-5 p-6">
            @php
                $amountDue = (float) $order->total_amount;
                $savings = (float) $order->discount_amount;
                $originalAmount = round($amountDue + $savings, 2);
            @endphp
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-sm text-navy/55">Order</p>
                    <p class="font-semibold">{{ $order->order_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-navy/55">Amount due</p>
                    @if ($savings > 0)
                        <p class="text-sm text-navy/40 line-through">${{ number_format($originalAmount, 2) }}</p>
                        <p class="text-2xl font-semibold text-brand-dark">${{ number_format($amountDue, 2) }}</p>
                        <p class="text-sm font-medium text-emerald-700">You save ${{ number_format($savings, 2) }}</p>
                    @else
                        <p class="text-2xl font-semibold">${{ number_format($amountDue, 2) }}</p>
                    @endif
                </div>
            </div>
            @if (($appliedCoupons ?? collect())->isNotEmpty())
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60">
                    <div class="border-b border-emerald-200 px-4 py-3">
                        <h2 class="font-semibold">On this order</h2>
                        <p class="mt-1 text-sm text-navy/60">Each coupon here is included in the amount due.</p>
                    </div>
                    <div class="divide-y divide-emerald-200">
                        @foreach ($appliedCoupons as $reward)
                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="font-semibold">Coupon {{ $reward->code }} is applied</p>
                                    <p class="mt-1 text-sm text-emerald-800">Saves ${{ number_format($reward->discountAmount($originalAmount), 2) }} · {{ $reward->discountSummary() }}</p>
                                </div>
                                <form method="POST" action="{{ route('orders.payment.coupon.destroy', $order) }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="coupon_code" value="{{ $reward->code }}">
                                    <button class="text-sm font-medium text-navy/70 hover:text-brand" type="submit">Remove coupon</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if (($availableCoupons ?? collect())->isNotEmpty())
                <div class="rounded-xl border border-navy/10">
                    <div class="border-b border-navy/10 px-4 py-3">
                        <h2 class="font-semibold">Available coupons</h2>
                        <p class="mt-1 text-sm text-navy/60">Use a coupon to move it onto this order. The amount due changes by that coupon.</p>
                    </div>
                    <div class="divide-y divide-navy/10">
                        @foreach ($availableCoupons as $reward)
                            @php
                                $couponSavings = $reward->discountAmount($originalAmount);
                                $couponDue = round(max(0, $originalAmount - ($savings + $couponSavings)), 2);
                            @endphp
                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="font-semibold">{{ $reward->code }}</p>
                                    <p class="mt-1 text-sm text-navy/70">{{ $reward->discountSummary() }} · Use before {{ $reward->expires_at?->format('M j, Y') }}</p>
                                    <p class="mt-1 text-sm font-medium text-emerald-700">Amount due becomes ${{ number_format($couponDue, 2) }} · save ${{ number_format($couponSavings, 2) }} more</p>
                                </div>
                                <form method="POST" action="{{ route('orders.payment.coupon', $order) }}">
                                    @csrf
                                    <input type="hidden" name="coupon_code" value="{{ $reward->code }}">
                                    <button class="btn-primary" type="submit">Use coupon</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($stripe)
                <form method="POST" action="{{ route('orders.payment.process', $order) }}">
                    @csrf
                    <input type="hidden" name="payment_method" value="stripe">
                    <button class="btn-primary w-full" type="submit">Pay with Stripe</button>
                </form>
            @endif
            @if ($demo)
                <form method="POST" action="{{ route('orders.payment.process', $order) }}" class="space-y-4">
                    @csrf
                    <p class="text-sm text-navy/65">Dummy payment gateway. No money is charged. Use this until Stripe is connected.</p>
                    <label class="block text-sm font-medium" for="demo_result">Payment result</label>
                    <select id="demo_result" name="demo_result" class="w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                        <option value="success">Successful payment</option>
                        <option value="declined">Declined payment</option>
                    </select>
                    <input type="hidden" name="payment_method" value="demo_card">
                    <button class="btn-primary w-full" type="submit">Pay with dummy gateway</button>
                </form>
            @endif
            @if (! $stripe && ! $demo)
                <p class="text-sm text-navy/65" role="status">Stripe checkout is currently unavailable.</p>
            @endif
        </section>
    </div>
@endsection
