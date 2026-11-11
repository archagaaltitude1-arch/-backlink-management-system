@extends('layouts.admin')

@section('title', $payment->transaction_id)

@section('content')
    <div class="space-y-6">
        <section>
            <a href="{{ route('admin.payments') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Payment Management</a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $payment->transaction_id }}</h1>
            <p class="mt-2 text-sm text-navy/70">Recorded {{ $payment->created_at?->format('M j, Y g:i A') }} through {{ $payment->gateway }}.</p>
        </section>
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Amount</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">${{ number_format((float) $payment->amount, 2) }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Status</p>
                <p class="mt-3 text-lg font-semibold">{{ ucfirst($payment->status) }}</p>
                <p class="mt-1 text-sm text-navy/60">{{ $payment->paid_at ? 'Paid '.$payment->paid_at->format('M j, Y g:i A') : 'Not paid' }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Customer</p>
                <p class="mt-3 text-lg font-semibold">{{ $payment->customer?->name ?? 'Unknown customer' }}</p>
                <p class="mt-1 text-sm text-navy/60">{{ $payment->customer?->email }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Method</p>
                <p class="mt-3 text-lg font-semibold">{{ $payment->payment_method }}</p>
                <p class="mt-1 text-sm text-navy/60">{{ ucfirst($payment->gateway) }}</p>
            </article>
        </section>
        <section class="panel p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Applied to</h2>
            @if ($payment->order)
                <p class="mt-3 text-sm text-navy/70">
                    Order
                    <a href="{{ route('admin.orders.show', $payment->order) }}" class="font-semibold text-brand">{{ $payment->order->order_number }}</a>
                    · {{ ucfirst($payment->order->order_status) }} · {{ ucfirst($payment->order->payment_status) }}
                </p>
                <ul class="mt-4 divide-y divide-navy/10">
                    @foreach ($payment->order->items as $item)
                        <li class="flex items-center justify-between gap-4 py-3 text-sm">
                            <span>{{ $item->item_name }}</span>
                            <span class="tabular-nums">${{ number_format((float) $item->unit_price * $item->quantity, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            @elseif ($payment->creditPackage)
                <p class="mt-3 text-sm text-navy/70">Credit package <span class="font-semibold">{{ $payment->creditPackage->name }}</span> · {{ $payment->creditPackage->credits }} credits</p>
            @else
                <p class="mt-3 text-sm text-navy/60">This payment is not linked to an order or credit package.</p>
            @endif
        </section>
    </div>
@endsection
