@extends('layouts.customer')

@section('title', 'Thank you')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6 py-6">
        @if ($order->payment_status !== 'paid')
            <section class="panel p-6 sm:p-8">
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Payment</p>
                <h1 class="mt-2 text-3xl font-semibold">Waiting for confirmation</h1>
                <p class="mt-3 text-sm text-navy/70" role="status">Waiting for Stripe to confirm payment for {{ $order->order_number }}. Refresh this page in a moment.</p>
            </section>
        @else
            <section class="panel p-6 sm:p-8">
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Payment complete</p>
                <h1 class="mt-2 text-3xl font-semibold">Thank you</h1>
                <p class="mt-3 text-sm text-navy/70">Your payment for {{ $order->order_number }} is confirmed. The order total is ${{ number_format($order->total_amount, 2) }} and it is awaiting approval.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($order->invoice)
                        <a class="btn-primary" href="{{ route('customer.invoices.download', $order->invoice) }}">Download invoice</a>
                    @endif
                    <a class="btn-navy" href="{{ route('packages.index') }}">Shop more</a>
                </div>
            </section>
            @if (session('success'))
                <p class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('success') }}</p>
            @endif
            <section class="panel p-6">
                <h2 class="font-semibold">Shop more to gain more discounts</h2>
                <p class="mt-2 text-sm text-navy/70">{{ $shopMore }}</p>
                <a class="mt-4 inline-flex font-semibold text-brand hover:underline" href="{{ route('customer.coupons') }}">View coupons</a>
            </section>
            <a class="text-sm font-semibold text-navy hover:underline" href="{{ route('orders.show', $order) }}">View order</a>
        @endif
    </div>
@endsection
