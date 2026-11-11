@extends('layouts.admin')

@section('title', 'Customer Statistics')

@section('content')
    <div class="space-y-6">
        <section>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
            <h1 class="mt-2 text-2xl font-semibold">Customer Statistics</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-navy/70">Registration and activity counts from existing customer accounts. {{ $inRange }} customers registered during {{ $period->label() }}.</p>
        </section>
        @include('admin.partials.period-filter', ['action' => route('admin.customer-statistics'), 'period' => $period])
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-5"><p class="text-sm text-navy/60">Registered customers</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $total }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">New today</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $today }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">New this week</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $week }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">New this month</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $month }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">Active</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $active }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">Inactive</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $inactive }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">Suspended</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $suspended }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">With an order</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $withOrders }}</p></article>
        </section>
        <section class="panel p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Registration trend</h2>
            <p class="mt-1 text-sm text-navy/60">New customer accounts over the last six months.</p>
            <div class="mt-6">@include('admin.partials.bar-chart', ['chart' => $chart])</div>
        </section>
        <section class="panel overflow-hidden">
            <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold">Recent registrations</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[720px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Customer</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Orders</th>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Registered</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($recent as $customer)
                            <tr>
                                <td class="px-5 py-3.5 sm:px-6">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="font-medium hover:text-brand">{{ $customer->name }}</a>
                                    <p class="text-xs text-navy/55">{{ $customer->email }}</p>
                                </td>
                                <td class="px-4 py-3.5">{{ ucfirst($customer->status) }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $customer->orders_count }}</td>
                                <td class="px-5 py-3.5 text-navy/70 sm:px-6">{{ $customer->created_at?->format('M j, Y g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No customer accounts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
