@extends('layouts.seo')

@section('title', 'SEO Staff Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="panel p-6 sm:p-8 bg-gradient-to-r from-navy via-navy to-navy-soft text-white relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/20 px-3 py-1 text-xs font-semibold text-brand ring-1 ring-brand/30">
                        <span class="h-1.5 w-1.5 rounded-full bg-brand animate-pulse"></span>
                        Fulfillment & Verification
                    </span>
                    <span class="text-xs text-white/60">Live Portal</span>
                </div>
                <h1 class="mt-2 text-2xl sm:text-3xl font-bold tracking-tight text-white">
                    SEO Staff Dashboard
                </h1>
                <p class="mt-1.5 max-w-2xl text-sm text-white/70">
                    Welcome back, <strong class="text-white">{{ $currentStaff }}</strong>. Review your assigned backlink orders, inspect target URLs, anchor texts, and customer requirements, and update your assigned work status.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('seo.assigned-orders') }}" class="btn-primary shadow-lg shadow-brand/20 text-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Assigned Orders
                </a>
                <a href="{{ route('seo.profile') }}" class="btn-inverse text-sm">
                    Staff Profile
                </a>
            </div>
        </div>
        <!-- Decorative subtle background shape -->
        <div class="absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-brand/10 blur-3xl pointer-events-none"></div>
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

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="panel p-5 relative overflow-hidden border-t-4 border-t-brand">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Total Assigned</p>
                    <p class="mt-1 font-display text-3xl font-bold text-navy">{{ $stats['total_assigned'] }}</p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-brand/10 text-brand">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-navy/55">Orders assigned to you</p>
        </div>

        <div class="panel p-5 relative overflow-hidden border-t-4 border-t-sky-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">In Progress</p>
                    <p class="mt-1 font-display text-3xl font-bold text-sky-600">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-sky-50 text-sky-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-navy/55">Content placement & outreach</p>
        </div>

        <div class="panel p-5 relative overflow-hidden border-t-4 border-t-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Pending Review</p>
                    <p class="mt-1 font-display text-3xl font-bold text-amber-600">{{ $stats['pending_review'] }}</p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-navy/55">Awaiting fulfillment start</p>
        </div>

        <div class="panel p-5 relative overflow-hidden border-t-4 border-t-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Verified Live</p>
                    <p class="mt-1 font-display text-3xl font-bold text-emerald-600">{{ $stats['completed'] }}</p>
                </div>
                <div class="grid h-12 w-12 place-items-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-navy/55">Backlinks confirmed & completed</p>
        </div>
    </div>

    <!-- Assigned Orders Section -->
    <div class="panel overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-5 border-b border-navy/10 bg-canvas/30">
            <div>
                <h2 class="text-base font-bold text-navy">Assigned Orders</h2>
                <p class="text-xs text-navy/60 mt-0.5">Orders assigned to you with target URLs, anchor texts, and customer requirements</p>
            </div>
            <a href="{{ route('seo.assigned-orders') }}" class="text-xs font-semibold text-brand hover:underline">
                View Detailed Order List &rarr;
            </a>
        </div>

        @if (count($orders) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-navy/10 bg-canvas/50 text-[11px] font-semibold text-navy/60 uppercase tracking-wider">
                            <th class="py-3 px-4">Order / Customer</th>
                            <th class="py-3 px-4">Publisher Website</th>
                            <th class="py-3 px-4">Target URL & Anchor Text</th>
                            <th class="py-3 px-4">Customer Requirements</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Update Work Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-canvas/40 transition align-top">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-navy">{{ $order['id'] }}</div>
                                    <div class="text-xs text-navy/70">{{ $order['customer'] }}</div>
                                    <span class="inline-block mt-1 text-[11px] font-medium text-navy/50">{{ $order['service'] }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-navy">{{ $order['website'] }}</div>
                                    <div class="flex items-center gap-2 mt-1 text-[11px]">
                                        <span class="rounded bg-navy/5 px-1.5 py-0.5 text-navy font-mono font-medium">DA {{ $order['domain_authority'] }}</span>
                                        <span class="rounded bg-brand/10 px-1.5 py-0.5 text-brand font-mono font-medium">DR {{ $order['domain_rating'] }}</span>
                                    </div>
                                    <div class="text-[11px] text-navy/50 mt-1">{{ $order['niche'] }}</div>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <a
                                        href="{{ $order['target_url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="font-medium text-navy hover:text-brand text-xs break-all block"
                                        title="{{ $order['target_url'] }}"
                                    >
                                        {{ $order['target_url'] }}
                                    </a>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-navy/70">
                                        <span class="text-navy/40">Anchor:</span>
                                        <span class="font-semibold text-brand bg-brand/5 px-1.5 py-0.5 rounded">{{ $order['anchor_text'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 max-w-sm">
                                    <div class="text-xs text-navy">
                                        <span class="font-semibold">Topic:</span> {{ $order['article_topic'] }}
                                    </div>
                                    <div class="mt-1 text-xs text-navy/70">
                                        <span class="font-semibold text-navy/80">Requirements:</span>
                                        {{ $order['requirements'] ?: 'No specific customer requirements provided.' }}
                                    </div>
                                    @if (! empty($order['notes']))
                                        <div class="mt-1 text-[11px] text-navy/55 italic">
                                            Notes: {{ $order['notes'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeClass = match ($order['status']) {
                                            'in_progress' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                                            'assigned' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                                            'published' => 'bg-purple-50 text-purple-700 ring-1 ring-purple-200',
                                            'completed' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                            default => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} text-[11px]">
                                        {{ $order['status_label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <form action="{{ route('seo.orders.status', ['id' => $order['assignment_id'] ?? $order['id']]) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="assignment_id" value="{{ $order['assignment_id'] }}">
                                        <input type="hidden" name="order_item_id" value="{{ $order['order_item_id'] }}">
                                        <select name="status" aria-label="Work status for {{ $order['id'] }}" class="rounded-lg border border-navy/20 bg-white px-2.5 py-1.5 text-xs text-navy focus:border-brand focus:outline-none">
                                            <option value="assigned" @selected($order['status'] === 'assigned')>Assigned</option>
                                            <option value="in_progress" @selected($order['status'] === 'in_progress')>In Progress</option>
                                            <option value="published" @selected($order['status'] === 'published')>Published</option>
                                            <option value="completed" @selected($order['status'] === 'completed')>Completed</option>
                                        </select>
                                        <button type="submit" class="btn-primary text-xs px-3 py-1.5">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-canvas text-navy/40">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-navy">No Assigned Orders Yet</h3>
                <p class="mt-1 text-xs text-navy/60 max-w-md mx-auto">
                    Orders assigned to your account will appear here with their target URLs, anchor texts, and customer requirements.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
