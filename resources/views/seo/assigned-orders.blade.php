@extends('layouts.seo')

@section('title', 'Manage Assigned Staff Orders')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="panel p-6 sm:p-8 border-l-4 border-l-brand">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Member 4 · Staff Management & Fulfillment</p>
                <h1 class="mt-1 text-2xl font-bold text-navy">Assigned Orders Management</h1>
                <p class="mt-1.5 text-sm text-navy/70">
                    Review target URLs, anchor texts, customer requirements, update order status, and assign tasks to SEO staff.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge bg-navy text-white px-3 py-1.5 text-xs font-medium">
                    Total: {{ count($orders) }} Orders
                </span>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Controls: Status Filter Tabs & Search -->
    <div class="panel p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center gap-1.5">
            @php
                $tabs = [
                    'all' => 'All Orders',
                    'assigned' => 'Assigned',
                    'in_progress' => 'In Progress',
                    'published' => 'Published / Ready',
                    'completed' => 'Completed',
                    'unassigned' => 'Unassigned',
                ];
            @endphp
            @foreach ($tabs as $key => $label)
                <a
                    href="{{ route('seo.assigned-orders', ['status' => $key, 'search' => $searchTerm]) }}"
                    @class([
                        'rounded-xl px-3.5 py-1.5 text-xs font-medium transition',
                        'bg-brand text-white shadow-sm' => $currentFilter === $key,
                        'text-navy/70 hover:bg-canvas hover:text-navy' => $currentFilter !== $key,
                    ])
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('seo.assigned-orders') }}" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $currentFilter }}">
            <div class="relative w-full sm:w-64">
                <input
                    type="text"
                    name="search"
                    value="{{ $searchTerm }}"
                    placeholder="Search by order, domain, anchor..."
                    class="block w-full rounded-xl border border-navy/20 bg-white py-2 pl-9 pr-3 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                >
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-3.5 w-3.5 text-navy/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <button type="submit" class="btn-navy text-xs px-3.5 py-2">
                Search
            </button>
            @if ($searchTerm)
                <a href="{{ route('seo.assigned-orders', ['status' => $currentFilter]) }}" class="text-xs text-navy/50 hover:text-navy">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Orders Detailed Cards Grid / List -->
    <div class="space-y-4">
        @forelse ($orders as $order)
            <div class="panel p-5 sm:p-6 transition hover:shadow-md border border-navy/10">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <!-- Left: Main Order Metadata -->
                    <div class="flex-1 space-y-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="font-display text-base font-bold text-navy">{{ $order['id'] }}</span>
                            <span class="badge bg-navy/5 text-navy font-semibold text-xs">{{ $order['service'] }}</span>
                            
                            @php
                                $badgeClass = match ($order['status']) {
                                    'in_progress' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                                    'assigned' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                    'published' => 'bg-purple-50 text-purple-700 ring-1 ring-purple-200',
                                    'completed' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                    default => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} text-xs">
                                {{ $order['status_label'] }}
                            </span>

                            <span class="text-xs text-navy/50 ml-auto sm:ml-0">
                                Due: <strong class="text-navy">{{ $order['due_date'] }}</strong>
                            </span>
                        </div>

                        <!-- Target details banner -->
                        <div class="rounded-xl bg-canvas/60 p-4 border border-navy/5 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="text-navy/50 block font-semibold uppercase tracking-wider text-[10px]">Publisher Website</span>
                                <div class="mt-1 font-bold text-sm text-navy flex items-center gap-2">
                                    {{ $order['website'] }}
                                    <span class="rounded bg-brand/10 px-1.5 py-0.5 text-brand font-mono font-medium text-xs">DR {{ $order['domain_rating'] }}</span>
                                    <span class="rounded bg-navy/10 px-1.5 py-0.5 text-navy font-mono font-medium text-xs">DA {{ $order['domain_authority'] }}</span>
                                </div>
                                <span class="text-[11px] text-navy/60 block mt-0.5">Niche: {{ $order['niche'] }} · Traffic: {{ $order['traffic'] }}</span>
                            </div>

                            <div>
                                <span class="text-navy/50 block font-semibold uppercase tracking-wider text-[10px]">Anchor Text & Target URL</span>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="font-bold text-brand bg-white px-2 py-0.5 rounded ring-1 ring-navy/10 text-xs">
                                        "{{ $order['anchor_text'] }}"
                                    </span>
                                </div>
                                <a
                                    href="{{ $order['target_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-1 text-xs text-navy/70 hover:text-brand flex items-center gap-1 font-mono truncate"
                                    title="{{ $order['target_url'] }}"
                                >
                                    <span>{{ $order['target_url'] }}</span>
                                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Article Topic & Notes -->
                        <div class="text-xs text-navy/70 space-y-1">
                            <p><strong class="text-navy">Article Topic:</strong> {{ $order['article_topic'] }}</p>
                            @if ($order['notes'])
                                <p><strong class="text-navy">Staff Notes:</strong> <span class="italic text-navy/80">{{ $order['notes'] }}</span></p>
                            @endif
                            <p class="text-[11px] text-navy/50 pt-1">
                                Customer: <strong class="text-navy">{{ $order['customer'] }}</strong> ({{ $order['customer_email'] }}) ·
                                Assigned Staff: <strong class="text-brand">{{ $order['assigned_staff'] }}</strong> (via {{ $order['assigned_by'] ?? 'Unassigned' }})
                            </p>
                        </div>
                    </div>

                    <!-- Right: Staff Management Actions (Order Assignment & Status update) -->
                    <div class="lg:w-80 shrink-0 space-y-3 pt-3 lg:pt-0 lg:border-l lg:border-navy/10 lg:pl-6">
                        <!-- Quick Re-Assign form -->
                        <div class="rounded-xl bg-canvas/40 p-3.5 border border-navy/10">
                            <p class="text-xs font-bold text-navy mb-2">Assign / Reassign Staff</p>
                            <form action="{{ route('seo.orders.assign', ['id' => $order['id']]) }}" method="POST" class="space-y-2">
                                @csrf
                                <input type="hidden" name="order_item_id" value="{{ $order['order_item_id'] }}">
                                <select name="staff_id" class="block w-full rounded-lg border border-navy/20 bg-white px-2.5 py-1.5 text-xs text-navy focus:border-brand focus:outline-none">
                                    @foreach ($staffList as $staff)
                                        <option value="{{ $staff['id'] }}" @selected($order['assigned_staff_id'] == $staff['id'])>
                                            {{ $staff['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-navy w-full text-xs py-1.5">
                                    Update Assignment
                                </button>
                            </form>
                        </div>

                        <!-- Update Status Form -->
                        <div class="rounded-xl bg-canvas/40 p-3.5 border border-navy/10">
                            <p class="text-xs font-bold text-navy mb-2">Update Workflow Status</p>
                            <form action="{{ route('seo.orders.status', ['id' => $order['id']]) }}" method="POST" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="order_item_id" value="{{ $order['order_item_id'] }}">
                                <select name="status" class="block w-full rounded-lg border border-navy/20 bg-white px-2.5 py-1.5 text-xs text-navy focus:border-brand focus:outline-none">
                                    <option value="assigned" @selected($order['status'] === 'assigned')>Assigned</option>
                                    <option value="in_progress" @selected($order['status'] === 'in_progress')>In Progress</option>
                                    <option value="published" @selected($order['status'] === 'published')>Published / Ready for Verification</option>
                                    <option value="completed" @selected($order['status'] === 'completed')>Completed</option>
                                </select>
                                <button type="submit" class="btn-primary w-full text-xs py-1.5">
                                    Save Status
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="panel p-12 text-center">
                <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-canvas text-navy/40">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-navy">No Orders Found</h3>
                <p class="mt-1 text-xs text-navy/60">No orders match the current status filter or search query.</p>
                <a href="{{ route('seo.assigned-orders') }}" class="btn-primary mt-4 inline-flex text-xs">
                    Reset Filter
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
