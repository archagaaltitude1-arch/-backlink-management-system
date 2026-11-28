@extends('layouts.customer')

@section('title', 'Order '.$order->order_number)

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-brand hover:underline">← Back to orders</a>
                <p class="mt-5 text-xs font-semibold tracking-wider text-brand uppercase">Order details</p>
                <h1 class="mt-1 text-3xl font-semibold tracking-tight">{{ $order->order_number }}</h1>
                <p class="mt-2 text-sm text-navy/60">Placed {{ $order->created_at->format('M j, Y · g:i A') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($order->invoice)
                    <a class="btn-inverse" href="{{ route('customer.invoices.download', $order->invoice) }}">Download invoice</a>
                @endif
                @if (in_array($order->payment_status, ['pending', 'failed'], true))
                    <a class="btn-primary" href="{{ route('orders.payment', $order) }}">{{ $order->payment_status === 'failed' ? 'Retry payment' : 'Complete payment' }}</a>
                @endif
            </div>
        </header>

        @if (request()->query('stripe') === 'returned' && $order->payment_status !== 'paid')
            <p class="rounded-xl bg-sky-50 p-4 text-sm text-sky-900" role="status">Waiting for Stripe to confirm payment. Refresh this page in a moment.</p>
        @endif
        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 ring-1 ring-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-rose-200">{{ session('error') }}</div>
        @endif

        <section class="panel overflow-hidden" aria-labelledby="order-tracking-heading">
            <div class="flex flex-col gap-4 border-t-[3px] border-t-brand bg-gradient-to-r from-brand/10 to-transparent px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Live progress</p>
                    <h2 id="order-tracking-heading" class="mt-1 text-xl font-semibold">Track your order</h2>
                    <p class="mt-1 text-sm text-navy/65">{{ $tracking['overall']['waiting'] ?? 'All items in this order are complete.' }}</p>
                </div>
                <div class="rounded-xl bg-white px-4 py-3 ring-1 ring-navy/10 sm:min-w-40 sm:text-right">
                    <p class="text-xs font-medium text-navy/55">Overall status</p>
                    <p class="mt-1 font-semibold text-navy">{{ $tracking['overall']['status'] }}</p>
                    <p class="mt-1 text-sm font-semibold tabular-nums text-navy">${{ number_format($order->total_amount, 2) }}</p>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                @include('orders.partials.order-tracking-timeline', [
                    'steps' => $tracking['overall']['steps'],
                    'label' => 'Overall order progress',
                ])
            </div>
        </section>

        <section class="space-y-4" aria-labelledby="item-tracking-heading">
            <div>
                <p class="text-xs font-semibold tracking-wider text-navy/55 uppercase">Fulfillment</p>
                <h2 id="item-tracking-heading" class="mt-1 text-xl font-semibold">Progress by item</h2>
                <p class="mt-1 text-sm text-navy/60">Each placement moves through the timeline independently. The overall status advances when every item reaches that stage.</p>
            </div>

            @forelse ($tracking['items'] as $itemTracking)
                @php
                    $item = $itemTracking['item'];
                    $itemStatusTone = match ($itemTracking['status']) {
                        'Delivered' => 'bg-emerald-50 text-emerald-800',
                        'On the way' => 'bg-sky-50 text-sky-800',
                        'In progress' => 'bg-amber-50 text-amber-800',
                        default => 'bg-canvas text-navy/75',
                    };
                @endphp
                <article class="panel overflow-hidden">
                    <header class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-start sm:justify-between sm:px-6">
                        <div class="min-w-0">
                            <h3 class="font-semibold">{{ $item->item_name }}</h3>
                            <p class="mt-1 text-sm text-navy/60">
                                @if ($itemTracking['websites'] !== [])
                                    {{ implode(', ', $itemTracking['websites']) }} ·
                                @endif
                                @if ($item->backlinks)
                                    {{ number_format($item->backlinks) }} backlink placement(s)
                                @else
                                    Quantity {{ $item->quantity }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $itemStatusTone }}">{{ $itemTracking['status'] }}</span>
                            <span class="font-semibold tabular-nums">${{ number_format((float) $item->unit_price * $item->quantity, 2) }}</span>
                        </div>
                    </header>
                    <div class="p-5 sm:p-6">
                        @include('orders.partials.order-tracking-timeline', [
                            'steps' => $itemTracking['steps'],
                            'label' => $item->item_name.' progress',
                        ])
                        @if ($itemTracking['published_url'])
                            <a href="{{ $itemTracking['published_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-brand hover:underline">
                                View live placement
                                <span aria-hidden="true">↗</span>
                            </a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="panel p-6 text-sm text-navy/60">No fulfillment items are attached to this order yet.</div>
            @endforelse
        </section>

        <section class="panel overflow-hidden" aria-labelledby="order-summary-heading">
            <div class="border-t-[3px] border-t-navy bg-gradient-to-r from-navy/5 to-transparent px-5 py-4 sm:px-6">
                <p class="text-xs font-semibold tracking-wider text-navy/55 uppercase">Summary</p>
                <h2 id="order-summary-heading" class="mt-1 text-lg font-semibold">Order details</h2>
            </div>
            <div class="grid gap-4 border-b border-navy/10 p-5 sm:grid-cols-3 sm:px-6">
                <div>
                    <p class="text-xs uppercase text-navy/50">Order status</p>
                    <p class="mt-1 font-semibold">{{ str($order->order_status)->headline() }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-navy/50">Payment status</p>
                    <p class="mt-1 font-semibold">{{ str($order->payment_status)->headline() }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-navy/50">Total</p>
                    <p class="mt-1 font-semibold">${{ number_format($order->total_amount, 2) }}</p>
                    @if ((float) $order->discount_amount > 0)
                        <p class="mt-1 text-xs text-navy/55">${{ number_format((float) $order->discount_amount, 2) }} off{{ $order->coupon_code ? ' · '.$order->coupon_code : '' }}</p>
                    @endif
                </div>
            </div>
            @foreach ($order->items as $item)
                <div class="space-y-4 border-b border-navy/10 p-5 last:border-b-0 sm:p-6">
                    <div class="flex flex-wrap justify-between gap-3">
                        <h3 class="font-semibold">{{ $item->item_name }} <span class="font-normal text-navy/55">× {{ $item->quantity }}</span></h3>
                        <span class="font-semibold tabular-nums">${{ number_format((float) $item->unit_price * $item->quantity, 2) }}</span>
                    </div>
                    <dl class="grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-navy/50">Target URL</dt><dd class="mt-1 break-all font-medium">{{ $item->target_url }}</dd></div>
                        <div><dt class="text-navy/50">Anchor text</dt><dd class="mt-1 font-medium">{{ $item->anchor_text }}</dd></div>
                        <div><dt class="text-navy/50">Article topic</dt><dd class="mt-1 font-medium">{{ $item->article_topic }}</dd></div>
                        <div><dt class="text-navy/50">Requirements</dt><dd class="mt-1 font-medium">{{ $item->requirements ?: 'None provided' }}</dd></div>
                    </dl>
                </div>
            @endforeach
        </section>
    </div>
@endsection
