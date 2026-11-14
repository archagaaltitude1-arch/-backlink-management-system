@extends('layouts.customer')

@section('title', 'Customer Dashboard')

@section('content')
    @php
        $orderNeedingDetails = $orders->firstWhere('order_status', 'awaiting_details');
    @endphp

    <div class="mx-auto w-full max-w-6xl space-y-6 font-sans">
        <section aria-labelledby="account-overview" class="grid overflow-hidden rounded-3xl border border-navy/10 bg-canvas md:grid-cols-[minmax(0,1fr)_minmax(16rem,0.48fr)]">
            <div class="min-w-0 border-l-4 border-brand px-5 py-6 sm:px-6 sm:py-8">
                <h2 id="account-overview" class="max-w-[24ch] break-words font-display text-3xl leading-tight font-bold tracking-tight text-navy sm:text-5xl">Welcome, {{ $customer->name }}</h2>
                @if ($orderNeedingDetails)
                    <p class="mt-3 text-sm text-navy/75">Order {{ $orderNeedingDetails->order_number }} is paid. Add your publishing details to send it for review.</p>
                @endif
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($orderNeedingDetails)
                        <a class="inline-flex min-h-11 items-center rounded-full bg-brand px-5 text-sm font-semibold text-navy hover:bg-brand/85 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-navy" href="{{ route('orders.show', $orderNeedingDetails) }}">Add publishing details</a>
                    @else
                        <a class="inline-flex min-h-11 items-center rounded-full bg-brand px-5 text-sm font-semibold text-navy hover:bg-brand/85 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-navy" href="{{ route('publishers.index') }}">Browse publishers</a>
                    @endif
                    <a class="inline-flex min-h-11 items-center rounded-full px-5 text-sm font-semibold text-navy ring-1 ring-inset ring-navy/30 hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-navy" href="{{ route('orders.cart') }}">View cart</a>
                </div>
            </div>
            <div class="flex min-w-0 flex-col justify-between bg-navy px-5 py-6 text-white sm:px-6 sm:py-8">
                <div>
                    <p class="text-sm font-medium text-white/85">Available credits</p>
                    <p class="mt-2 break-words font-display text-5xl font-bold tracking-tight text-brand tabular-nums">{{ number_format($wallet->available_balance) }}</p>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-between gap-2 border-t border-white/20 pt-4 text-sm">
                    <span class="text-white/85">{{ number_format($wallet->reserved_balance) }} reserved</span>
                    <a class="font-semibold underline underline-offset-4" href="{{ route('customer.wallet') }}">Manage wallet</a>
                </div>
            </div>
        </section>

        <nav aria-label="Account shortcuts" class="grid divide-y divide-navy/10 overflow-hidden rounded-2xl border border-navy/10 bg-white sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            @foreach ([
                ['Orders', $orderCount, 'orders.index'],
                ['Saved websites', $savedWebsiteCount, 'customer.saved-websites'],
                ['Unread notifications', $unreadNotificationCount, 'customer.notifications'],
            ] as [$label, $count, $destination])
                <a href="{{ route($destination) }}" class="flex min-w-0 items-center justify-between gap-3 px-5 py-4 text-navy transition hover:bg-canvas focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-navy sm:flex-col sm:items-start">
                    <span class="text-sm font-medium">{{ $label }}</span>
                    <span class="font-display text-2xl font-bold tabular-nums">{{ number_format($count) }}</span>
                </a>
            @endforeach
        </nav>

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
            <section aria-labelledby="recent-orders" class="panel overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-navy/10 bg-canvas px-5 py-4 text-navy">
                    <h2 id="recent-orders" class="font-bold">Recent orders</h2>
                    <a class="text-sm font-semibold text-navy hover:underline" href="{{ route('orders.index') }}">All orders</a>
                </div>
                <ul class="divide-y divide-navy/10">
                    @forelse ($orders as $order)
                        <li>
                            <a href="{{ route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 hover:bg-canvas/60">
                                <div class="min-w-0">
                                    <p class="break-words font-semibold">{{ $order->order_number }}</p>
                                    <p class="mt-1 break-words text-sm text-navy/75">{{ $order->items->pluck('item_name')->implode(', ') }}</p>
                                    <p class="mt-1 text-xs text-navy/65">{{ $order->created_at->format('M j, Y') }} · {{ $order->items->sum('quantity') }} item(s)</p>
                                </div>
                                <div class="sm:text-right">
                                    <p class="font-semibold tabular-nums">${{ number_format($order->total_amount, 2) }}</p>
                                    <span @class(['badge mt-1', 'bg-brand/15 text-navy' => $order->order_status === 'awaiting_details', 'bg-canvas text-navy' => $order->order_status !== 'awaiting_details'])>{{ str($order->order_status)->headline() }}</span>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="p-5 text-sm text-navy/65">No orders yet.</li>
                    @endforelse
                </ul>
            </section>

            <div class="grid gap-5">
                <section aria-labelledby="project-progress" class="panel overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-navy/10 bg-canvas px-5 py-4 text-navy">
                        <h2 id="project-progress" class="font-bold">Project progress</h2>
                        <a class="text-sm font-semibold text-navy hover:underline" href="{{ route('customer.projects') }}">All projects</a>
                    </div>
                    <ul class="divide-y divide-navy/10">
                        @forelse ($projects as $project)
                            <li class="px-5 py-4">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <p class="break-words text-sm font-semibold">{{ $project->name }}</p>
                                    <span class="badge bg-canvas text-navy">{{ str($project->status)->headline() }}</span>
                                </div>
                                <p class="mt-2 text-xs text-navy/65">{{ str($project->verification_status)->headline() }}</p>
                            </li>
                        @empty
                            <li class="p-5 text-sm text-navy/65">No projects yet.</li>
                        @endforelse
                    </ul>
                </section>

                <section aria-labelledby="saved-payment-card" class="panel p-5">
                    <h2 id="saved-payment-card" class="font-bold">Saved payment card</h2>
                    <p class="mt-2 text-sm text-navy/75">
                        {{ $savedCard ? $savedCard->brand.' ending in '.$savedCard->last_four : 'No demo card saved yet.' }}
                    </p>
                    <a class="mt-3 inline-flex text-sm font-semibold text-navy hover:underline" href="{{ route('customer.payment-methods') }}">Manage payment methods</a>
                </section>
            </div>
        </div>

        @include('components.customer-service-cards')
    </div>
@endsection
