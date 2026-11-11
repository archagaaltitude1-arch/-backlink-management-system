@extends('layouts.admin')

@section('title', $order->order_number)

@section('content')
    <div class="space-y-6">
        @include('admin.niches.partials.alerts')
        @if ($errors->any())
            <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $errors->first() }}</p>
        @endif
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.orders') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Order Management</a>
                <h1 class="mt-2 text-2xl font-semibold">{{ $order->order_number }}</h1>
                <p class="mt-2 text-sm text-navy/70">Placed {{ $order->created_at?->format('M j, Y g:i A') }} by {{ $order->customer?->name ?? 'a guest checkout' }}.</p>
            </div>
        </section>
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Customer</p>
                <p class="mt-3 text-lg font-semibold">{{ $order->customer?->name ?? 'Guest checkout' }}</p>
                <p class="mt-1 text-sm text-navy/60">{{ $order->customer?->email ?? 'No account email' }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Order status</p>
                <p class="mt-3 text-lg font-semibold">{{ ucfirst($order->order_status) }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Payment status</p>
                <p class="mt-3 text-lg font-semibold">{{ ucfirst($order->payment_status) }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Total</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">${{ number_format((float) $order->total_amount, 2) }}</p>
                @if ((float) $order->discount_amount > 0)
                    <p class="mt-1 text-xs text-navy/55">Discount ${{ number_format((float) $order->discount_amount, 2) }}{{ $order->coupon_code ? ' · '.$order->coupon_code : '' }}</p>
                @endif
            </article>
        </section>
        <section class="panel overflow-hidden">
            <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold">Line items</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Item</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Target</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Qty</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($order->items as $item)
                            <tr>
                                <td class="px-5 py-3.5 sm:px-6">
                                    <p class="font-medium">{{ $item->item_name }}</p>
                                    <p class="mt-1 text-xs text-navy/55">{{ $item->article_topic }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="break-all">{{ $item->target_url }}</p>
                                    <p class="mt-1 text-xs text-navy/55">{{ $item->anchor_text }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $item->quantity }}</td>
                                <td class="px-5 py-3.5 text-right font-medium tabular-nums sm:px-6">${{ number_format((float) $item->unit_price * $item->quantity, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-sm text-navy/60 sm:px-6">This order has no line items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="divide-y divide-navy/10 border-t border-navy/10">
                @foreach ($order->items as $item)
                    <form method="POST" action="{{ route('admin.orders.assign', [$order, $item]) }}" class="grid gap-3 px-5 py-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_16rem_auto] lg:items-end">
                        @csrf
                        <div>
                            <p class="text-sm font-medium">{{ $item->item_name }}</p>
                            <p class="mt-1 text-xs text-navy/55">
                                {{ $item->assignment?->staff ? 'Assigned to '.$item->assignment->staff->name : 'Not assigned to staff yet' }}
                            </p>
                        </div>
                        <div>
                            <label for="staff-{{ $item->id }}" class="text-sm font-medium">SEO staff</label>
                            <select id="staff-{{ $item->id }}" name="staff_id" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30" @disabled($staff->isEmpty())>
                                @forelse ($staff as $member)
                                    <option value="{{ $member->id }}" @selected($item->assignment?->staff_id === $member->id)>{{ $member->name }}</option>
                                @empty
                                    <option value="">No active staff accounts</option>
                                @endforelse
                            </select>
                        </div>
                        <button type="submit" class="btn-navy" @disabled($staff->isEmpty())>Assign to SEO staff</button>
                    </form>
                @endforeach
            </div>
        </section>
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="panel overflow-hidden">
                <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-semibold">Payments</h2>
                </div>
                <ul class="divide-y divide-navy/10">
                    @forelse ($order->payments as $payment)
                        <li class="flex items-center justify-between gap-4 px-5 py-3.5 text-sm sm:px-6">
                            <div>
                                <a href="{{ route('admin.payments.show', $payment) }}" class="font-medium hover:text-brand">{{ $payment->transaction_id }}</a>
                                <p class="text-xs text-navy/55">{{ $payment->payment_method }} · {{ ucfirst($payment->status) }}</p>
                            </div>
                            <span class="tabular-nums">${{ number_format((float) $payment->amount, 2) }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-sm text-navy/60 sm:px-6">No payment has been recorded for this order.</li>
                    @endforelse
                </ul>
            </article>
            <article class="panel p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Invoice</h2>
                @if ($order->invoice)
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-navy/60">Number</dt>
                            <dd class="font-medium">{{ $order->invoice->invoice_number }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-navy/60">Issued</dt>
                            <dd class="font-medium">{{ $order->invoice->issued_at?->format('M j, Y') }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-navy/60">Amount</dt>
                            <dd class="font-medium tabular-nums">${{ number_format((float) $order->invoice->total_amount, 2) }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-4 text-sm text-navy/60">No invoice has been issued for this order.</p>
                @endif
            </article>
        </section>
    </div>
@endsection
