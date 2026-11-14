@extends('layouts.customer')

@section('title', 'Checkout')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <header class="rounded-2xl bg-navy p-6 text-white sm:p-8">
            <a href="{{ route('orders.cart') }}" class="text-sm text-white/75 hover:underline">Back to cart</a>
            <h1 class="mt-2 text-3xl font-semibold">Checkout</h1>
            <p class="mt-2 text-sm text-white/75">Apply your discount, complete payment, then tell us what to publish.</p>
        </header>

        @if (session('error') || $errors->any())
            <p role="alert" class="rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ session('error') ?: $errors->first() }}</p>
        @endif
        @if (session('success'))
            <p role="status" class="rounded-xl bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</p>
        @endif

        <div class="grid items-start gap-5 md:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)]">
            <section class="panel space-y-4 p-6">
                <h2 class="text-lg font-semibold">Checkout discount</h2>
                <p class="text-sm text-navy/70">Request a one-use 10% coupon for this checkout.</p>
                <form method="POST" action="{{ route('orders.checkout.coupon') }}">
                    @csrf
                    <button type="submit" class="btn-navy">Email my coupon</button>
                </form>

                @if ($cart->coupon_code)
                    <form method="POST" action="{{ route('orders.cart.coupon.destroy') }}" class="flex flex-wrap items-center justify-between gap-2 border-t border-navy/10 pt-4">
                        @csrf
                        @method('DELETE')
                        <span class="break-all text-sm text-green-800">{{ $cart->coupon_code }} applied</span>
                        <button type="submit" class="text-sm font-semibold underline">Remove</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('orders.cart.coupon') }}" class="border-t border-navy/10 pt-4">
                        @csrf
                        <label for="checkout-code" class="text-sm font-medium">Coupon code</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <input id="checkout-code" name="coupon_code" required maxlength="40" class="min-w-0 flex-1 rounded-lg border border-navy/20 px-3 py-2 text-sm">
                            <button type="submit" class="btn-navy">Apply coupon</button>
                        </div>
                    </form>
                @endif
            </section>

            <section class="panel p-6">
                <h2 class="text-lg font-semibold">Order summary</h2>
                <ul class="mt-4 divide-y divide-navy/10">
                    @foreach ($cart->items as $item)
                        <li class="flex justify-between gap-4 py-3 text-sm">
                            <span>{{ $item->item_name }} × {{ $item->quantity }}</span>
                            <span class="shrink-0">${{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-3 space-y-2 border-t border-navy/10 pt-4">
                    <div class="flex justify-between text-sm"><dt>Subtotal</dt><dd>${{ number_format($subtotal, 2) }}</dd></div>
                    @if ($cart->coupon_code)
                        <div class="flex justify-between text-sm text-green-800"><dt>Discount</dt><dd>−${{ number_format($cart->coupon_discount, 2) }}</dd></div>
                    @endif
                    <div class="flex justify-between text-lg font-semibold"><dt>Total</dt><dd>${{ number_format($total, 2) }}</dd></div>
                </dl>
                <form method="POST" action="{{ route('orders.checkout.payment') }}" class="mt-5">
                    @csrf
                    <button type="submit" class="btn-primary w-full">Continue to payment</button>
                </form>
            </section>
        </div>
    </div>
@endsection
