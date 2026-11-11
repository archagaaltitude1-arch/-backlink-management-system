@extends('layouts.admin')

@section('title', 'Payment Management')

@section('content')
    @php
        $statusStyles = [
            'pending' => 'bg-amber-50 text-amber-800',
            'successful' => 'bg-emerald-50 text-emerald-700',
            'failed' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="space-y-6">
        <section>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
            <h1 class="mt-2 text-2xl font-semibold">Payment Management</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">The payment ledger for order charges and credit purchases.</p>
        </section>
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Payments</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $total }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Successful</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $successfulCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Failed</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $failedCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Collected</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">${{ number_format((float) $collectedTotal, 2) }}</p>
            </article>
        </section>
        <section class="panel overflow-hidden">
            <form method="GET" action="{{ route('admin.payments') }}" class="grid gap-3 border-b border-navy/10 px-5 py-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_11rem_auto] lg:items-end">
                <div class="min-w-0">
                    <label for="q" class="text-sm font-medium">Search</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Transaction, order number, customer, or method" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label for="status" class="text-sm font-medium">Status</label>
                    <select id="status" name="status" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                        <option value="">All</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-navy">Apply</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-[860px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Transaction</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Customer</th>
                            <th scope="col" class="px-4 py-3 font-semibold">For</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Method</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Amount</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Paid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-canvas/80">
                                <td class="px-5 py-3.5 font-medium whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.payments.show', $payment) }}" class="hover:text-brand">{{ $payment->transaction_id }}</a>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="block font-medium">{{ $payment->customer?->name ?? 'Unknown customer' }}</span>
                                    <span class="block text-xs text-navy/55">{{ $payment->customer?->email }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($payment->order)
                                        <a href="{{ route('admin.orders.show', $payment->order) }}" class="hover:text-brand">{{ $payment->order->order_number }}</a>
                                    @else
                                        {{ $payment->creditPackage?->name ?? 'Account payment' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-navy/70">{{ $payment->payment_method }}</td>
                                <td class="px-4 py-3.5 text-right font-medium whitespace-nowrap tabular-nums">${{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="badge {{ $statusStyles[$payment->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($payment->status) }}</span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-navy/70 sm:px-6">{{ $payment->paid_at?->format('M j, Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-sm text-navy/60 sm:px-6">
                                    {{ $search !== '' || $status !== '' ? 'No payments match this search.' : 'No payments have been recorded yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['records' => $payments])
        </section>
    </div>
@endsection
