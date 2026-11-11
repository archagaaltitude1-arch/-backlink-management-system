@extends('layouts.seo')

@section('title', 'Completed Orders')

@section('content')
@php
    $completedOrders = $completedOrders ?? [
        [
            'report_id' => 'RPT-9041',
            'order_id' => 'BM-20261111-009',
            'customer' => 'Pulse Analytics',
            'customer_email' => 'ops@pulseanalytics.example',
            'service' => 'Niche Edits',
            'website' => 'fintechdigest.co',
            'da' => 66,
            'dr' => 70,
            'target_url' => 'https://pulseanalytics.example/saas-retention-metrics',
            'anchor_text' => 'SaaS retention benchmarks',
            'published_url' => 'https://fintechdigest.co/b2b-revenue/saas-retention-metrics-2026',
            'http_status' => '200 OK',
            'link_rel' => 'Dofollow',
            'customer_status' => 'Customer Approved',
            'notification_status' => 'Email & Portal Notified',
            'credit_status' => 'Credits Confirmed',
            'verified_by' => auth()->user()?->name ?? 'SEO Staff',
            'completed_at' => 'Nov 11, 2026 · 09:15 AM',
        ],
        [
            'report_id' => 'RPT-9038',
            'order_id' => 'BM-20261110-082',
            'customer' => 'Noah Williams',
            'customer_email' => 'noah.williams@example.com',
            'service' => 'Guest Post',
            'website' => 'founderpathdaily.com',
            'da' => 71,
            'dr' => 74,
            'target_url' => 'https://noahwilliams.example/warehouse-tour',
            'anchor_text' => 'warehouse tour',
            'published_url' => 'https://founderpathdaily.com/operations/inside-modern-warehouse-tour',
            'http_status' => '200 OK',
            'link_rel' => 'Dofollow',
            'customer_status' => 'Customer Approved',
            'notification_status' => 'Email & Portal Notified',
            'credit_status' => 'Credits Confirmed',
            'verified_by' => auth()->user()?->name ?? 'SEO Staff',
            'completed_at' => 'Nov 10, 2026 · 03:50 PM',
        ],
        [
            'report_id' => 'RPT-9033',
            'order_id' => 'BM-20261109-064',
            'customer' => 'Brightline Security',
            'customer_email' => 'seo@brightlinesec.example',
            'service' => 'Press Release',
            'website' => 'cyberwirebulletin.org',
            'da' => 78,
            'dr' => 81,
            'target_url' => 'https://brightlinesec.example/zero-trust-audit',
            'anchor_text' => 'zero trust audit checklist',
            'published_url' => 'https://cyberwirebulletin.org/releases/brightline-zero-trust-audit-2026',
            'http_status' => '200 OK',
            'link_rel' => 'Dofollow',
            'customer_status' => 'Customer Approved',
            'notification_status' => 'Email & Portal Notified',
            'credit_status' => 'Credits Confirmed',
            'verified_by' => auth()->user()?->name ?? 'SEO Staff',
            'completed_at' => 'Nov 09, 2026 · 05:20 PM',
        ],
        [
            'report_id' => 'RPT-9029',
            'order_id' => 'BM-20261108-051',
            'customer' => 'Kestrel Commerce',
            'customer_email' => 'growth@kestrelcommerce.example',
            'service' => 'Link Insertion',
            'website' => 'retailtechreview.io',
            'da' => 62,
            'dr' => 65,
            'target_url' => 'https://kestrelcommerce.example/checkout-speed',
            'anchor_text' => 'headless checkout speed',
            'published_url' => 'https://retailtechreview.io/guides/ecommerce-conversion-checkout-speed',
            'http_status' => '200 OK',
            'link_rel' => 'Dofollow',
            'customer_status' => 'Report Delivered',
            'notification_status' => 'Email & Portal Notified',
            'credit_status' => 'Credits Confirmed',
            'verified_by' => auth()->user()?->name ?? 'SEO Staff',
            'completed_at' => 'Nov 08, 2026 · 11:10 AM',
        ],
    ];
@endphp

