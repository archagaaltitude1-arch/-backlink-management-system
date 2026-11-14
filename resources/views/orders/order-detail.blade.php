@extends('layouts.customer')

@section('title', 'Order '.$order->order_number)

@section('content')
    @if ($order->order_status === 'awaiting_details')
        <div class="mx-auto max-w-5xl space-y-6">
            <header class="rounded-2xl bg-navy p-6 text-white sm:p-8">
                <p class="text-sm text-white/75">{{ $order->order_number }} · Paid</p>
                <h1 class="mt-2 text-3xl font-semibold">Publishing details</h1>
                <p class="mt-2 text-sm text-white/75">Tell us what to publish so we can send your order for review.</p>
            </header>
            @if ($errors->any())
                <ul role="alert" class="list-inside list-disc rounded-xl bg-red-50 p-4 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <form method="POST" action="{{ route('orders.details', $order) }}" class="space-y-5">
                @csrf
                @foreach ($order->items as $index => $item)
                    <section class="panel p-6">
                        <h2 class="text-lg font-semibold">{{ $item->item_name }}</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach (['target_url' => 'Target URL', 'anchor_text' => 'Anchor text', 'article_topic' => 'Article topic'] as $field => $label)
                                <label class="block text-sm font-medium">
                                    {{ $label }}
                                    <input name="items[{{ $index }}][{{ $field }}]" type="{{ $field === 'target_url' ? 'url' : 'text' }}" value="{{ old("items.$index.$field", $item->$field) }}" required maxlength="{{ $field === 'target_url' ? 2048 : 255 }}" class="mt-1 w-full rounded-lg border border-navy/20 px-3 py-2">
                                </label>
                            @endforeach
                            <label class="block text-sm font-medium sm:col-span-2">
                                Requirements <span class="font-normal text-navy/60">(optional)</span>
                                <textarea name="items[{{ $index }}][requirements]" rows="3" maxlength="5000" class="mt-1 w-full rounded-lg border border-navy/20 px-3 py-2">{{ old("items.$index.requirements", $item->requirements) }}</textarea>
                            </label>
                        </div>
                    </section>
                @endforeach
                <div class="flex flex-wrap items-center gap-4">
                    <button type="submit" class="btn-primary">Submit for review</button>
                    @if ($order->invoice)
                        <a class="text-sm font-semibold underline" href="{{ route('customer.invoices.download', $order->invoice) }}">Download receipt</a>
                    @endif
                </div>
            </form>
        </div>
    @else
    <div class="mx-auto max-w-5xl space-y-6"><div><h1 class="mt-2 text-3xl font-semibold">{{ $order->order_number }}</h1><p class="mt-2 text-sm text-navy/60">Placed {{ $order->created_at->format('M j, Y g:i A') }}</p></div><section class="panel overflow-hidden"><div class="grid gap-4 border-b border-navy/10 p-6 sm:grid-cols-3"><div><p class="text-xs uppercase text-navy/50">Order status</p><p class="mt-1 font-semibold">{{ str($order->order_status)->headline() }}</p></div><div><p class="text-xs uppercase text-navy/50">Payment status</p><p class="mt-1 font-semibold">{{ ucfirst($order->payment_status) }}</p></div><div><p class="text-xs uppercase text-navy/50">Total</p><p class="mt-1 font-semibold">${{ number_format($order->total_amount, 2) }}</p></div></div>@foreach ($order->items as $item)<div class="space-y-3 border-b border-navy/10 p-6"><div class="flex justify-between gap-4"><h2 class="font-semibold">{{ $item->item_name }}</h2><span>${{ number_format($item->unit_price * $item->quantity, 2) }}</span></div><dl class="grid gap-3 text-sm sm:grid-cols-2"><div><dt class="text-navy/50">Target URL</dt><dd class="break-all font-medium">{{ $item->target_url }}</dd></div><div><dt class="text-navy/50">Anchor text</dt><dd class="font-medium">{{ $item->anchor_text }}</dd></div><div><dt class="text-navy/50">Article topic</dt><dd class="font-medium">{{ $item->article_topic }}</dd></div><div><dt class="text-navy/50">Requirements</dt><dd class="font-medium">{{ $item->requirements ?: 'None provided' }}</dd></div></dl></div>@endforeach</section><a class="btn-navy" href="{{ route('orders.index') }}">Back to orders</a></div>
        @if (request()->query('stripe') === 'returned' && $order->payment_status !== 'paid')
            <p class="rounded-xl bg-sky-50 p-4 text-sm text-sky-900" role="status">Waiting for Stripe to confirm payment. Refresh this page in a moment.</p>
        @endif
        @if ($order->order_status === 'awaiting_approval')
            <p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Payment confirmed. Your order is awaiting admin approval.</p>
        @endif
        @if ($order->invoice)
            <a class="btn-primary" href="{{ route('customer.invoices.download', $order->invoice) }}">Download invoice</a>
        @endif
        @if ($order->rewardCoupon)
            <section class="panel p-5">
                <h2 class="font-semibold">10% off your next order</h2>
                <p class="mt-2 text-sm">Use <strong>{{ $order->rewardCoupon->code }}</strong> at checkout. Valid for one use through {{ $order->rewardCoupon->expires_at->format('M j, Y') }}.</p>
            </section>
        @endif
        @if (session('success'))
            <div class="rounded-xl bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
        @endif
        @if (in_array($order->payment_status, ['pending', 'failed'], true))
            <a class="btn-primary" href="{{ route('orders.payment', $order) }}">{{ $order->payment_status === 'failed' ? 'Retry payment' : 'Complete payment' }}</a>
        @endif
    @endif
@endsection
