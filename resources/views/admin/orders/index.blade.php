@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
    @php
        $statusStyles = [
            'pending' => 'bg-amber-50 text-amber-800',
            'processing' => 'bg-sky-50 text-sky-800',
            'completed' => 'bg-emerald-50 text-emerald-700',
            'assigned' => 'bg-amber-50 text-amber-800',
            'in_progress' => 'bg-sky-50 text-sky-800',
            'published' => 'bg-purple-50 text-purple-700',
        ];
    @endphp

    <div class="space-y-6">
        @include('admin.niches.partials.alerts')
        <section>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
            <h1 class="mt-2 text-2xl font-semibold">Order Management</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-navy/70">Customer orders arrive here. Assign each placement to an SEO staff member so they can publish the backlink.</p>
        </section>
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Customer orders</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $orderCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Needs SEO staff</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $needsStaffCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">With SEO staff</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $withStaffCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Waiting for approval</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $awaitingApprovalCount }}</p>
            </article>
        </section>
        <section class="panel overflow-hidden">
            <form method="GET" action="{{ route('admin.orders') }}" class="grid gap-3 border-b border-navy/10 px-5 py-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_11rem_13rem_auto] lg:items-end">
                <div class="min-w-0">
                    <label for="q" class="text-sm font-medium">Search customer orders</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Order number, customer, or anchor text" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label for="order_status" class="text-sm font-medium">Order status</label>
                    <select id="order_status" name="order_status" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                        <option value="">All</option>
                        @foreach ($orderStatuses as $status)
                            <option value="{{ $status }}" @selected($orderStatus === $status)>{{ str($status)->headline() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="assignment" class="text-sm font-medium">SEO assignment</label>
                    <select id="assignment" name="assignment" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                        <option value="">All</option>
                        @foreach ($assignmentFilters as $value => $label)
                            <option value="{{ $value }}" @selected($assignment === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-navy">Apply</button>
            </form>
            <div class="divide-y divide-navy/10">
                @forelse ($orders as $order)
                    <article class="px-5 py-5 sm:px-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold hover:text-brand">{{ $order->order_number }}</a>
                                <p class="mt-1 text-sm text-navy/70">{{ $order->customer?->name ?? 'Guest checkout' }}</p>
                                <p class="text-xs text-navy/50">{{ $order->customer?->email }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="badge {{ $statusStyles[$order->order_status] ?? 'bg-canvas text-navy' }}">{{ str($order->order_status)->headline() }}</span>
                                <p class="mt-2 text-xs text-navy/50">{{ $order->created_at?->format('M j, Y') }}</p>
                            </div>
                        </div>
                        @forelse ($order->items as $item)
                            @php
                                $assignmentStatus = $item->assignment?->status;
                                $assignee = $item->assignment?->staff?->name;
                                $assignmentLabel = match ($assignmentStatus) {
                                    'assigned' => 'Assigned to '.$assignee,
                                    'in_progress' => 'In progress with '.$assignee,
                                    'published' => 'Waiting for customer approval · '.$assignee,
                                    'completed' => 'Approved · '.$assignee,
                                    default => 'Needs SEO staff',
                                };
                            @endphp
                            @if ($order->payment_status === 'paid' && in_array($order->order_status, ['processing', 'completed'], true))
                            <form method="POST" action="{{ route('admin.orders.assign', [$order, $item]) }}" class="mt-4 grid gap-3 rounded-xl border border-navy/10 bg-canvas/40 p-4 lg:grid-cols-[minmax(0,1fr)_16rem_auto] lg:items-end">
                                @csrf
                                <div class="min-w-0">
                                    <p class="font-medium">{{ $item->item_name }}</p>
                                    <p class="mt-1 text-xs text-navy/60">{{ $assignmentLabel }}</p>
                                    <p class="mt-1 break-all text-xs text-navy/55">{{ $item->target_url }}</p>
                                    <p class="mt-1 text-xs text-navy/55">Anchor: {{ $item->anchor_text }}{{ $item->website ? ' · '.$item->website->domain : '' }}</p>
                                </div>
                                <div>
                                    <label for="staff-{{ $item->id }}" class="text-sm font-medium">SEO staff</label>
                                    <select id="staff-{{ $item->id }}" name="staff_id" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30" @disabled($staff->isEmpty())>
                                        @forelse ($staff as $member)
                                            <option value="{{ $member->id }}" @selected($item->assignment?->staff_id === $member->id)>{{ $member->name }}</option>
                                        @empty
                                            <option value="">No active SEO staff</option>
                                        @endforelse
                                    </select>
                                </div>
                                <button type="submit" class="btn-navy" @disabled($staff->isEmpty())>{{ $assignee ? 'Reassign' : 'Assign to SEO staff' }}</button>
                            </form>
                            @endif
                        @empty
                            <p class="mt-4 text-sm text-navy/60">This customer order has no placement yet.</p>
                        @endforelse
                    </article>
                @empty
                    <p class="px-5 py-8 text-sm text-navy/60 sm:px-6">
                        {{ $search !== '' || $orderStatus !== '' || $assignment !== '' ? 'No orders match this search.' : 'No orders have been placed yet.' }}
                    </p>
                @endforelse
            </div>
            @include('admin.partials.pagination', ['records' => $orders])
        </section>
    </div>
@endsection
