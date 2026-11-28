@extends('layouts.customer')

@section('title', 'Customer Dashboard')

@section('content')
    @php
        $stats = [
            ['label' => 'Wallet balance', 'value' => number_format($wallet->available_balance), 'hint' => number_format($wallet->reserved_balance).' credits reserved', 'href' => route('customer.wallet')],
            ['label' => 'Active orders', 'value' => number_format($activeOrderCount), 'hint' => 'Track current placements', 'href' => route('orders.index')],
            ['label' => 'Needs your action', 'value' => number_format($actionOrderCount), 'hint' => 'Payment or checkout needed', 'href' => route('orders.index')],
            ['label' => 'Available coupons', 'value' => number_format($availableCoupons->count()), 'hint' => 'Offers ready to use', 'href' => route('customer.coupons')],
        ];
        $actions = [
            ['label' => 'Browse websites', 'detail' => 'Find publishers for your next backlink', 'href' => route('customer.websites')],
            ['label' => 'Explore services', 'detail' => 'Compare link building options', 'href' => route('customer.services')],
            ['label' => 'View order history', 'detail' => 'Track payments and publishing', 'href' => route('orders.index')],
        ];
    @endphp

    <div class="mx-auto max-w-[1500px] space-y-5 sm:space-y-6">
        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl bg-navy text-white shadow-[0_16px_36px_rgba(21,32,41,0.14)]">
            <div class="h-1.5 bg-brand"></div>
            <div class="flex flex-col gap-5 bg-[radial-gradient(ellipse_at_0%_0%,rgba(255,73,23,0.18),transparent_45%)] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7 sm:py-6">
                <div class="min-w-0">
                    <p class="text-xs font-semibold tracking-[0.12em] text-brand uppercase">Customer dashboard</p>
                    <h1 class="mt-1.5 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Welcome back, {{ $customer->name }}</h1>
                    <p class="mt-1.5 text-sm text-white/65">{{ now()->format('l, F j, Y') }} <span class="px-1.5 text-white/35" aria-hidden="true">•</span> Here’s what’s happening with your orders.</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <a href="{{ route('customer.websites') }}" class="btn-primary">Browse websites</a>
                    <a href="{{ route('customer.services') }}" class="btn-inverse">Explore services</a>
                </div>
            </div>
        </section>

        <section aria-label="Account overview" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <a href="{{ $stat['href'] }}" class="panel group flex min-h-28 flex-col justify-between overflow-hidden border-l-[3px] border-l-brand px-5 py-4 transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex flex-1 flex-col justify-between">
                        <span class="text-sm font-medium text-navy/60">{{ $stat['label'] }}</span>
                        <span class="mt-1 font-display text-2xl font-semibold tracking-tight text-navy tabular-nums">{{ $stat['value'] }}</span>
                        <span class="mt-1 text-xs text-navy/50 group-hover:text-navy/70">{{ $stat['hint'] }}</span>
                    </span>
                </a>
            @endforeach
        </section>

        @if ($actionOrders->isNotEmpty())
            <section class="panel overflow-hidden border-l-[3px] border-l-brand" aria-labelledby="customer-action-heading">
                <div class="flex flex-col gap-3 border-b border-navy/10 bg-orange-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-brand-dark uppercase">Action needed</p>
                        <h2 id="customer-action-heading" class="mt-1 text-lg font-semibold">Finish payment to start your order</h2>
                    </div>
                    <span class="w-fit rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-dark ring-1 ring-brand/15">{{ $actionOrderCount }} order(s)</span>
                </div>
                <ul class="divide-y divide-navy/10">
                    @foreach ($actionOrders as $actionOrder)
                        <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="min-w-0">
                                <a href="{{ route('orders.show', $actionOrder) }}" class="font-semibold text-navy hover:text-brand">{{ $actionOrder->order_number }}</a>
                                <p class="mt-1 truncate text-sm text-navy/60">{{ $actionOrder->items->pluck('item_name')->implode(', ') ?: 'Order items' }}</p>
                                <p class="mt-1 text-xs text-navy/50">{{ $actionOrder->payment_status === 'failed' ? 'Payment failed' : 'Payment pending' }} · ${{ number_format((float) $actionOrder->total_amount, 2) }}</p>
                            </div>
                            <a href="{{ route('orders.payment', $actionOrder) }}" class="btn-primary shrink-0">{{ $actionOrder->payment_status === 'failed' ? 'Retry payment' : 'Complete payment' }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="space-y-4" aria-labelledby="active-order-heading">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Live tracking</p>
                    <h2 id="active-order-heading" class="mt-1 text-xl font-semibold">Active orders</h2>
                    <p class="mt-1 text-sm text-navy/60">Follow each placement from order to delivery.</p>
                </div>
                <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-brand-dark hover:underline">View all orders <span aria-hidden="true">→</span></a>
            </div>

            @forelse ($activeOrders as $activeOrder)
                @php
                    $order = $activeOrder['order'];
                    $tracking = $activeOrder['tracking'];
                @endphp
                <article class="panel overflow-hidden" aria-labelledby="tracking-order-{{ $order->id }}">
                    <header class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div>
                            <p class="text-xs font-medium text-navy/50">Order</p>
                            <h3 id="tracking-order-{{ $order->id }}" class="mt-0.5 font-semibold">{{ $order->order_number }}</h3>
                            <p class="mt-1 text-xs text-navy/55">Placed {{ $order->created_at->format('M j, Y · g:i A') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                            <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-brand-dark ring-1 ring-brand/15">{{ $tracking['overall']['status'] }}</span>
                            <span class="font-semibold tabular-nums">${{ number_format((float) $order->total_amount, 2) }}</span>
                            @if ($order->payment_status !== 'paid')
                                <a href="{{ route('orders.payment', $order) }}" class="text-sm font-semibold text-brand hover:underline">Complete payment</a>
                            @endif
                        </div>
                    </header>

                    <div class="px-5 py-5 sm:px-6">
                        <p class="mb-4 text-xs font-semibold tracking-wider text-navy/50 uppercase">Overall progress</p>
                        @include('orders.partials.order-tracking-timeline', [
                            'steps' => $tracking['overall']['steps'],
                            'label' => 'Overall progress for '.$order->order_number,
                        ])
                    </div>

                    <div class="divide-y divide-navy/10 border-t border-navy/10">
                        @foreach ($tracking['items'] as $itemTracking)
                            @php
                                $item = $itemTracking['item'];
                                $latestMilestone = collect($itemTracking['steps'])
                                    ->filter(fn (array $step): bool => $step['timestamp'] !== null)
                                    ->sortByDesc(fn (array $step): int => $step['timestamp']->getTimestamp())
                                    ->first();
                                $placementStatusTone = match ($itemTracking['status']) {
                                    'Delivered' => 'bg-emerald-50 text-emerald-800',
                                    'On the way' => 'bg-sky-50 text-sky-800',
                                    'In progress' => 'bg-amber-50 text-amber-800',
                                    default => 'bg-canvas text-navy/70',
                                };
                            @endphp
                            <section class="px-5 py-5 sm:px-6" aria-label="{{ $item->item_name }} placement tracking">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="font-semibold text-navy">{{ $item->item_name }}</h4>
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $placementStatusTone }}">{{ $itemTracking['status'] }}</span>
                                        </div>
                                        <dl class="mt-3 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2 xl:grid-cols-3">
                                            <div class="min-w-0">
                                                <dt class="text-xs text-navy/50">Site</dt>
                                                <dd class="mt-1 truncate font-medium">{{ implode(', ', $itemTracking['websites']) ?: 'Not assigned yet' }}</dd>
                                            </div>
                                            <div class="min-w-0">
                                                <dt class="text-xs text-navy/50">Target URL</dt>
                                                <dd class="mt-1 break-all font-medium">{{ $item->target_url ?: 'Waiting for target URL' }}</dd>
                                            </div>
                                            <div class="min-w-0">
                                                <dt class="text-xs text-navy/50">Anchor text</dt>
                                                <dd class="mt-1 break-words font-medium">{{ $item->anchor_text ?: 'Waiting for anchor text' }}</dd>
                                            </div>
                                        </dl>
                                        <p class="mt-3 text-xs text-navy/55">
                                            @if ($latestMilestone)
                                                Latest milestone: <span class="font-semibold text-navy/75">{{ $latestMilestone['label'] }}</span>
                                                <time datetime="{{ $latestMilestone['timestamp']->toIso8601String() }}">{{ $latestMilestone['timestamp']->format('M j, Y · g:i A') }}</time>
                                            @else
                                                Waiting for the first recorded update.
                                            @endif
                                        </p>
                                    </div>
                                    @if ($itemTracking['published_url'])
                                        <a href="{{ $itemTracking['published_url'] }}" target="_blank" rel="noopener noreferrer" class="shrink-0 text-sm font-semibold text-brand hover:underline">View live placement <span aria-hidden="true">↗</span></a>
                                    @endif
                                </div>
                                <div class="mt-5 rounded-xl bg-canvas/50 p-4 sm:p-5">
                                    @include('orders.partials.order-tracking-timeline', [
                                        'steps' => $itemTracking['steps'],
                                        'label' => $item->item_name.' progress',
                                    ])
                                </div>
                            </section>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="panel flex flex-col items-center px-6 py-9 text-center">
                    <span class="grid size-11 place-items-center rounded-full bg-brand/10 text-brand" aria-hidden="true">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h14v17H5zM9 4V2h6v2M9 9h6M9 13h6" /></svg>
                    </span>
                    <h3 class="mt-3 font-semibold">No active orders</h3>
                    <p class="mt-1 max-w-md text-sm leading-6 text-navy/55">New placements and their tracking milestones will appear here after checkout.</p>
                    <a href="{{ route('customer.websites') }}" class="btn-primary mt-4">Browse websites</a>
                </div>
            @endforelse
        </section>

        @if (! empty($firstOrder))
            <section class="panel overflow-hidden">
                <div class="flex flex-col gap-4 border-t-[3px] border-t-brand px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand text-sm font-bold text-white">10%</span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold tracking-wider text-brand uppercase">First order offer</p>
                            <h2 class="mt-1 text-lg font-semibold">{{ $firstOrder->summary() }} on your first order</h2>
                            <p class="mt-1 text-sm text-navy/60">Applied automatically at checkout.</p>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section class="panel overflow-hidden">
                <div class="flex flex-col gap-2 border-t-[3px] border-t-brand bg-gradient-to-r from-brand/5 to-transparent px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-brand uppercase">Offers</p>
                        <h2 class="mt-1 text-lg font-semibold">Available coupons</h2>
                        <p class="mt-1 text-sm text-navy/60">Choose a coupon to apply to your next purchase.</p>
                    </div>
                    <a href="{{ route('customer.coupons') }}" class="text-sm font-semibold text-brand-dark hover:underline">All coupons</a>
                </div>
                @if ($availableCoupons->isNotEmpty())
                    <div class="divide-y divide-navy/10">
                    @foreach ($availableCoupons as $coupon)
                        <article class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-6">
                            <div class="min-w-0">@include('customer.coupons.order-reward', ['coupon' => $coupon, 'showActions' => false])</div>
                            <form method="POST" action="{{ route('customer.coupons.apply', $coupon) }}">
                                @csrf
                                <button class="btn-primary" type="submit">Use on next purchase</button>
                            </form>
                        </article>
                    @endforeach
                    </div>
                @else
                    <div class="px-5 py-6 text-sm text-navy/55 sm:px-6">You don’t have any available coupons right now.</div>
                @endif
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
            <div class="panel overflow-hidden">
                <div class="flex items-center justify-between gap-4 border-t-[3px] border-t-brand px-5 py-4 sm:px-6">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-brand uppercase">Activity</p>
                        <h2 class="mt-1 text-lg font-semibold">Recent orders</h2>
                        <p class="mt-1 text-sm text-navy/60">Track the latest work and payment status.</p>
                    </div>
                    <a class="shrink-0 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-navy ring-1 ring-navy/10 transition hover:bg-canvas" href="{{ route('orders.index') }}">All orders</a>
                </div>
                <ul class="divide-y divide-navy/10">
                    @forelse ($orders as $order)
                        @php
                            $orderStatus = $order->payment_status !== 'paid'
                                ? 'Awaiting payment'
                                : ($order->order_status === 'awaiting_approval' ? 'Awaiting approval' : str($order->order_status)->headline());
                            $statusTone = $order->payment_status !== 'paid' || $order->order_status === 'awaiting_approval'
                                ? 'bg-amber-50 text-amber-800'
                                : ($order->order_status === 'completed' ? 'bg-emerald-50 text-emerald-800' : 'bg-sky-50 text-sky-800');
                        @endphp
                        <li>
                            <a href="{{ route('orders.show', $order) }}" class="flex flex-col gap-3 px-5 py-4 transition hover:bg-canvas/70 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                                <div class="min-w-0">
                                    <p class="font-medium">{{ $order->order_number }}</p>
                                    <p class="mt-1 truncate text-sm text-navy/65">{{ $order->items->pluck('item_name')->implode(', ') ?: 'Order details' }}</p>
                                    <p class="mt-1 text-xs text-navy/50">{{ $order->created_at->format('M j, Y') }} · {{ $order->items->sum('quantity') }} item(s)</p>
                                </div>
                                <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTone }}">{{ $orderStatus }}</span>
                                    <span class="font-semibold tabular-nums">${{ number_format($order->total_amount, 2) }}</span>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="flex flex-col items-center px-5 py-9 text-center sm:px-6">
                            <span class="grid size-11 place-items-center rounded-full bg-brand/10 text-brand" aria-hidden="true">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h14v17H5zM9 4V2h6v2M9 9h6M9 13h6" /></svg>
                            </span>
                            <p class="mt-3 font-semibold text-navy">Your order activity will show up here</p>
                            <p class="mt-1 max-w-sm text-sm leading-6 text-navy/55">Browse websites and services to get started. You can track each order from this page.</p>
                            <a href="{{ route('customer.websites') }}" class="btn-primary mt-4">Find websites</a>
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="space-y-6">
                <section class="panel overflow-hidden">
                    <div class="flex items-center justify-between gap-4 border-t-[3px] border-t-brand px-5 py-4 sm:px-6">
                        <div>
                            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Delivery</p>
                            <h2 class="mt-1 text-lg font-semibold">Project progress</h2>
                        </div>
                        <a href="{{ route('customer.projects') }}" class="text-sm font-semibold text-brand-dark hover:underline">All projects</a>
                    </div>
                    <ul class="divide-y divide-navy/10">
                        @forelse ($projects as $project)
                            <li class="px-5 py-4 sm:px-6">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <p class="font-medium">{{ $project->name }}</p>
                                    <span class="badge bg-orange-100 text-orange-800">{{ str($project->status)->headline() }}</span>
                                </div>
                                <p class="mt-2 text-xs text-navy/55">{{ $project->verification_status === 'verified' ? 'Backlink verified' : 'Verification pending' }}</p>
                                @if ($project->published_url)
                                    <a href="{{ $project->published_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex text-sm font-semibold text-brand hover:underline">View published link</a>
                                @endif
                            </li>
                        @empty
                            <li class="px-5 py-7 text-sm text-navy/55 sm:px-6">Your projects will appear here after your first order.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="panel overflow-hidden">
                    <div class="border-t-[3px] border-t-brand px-5 py-4 sm:px-6">
                        <p class="text-xs font-semibold tracking-wider text-brand uppercase">Shortcuts</p>
                        <h2 class="mt-1 text-lg font-semibold">Quick actions</h2>
                    </div>
                    <div class="grid gap-3 p-5 sm:px-6">
                        @foreach ($actions as $action)
                            <a href="{{ $action['href'] }}" class="flex items-center gap-4 rounded-xl bg-[#F7F8FA] px-4 py-3 ring-1 ring-navy/10 transition hover:bg-brand/5 hover:ring-brand/20">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand text-lg font-semibold text-white" aria-hidden="true">+</span>
                                <span>
                                    <span class="block text-sm font-semibold">{{ $action['label'] }}</span>
                                    <span class="mt-0.5 block text-xs text-navy/55">{{ $action['detail'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>

                <section class="panel overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-t-[3px] border-t-brand px-5 py-4 sm:px-6">
                        <div>
                            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Payment</p>
                            <h2 class="mt-1 text-lg font-semibold">Saved payment methods</h2>
                        </div>
                        <a class="shrink-0 text-sm font-semibold text-brand-dark hover:underline" href="{{ route('customer.payment-methods') }}">Manage</a>
                    </div>
                    <ul class="divide-y divide-navy/10">
                        @forelse ($paymentMethods as $paymentMethod)
                            <li class="flex items-center justify-between gap-3 px-5 py-3.5 sm:px-6">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ $paymentMethod->label }}</p>
                                    <p class="mt-1 text-xs text-navy/50">{{ $paymentMethod->brand }} ending in {{ $paymentMethod->last_four }}</p>
                                </div>
                                @if ($paymentMethod->is_default)
                                    <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-800">Default</span>
                                @endif
                            </li>
                        @empty
                            <li class="px-5 py-5 text-sm text-navy/55 sm:px-6">No saved payment methods yet.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </section>

        <section class="panel overflow-hidden" aria-labelledby="recent-invoices-heading">
            <div class="flex items-center justify-between gap-4 border-t-[3px] border-t-brand px-5 py-4 sm:px-6">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Billing</p>
                    <h2 id="recent-invoices-heading" class="mt-1 text-lg font-semibold">Recent invoices</h2>
                </div>
                <a href="{{ route('customer.invoices') }}" class="text-sm font-semibold text-brand-dark hover:underline">All invoices</a>
            </div>
            @if ($invoices->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-sm">
                        <thead class="border-y border-navy/10 bg-canvas/60 text-xs font-semibold tracking-wide text-navy/55 uppercase">
                            <tr>
                                <th scope="col" class="px-5 py-3 sm:px-6">Invoice</th>
                                <th scope="col" class="px-5 py-3">Order</th>
                                <th scope="col" class="px-5 py-3">Issued</th>
                                <th scope="col" class="px-5 py-3">Total</th>
                                <th scope="col" class="px-5 py-3 text-right"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy/10">
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td class="px-5 py-3.5 font-semibold sm:px-6">{{ $invoice->invoice_number }}</td>
                                    <td class="px-5 py-3.5 text-navy/65">{{ $invoice->order?->order_number ?? '—' }}</td>
                                    <td class="px-5 py-3.5 text-navy/65">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</td>
                                    <td class="px-5 py-3.5 font-medium tabular-nums">${{ number_format((float) $invoice->total_amount, 2) }}</td>
                                    <td class="px-5 py-3.5 text-right"><a href="{{ route('customer.invoices.download', $invoice) }}" class="font-semibold text-brand hover:underline">Download<span class="sr-only"> {{ $invoice->invoice_number }}</span></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="border-t border-navy/10 px-5 py-5 text-sm text-navy/55 sm:px-6">Invoices will appear here after a payment is completed.</p>
            @endif
        </section>

        <section aria-label="Browse services">
            @include('components.customer-service-cards')
        </section>
    </div>
@endsection