<div class="space-y-6" id="completed-orders-workspace">
    <!-- Header Banner -->
    <div class="panel p-6 sm:p-8 border-l-4 border-l-brand">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Order History & Verification Reports</p>
                <h1 class="mt-1 text-2xl font-bold text-navy">Completed Orders</h1>
                <p class="mt-1.5 max-w-2xl text-sm text-navy/70">
                    Access verified live backlink placements, inspect generated verification reports, and review customer approval & notification confirmations.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 px-3 py-1.5 text-xs font-semibold">
                    {{ count($completedOrders) }} Verified Orders
                </span>
                <button type="button" id="export-completed-csv" class="btn-navy text-xs px-4 py-2 cursor-pointer">
                    Export Report CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Action Feedback Banner -->
    <div id="completed-action-toast" class="hidden rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200 flex items-center justify-between">
        <span id="completed-action-toast-text">Action completed.</span>
        <button type="button" id="completed-toast-close" class="text-xs font-semibold text-emerald-700 hover:underline">Dismiss</button>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="panel p-5 border-t-4 border-t-emerald-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Completed Orders</p>
            <p class="mt-1 font-display text-3xl font-bold text-emerald-600">{{ count($completedOrders) }}</p>
            <p class="mt-2 text-xs text-navy/55">Fulfilled & verified</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-brand">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Verified Dofollow</p>
            <p class="mt-1 font-display text-3xl font-bold text-navy">{{ collect($completedOrders)->where('link_rel', 'Dofollow')->count() }}</p>
            <p class="mt-2 text-xs text-navy/55">Active HTTP 200 OK links</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-sky-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Reports Generated</p>
            <p class="mt-1 font-display text-3xl font-bold text-sky-600">{{ count($completedOrders) }}</p>
            <p class="mt-2 text-xs text-navy/55">Backlink audit records</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-purple-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Customer Approved</p>
            <p class="mt-1 font-display text-3xl font-bold text-purple-600">{{ collect($completedOrders)->where('customer_status', 'Customer Approved')->count() }}</p>
            <p class="mt-2 text-xs text-navy/55">Notifications & credits settled</p>
        </div>
    </div>

    <!-- Service Filter & Search -->
    <div class="panel p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex flex-wrap items-center gap-1.5" id="completed-filter-tabs">
            @foreach (['all' => 'All Services', 'Guest Post' => 'Guest Post', 'Niche Edits' => 'Niche Edits', 'Link Insertion' => 'Link Insertion', 'Press Release' => 'Press Release'] as $key => $label)
                <button
                    type="button"
                    data-service="{{ $key }}"
                    @class([
                        'completed-tab rounded-xl px-3.5 py-1.5 text-xs font-medium transition cursor-pointer',
                        'bg-brand text-white shadow-sm' => $key === 'all',
                        'text-navy/70 hover:bg-canvas hover:text-navy' => $key !== 'all',
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="relative w-full sm:w-72">
            <input
                type="text"
                id="completed-search-input"
                placeholder="Search order, report ID, client, domain..."
                class="block w-full rounded-xl border border-navy/20 bg-white py-2 pl-9 pr-3 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
            >
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-3.5 w-3.5 text-navy/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Completed Orders Table -->
    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-navy/10 bg-canvas/50 text-[11px] font-semibold text-navy/60 uppercase tracking-wider">
                        <th class="py-3 px-4">Order & Report</th>
                        <th class="py-3 px-4">Customer & Service</th>
                        <th class="py-3 px-4">Publisher Website</th>
                        <th class="py-3 px-4">Live URL & Anchor</th>
                        <th class="py-3 px-4">Verification & Confirmation</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-navy/10" id="completed-table-body">
                    @foreach ($completedOrders as $order)
                        <tr
                            class="completed-row hover:bg-canvas/40 transition align-top"
                            data-service="{{ $order['service'] }}"
                            data-search="{{ strtolower($order['report_id'].' '.$order['order_id'].' '.$order['customer'].' '.$order['website'].' '.$order['anchor_text'].' '.$order['published_url']) }}"
                        >
                            <td class="py-4 px-4">
                                <div class="font-bold text-navy">{{ $order['order_id'] }}</div>
                                <div class="text-xs font-mono text-brand mt-0.5">{{ $order['report_id'] }}</div>
                                <div class="text-[11px] text-navy/50 mt-1">{{ $order['completed_at'] }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-navy">{{ $order['customer'] }}</div>
                                <div class="text-xs text-navy/55">{{ $order['customer_email'] }}</div>
                                <span class="inline-block mt-1.5 badge bg-canvas text-navy/75 text-[11px]">{{ $order['service'] }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-navy">{{ $order['website'] }}</div>
                                <div class="flex items-center gap-1.5 mt-1 text-[11px]">
                                    <span class="rounded bg-navy/5 px-1.5 py-0.5 text-navy font-mono font-medium">DA {{ $order['da'] }}</span>
                                    <span class="rounded bg-brand/10 px-1.5 py-0.5 text-brand font-mono font-medium">DR {{ $order['dr'] }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 max-w-xs">
                                <a
                                    href="{{ $order['published_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="font-medium text-brand hover:underline text-xs break-all block"
                                >
                                    {{ $order['published_url'] }}
                                </a>
                                <div class="mt-1.5 text-xs text-navy/70">
                                    <span class="text-navy/45">Anchor:</span>
                                    <span class="font-semibold text-navy">{{ $order['anchor_text'] }}</span>
                                </div>
                                <div class="mt-0.5 text-[11px] text-navy/55 break-all">
                                    Target: {{ $order['target_url'] }}
                                </div>
                            </td>
                            <td class="py-4 px-4 space-y-1.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-[11px]">
                                        {{ $order['http_status'] }} · {{ $order['link_rel'] }}
                                    </span>
                                    <span class="badge bg-purple-50 text-purple-700 ring-1 ring-purple-200 text-[11px]">
                                        {{ $order['customer_status'] }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-navy/60">
                                    {{ $order['notification_status'] }} · {{ $order['credit_status'] }}
                                </div>
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap space-y-2">
                                <button
                                    type="button"
                                    class="view-report-btn btn-primary text-xs px-3 py-1.5 cursor-pointer"
                                    data-report='@json($order)'
                                >
                                    View Report
                                </button>
                                <div>
                                    <button
                                        type="button"
                                        class="copy-live-url-btn text-xs font-semibold text-navy/70 hover:text-brand cursor-pointer"
                                        data-url="{{ $order['published_url'] }}"
                                    >
                                        Copy Live URL
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="completed-empty-state" class="hidden p-10 text-center">
            <p class="text-sm font-semibold text-navy">No completed orders match your filter.</p>
            <p class="mt-1 text-xs text-navy/60">Adjust the service tab or search terms.</p>
        </div>
    </div>
</div>

<!-- Verification Report Modal -->
<div id="verification-report-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/60 p-4">
    <div class="panel w-full max-w-2xl overflow-hidden shadow-xl">
        <div class="flex items-start justify-between gap-4 border-b border-navy/10 bg-navy px-6 py-5 text-white">
            <div>
                <p id="report-modal-id" class="text-xs font-semibold uppercase tracking-wider text-brand"></p>
                <h2 class="mt-1 text-lg font-bold text-white">Backlink Verification Report</h2>
            </div>
            <button type="button" id="report-modal-close" class="rounded-lg p-1.5 text-white/70 hover:bg-white/10 hover:text-white cursor-pointer">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 rounded-xl bg-canvas/60 p-4 border border-navy/10">
                <div>
                    <p class="font-semibold uppercase tracking-wider text-navy/50">Order ID</p>
                    <p id="report-order-id" class="mt-0.5 text-sm font-bold text-navy"></p>
                </div>
                <div>
                    <p class="font-semibold uppercase tracking-wider text-navy/50">Customer</p>
                    <p id="report-customer" class="mt-0.5 text-sm font-semibold text-navy"></p>
                </div>
                <div>
                    <p class="font-semibold uppercase tracking-wider text-navy/50">Publisher Website</p>
                    <p id="report-website" class="mt-0.5 text-sm font-semibold text-navy"></p>
                </div>
                <div>
                    <p class="font-semibold uppercase tracking-wider text-navy/50">Verified By & Date</p>
                    <p id="report-verified" class="mt-0.5 text-sm font-semibold text-navy"></p>
                </div>
            </div>

            <div class="space-y-2 rounded-xl border border-navy/10 p-4">
                <div><span class="font-semibold text-navy">Live Published URL:</span> <span id="report-live-url" class="text-brand break-all"></span></div>
                <div><span class="font-semibold text-navy">Target URL:</span> <span id="report-target-url" class="text-navy/80 break-all"></span></div>
                <div><span class="font-semibold text-navy">Anchor Text:</span> <span id="report-anchor" class="font-semibold text-navy"></span></div>
                <div class="pt-2 flex flex-wrap gap-2">
                    <span id="report-http" class="badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200"></span>
                    <span id="report-approval" class="badge bg-purple-50 text-purple-700 ring-1 ring-purple-200"></span>
                    <span id="report-credit" class="badge bg-sky-50 text-sky-700 ring-1 ring-sky-200"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="window.print()" class="btn-primary text-xs px-4 py-2 cursor-pointer">
                    Print Report
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let activeService = 'all';
    const tabs = document.querySelectorAll('.completed-tab');
    const rows = Array.from(document.querySelectorAll('.completed-row'));
    const searchInput = document.getElementById('completed-search-input');
    const emptyState = document.getElementById('completed-empty-state');
    const toast = document.getElementById('completed-action-toast');
    const toastText = document.getElementById('completed-action-toast-text');

    const applyFilters = () => {
        const query = (searchInput.value || '').trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach((row) => {
            const matchesService = activeService === 'all' || row.dataset.service === activeService;
            const matchesQuery = query === '' || (row.dataset.search || '').includes(query);
            const show = matchesService && matchesQuery;
            row.classList.toggle('hidden', !show);
            if (show) visibleCount++;
        });

        emptyState.classList.toggle('hidden', visibleCount > 0);
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activeService = tab.dataset.service;
            tabs.forEach((t) => {
                const isActive = t === tab;
                t.classList.toggle('bg-brand', isActive);
                t.classList.toggle('text-white', isActive);
                t.classList.toggle('shadow-sm', isActive);
                t.classList.toggle('text-navy/70', !isActive);
                t.classList.toggle('hover:bg-canvas', !isActive);
            });
            applyFilters();
        });
    });

    searchInput?.addEventListener('input', applyFilters);

    document.querySelectorAll('.copy-live-url-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.url;
            if (navigator.clipboard) {
                await navigator.clipboard.writeText(url);
            }
            toastText.textContent = `Copied live URL: ${url}`;
            toast.classList.remove('hidden');
        });
    });

    document.getElementById('export-completed-csv')?.addEventListener('click', () => {
        toastText.textContent = 'Completed orders CSV export ready (UI preview — backend integration pending).';
        toast.classList.remove('hidden');
    });

    document.getElementById('completed-toast-close')?.addEventListener('click', () => {
        toast.classList.add('hidden');
    });

    const modal = document.getElementById('verification-report-modal');
    document.querySelectorAll('.view-report-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const report = JSON.parse(btn.dataset.report);
            document.getElementById('report-modal-id').textContent = `${report.report_id} · ${report.service}`;
            document.getElementById('report-order-id').textContent = report.order_id;
            document.getElementById('report-customer').textContent = report.customer;
            document.getElementById('report-website').textContent = `${report.website} (DA ${report.da} / DR ${report.dr})`;
            document.getElementById('report-verified').textContent = `${report.verified_by} · ${report.completed_at}`;
            document.getElementById('report-live-url').textContent = report.published_url;
            document.getElementById('report-target-url').textContent = report.target_url;
            document.getElementById('report-anchor').textContent = `"${report.anchor_text}"`;
            document.getElementById('report-http').textContent = `${report.http_status} · ${report.link_rel}`;
            document.getElementById('report-approval').textContent = report.customer_status;
            document.getElementById('report-credit').textContent = report.credit_status;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    document.getElementById('report-modal-close')?.addEventListener('click', () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
});
</script>
@endpush
