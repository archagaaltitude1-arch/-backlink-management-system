@extends('layouts.customer')

@section('title', 'Payment')

@section('content')
    @php
        $demo = \App\Http\StripeCheckout::usesDemo();
        $stripe = \App\Http\StripeCheckout::isConfigured();
    @endphp
    <div class="mx-auto max-w-3xl space-y-6">
        <h1 class="text-3xl font-semibold">Complete payment</h1>
        @if ($errors->any())
            <p class="rounded-xl bg-red-50 p-4 text-sm text-red-800" role="alert">{{ $errors->first() }}</p>
        @endif
        <section class="panel space-y-5 p-6">
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-sm text-navy/55">Order</p>
                    <p class="font-semibold">{{ $order->order_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-navy/55">Amount due</p>
                    <p class="text-2xl font-semibold">${{ number_format($order->total_amount, 2) }}</p>
                </div>
            </div>
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
                    <p class="text-sm text-navy/65">Demo payment. No money will be charged.</p>
                    <label class="block text-sm font-medium" for="demo_result">Simulation result</label>
                    <select id="demo_result" name="demo_result" class="w-full rounded-lg border border-navy/15 px-3 py-2.5 text-sm">
                        <option value="success">Successful payment</option>
                        <option value="declined">Declined payment</option>
                    </select>
                    <input type="hidden" name="payment_method" value="demo_card">
                    <button class="btn-primary w-full" type="submit">Simulate payment</button>
                </form>
            @endif
            @if (! $stripe && ! $demo)
                <p class="text-sm text-navy/65" role="status">Stripe checkout is currently unavailable.</p>
            @endif
        </section>
    </div>
@endsection
