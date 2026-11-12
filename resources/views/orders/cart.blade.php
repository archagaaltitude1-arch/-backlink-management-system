@extends('layouts.customer')

@section('title', 'Cart')

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <h1 class="mt-2 text-3xl font-semibold">Shopping cart</h1>
            <p class="mt-2 text-sm text-navy/70">Review your backlink services before adding the order details.</p>
        </div>

        @if (session('success'))
            <div class="rounded-xl bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <section class="panel overflow-hidden">
                @forelse ($cart->items as $item)
                    <div class="flex flex-col gap-4 border-b border-navy/10 p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-semibold">{{ $item->item_name }}</h2>
                            <p class="mt-1 text-sm text-navy/60">${{ number_format($item->unit_price, 2) }} each · {{ $item->credit_cost }} credits</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('orders.cart.items.update', $item) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label class="sr-only" for="quantity-{{ $item->id }}">Quantity</label>
                                <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="99" value="{{ $item->quantity }}" class="w-20 rounded-lg border border-navy/15 px-3 py-2 text-sm">
                                <button class="btn-navy" type="submit">Update</button>
                            </form>
                            <form method="POST" action="{{ route('orders.cart.items.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn rounded-xl text-red-700 hover:bg-red-50" type="submit">Remove</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-navy/60">Your cart is empty.</div>
                @endforelse
            </section>

            <aside class="panel h-fit p-6">
                <h2 class="text-lg font-semibold">Add a service</h2>
                <form method="POST" action="{{ route('orders.cart.items.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-medium" for="product_key">Service</label>
                        <select id="product_key" name="product_key" required class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
                            @foreach ($catalog as $key => $product)
                                <option value="{{ $key }}">{{ $product['name'] }} · ${{ number_format($product['price'], 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium" for="quantity">Quantity</label>
                        <input id="quantity" name="quantity" type="number" min="1" max="99" value="1" required class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
                    </div>
                    <button class="btn-primary w-full" type="submit">Add to cart</button>
                </form>

                <div class="mt-6 border-t border-navy/10 pt-5">
                    @if ($cart->coupon_code)
                        <div class="mb-3 flex justify-between text-sm"><span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span></div>
                        <div class="mb-3 flex justify-between text-sm text-green-700"><span>Coupon {{ $cart->coupon_code }}</span><span>−${{ number_format($cart->coupon_discount, 2) }}</span></div>
                        <form method="POST" action="{{ route('orders.cart.coupon.destroy') }}" class="mb-4">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs font-semibold text-brand hover:underline" type="submit">Remove coupon</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('orders.cart.coupon') }}" class="mb-4 flex gap-2">
                            @csrf
                            <label class="sr-only" for="coupon_code">Coupon code</label>
                            <input id="coupon_code" name="coupon_code" placeholder="Coupon code" class="min-w-0 flex-1 rounded-lg border border-navy/15 px-3 py-2 text-sm">
                            <button class="btn-navy" type="submit">Apply</button>
                        </form>
                        @error('coupon_code')
                            <p class="mb-3 text-xs text-red-700">{{ $message }}</p>
                        @enderror
                    @endif
                    <div class="flex justify-between font-semibold"><span>Total</span><span>${{ number_format($total, 2) }}</span></div>
                    @if ($cart->items->isNotEmpty())
                        <a class="btn-primary mt-4 w-full" href="{{ route('orders.checkout') }}">Continue to checkout</a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
@endsection