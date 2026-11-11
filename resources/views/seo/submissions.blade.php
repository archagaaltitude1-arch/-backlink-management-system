@extends('layouts.seo')

@section('title', 'Submissions')

@section('content')
@php
    $submissions = $submissions ?? [
        [
            'id' => 'PUB-2081',
            'order_id' => 'BM-20261111-014',
            'customer' => 'Noah Williams',
            'service' => 'Guest Post',
            'article_title' => 'Enterprise AI Adoption Trends for SaaS Marketing Teams in 2026',
            'website' => 'techpulsejournal.com',
            'da' => 68,
            'dr' => 72,
            'target_url' => 'https://noahwilliams.example/ai-marketing-suite',
            'anchor_text' => 'enterprise AI marketing',
            'published_url' => '',
            'link_type' => 'Dofollow',
            'status' => 'ready_to_publish',
            'status_label' => 'Ready to Publish',
            'http_check' => 'Pending',
            'target_match' => false,
            'anchor_match' => false,
            'last_checked' => 'Not published yet',
        ],
        [
            'id' => 'PUB-2079',
            'order_id' => 'BM-20261111-023',
            'customer' => 'Vertex Cloud Labs',
            'service' => 'Guest Post',
            'article_title' => 'Cloud FinOps Playbook: Cutting Kubernetes Idle Spend by 32%',
            'website' => 'devopsinsider.net',
            'da' => 74,
            'dr' => 77,
            'target_url' => 'https://vertexcloud.example/kubernetes-cost-control',
            'anchor_text' => 'Kubernetes cost optimization',
            'published_url' => 'https://devopsinsider.net/cloud-finops-kubernetes-idle-spend',
            'link_type' => 'Dofollow',
            'status' => 'pending_verification',
            'status_label' => 'Pending Verification',
            'http_check' => '200 OK',
            'target_match' => true,
            'anchor_match' => true,
            'last_checked' => 'Nov 11, 2026 · 10:12 AM',
        ],
        [
            'id' => 'PUB-2076',
            'order_id' => 'BM-20261111-009',
            'customer' => 'Pulse Analytics',
            'service' => 'Niche Edits',
            'article_title' => 'Customer Retention Metrics Every SaaS CFO Should Track',
            'website' => 'fintechdigest.co',
            'da' => 66,
            'dr' => 70,
            'target_url' => 'https://pulseanalytics.example/saas-retention-metrics',
            'anchor_text' => 'SaaS retention benchmarks',
            'published_url' => 'https://fintechdigest.co/b2b-revenue/saas-retention-metrics-2026',
            'link_type' => 'Dofollow',
            'status' => 'verified_live',
            'status_label' => 'Verified Live',
            'http_check' => '200 OK',
            'target_match' => true,
            'anchor_match' => true,
            'last_checked' => 'Nov 11, 2026 · 09:05 AM',
        ],
        [
            'id' => 'PUB-2072',
            'order_id' => 'BM-20261111-005',
            'customer' => 'Cedar Logistics',
            'service' => 'Link Insertion',
            'article_title' => 'Regional Freight Visibility & Warehouse Automation Guide',
            'website' => 'supplychainbrief.com',
            'da' => 57,
            'dr' => 61,
            'target_url' => 'https://cedarlogistics.example/freight-tracking',
            'anchor_text' => 'freight visibility software',
            'published_url' => 'https://supplychainbrief.com/warehouse-automation-2026',
            'link_type' => 'Nofollow',
            'status' => 'issue_flagged',
            'status_label' => 'Issue Flagged',
            'http_check' => '200 OK',
            'target_match' => true,
            'anchor_match' => false,
            'last_checked' => 'Nov 10, 2026 · 06:45 PM',
        ],
    ];

    $stats = [
        'total' => count($submissions),
        'ready' => collect($submissions)->where('status', 'ready_to_publish')->count(),
        'pending' => collect($submissions)->where('status', 'pending_verification')->count(),
        'verified' => collect($submissions)->where('status', 'verified_live')->count(),
    ];
@endphp

<div class="space-y-6" id="submissions-workspace">
    <!-- Header Banner -->
    <div class="panel p-6 sm:p-8 border-l-4 border-l-brand">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Publishing & Backlink Verification</p>
                <h1 class="mt-1 text-2xl font-bold text-navy">Submissions</h1>
                <p class="mt-1.5 max-w-2xl text-sm text-navy/70">
                    Submit live published article URLs, run backlink verification for target URL & anchor text matches, and confirm placements for reporting.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="badge bg-navy text-white px-3 py-1.5 text-xs font-medium">
                    Active: {{ $stats['total'] }} Submissions
                </span>
                <a href="{{ route('seo.completed-orders') }}" class="btn-navy text-xs px-4 py-2">
                    View Completed Orders &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Action Feedback Banner -->
    <div id="submission-action-toast" class="hidden rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span id="submission-action-toast-text">Verification updated.</span>
        </div>
        <button type="button" id="submission-toast-close" class="text-xs font-semibold text-emerald-700 hover:underline">Dismiss</button>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="panel p-5 border-t-4 border-t-brand">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Total Submissions</p>
            <p class="mt-1 font-display text-3xl font-bold text-navy">{{ $stats['total'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Publishing & link checks</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-sky-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Ready to Publish</p>
            <p class="mt-1 font-display text-3xl font-bold text-sky-600" data-sub-count="ready_to_publish">{{ $stats['ready'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Awaiting published URL</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-amber-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Pending Verification</p>
            <p class="mt-1 font-display text-3xl font-bold text-amber-600" data-sub-count="pending_verification">{{ $stats['pending'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Needs live link inspection</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-emerald-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Verified Live</p>
            <p class="mt-1 font-display text-3xl font-bold text-emerald-600" data-sub-count="verified_live">{{ $stats['verified'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Confirmed & ready for report</p>
        </div>
    </div>

    <!-- Filter Tabs & Search -->
    <div class="panel p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex flex-wrap items-center gap-1.5" id="submission-filter-tabs">
            @php
                $tabs = [
                    'all' => 'All Submissions',
                    'ready_to_publish' => 'Ready to Publish',
                    'pending_verification' => 'Pending Verification',
                    'verified_live' => 'Verified Live',
                    'issue_flagged' => 'Issue Flagged',
                ];
            @endphp
            @foreach ($tabs as $key => $label)
                <button
                    type="button"
                    data-filter="{{ $key }}"
                    @class([
                        'submission-tab rounded-xl px-3.5 py-1.5 text-xs font-medium transition cursor-pointer',
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
                id="submission-search-input"
                placeholder="Search order, publisher, URL, anchor..."
                class="block w-full rounded-xl border border-navy/20 bg-white py-2 pl-9 pr-3 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
            >
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-3.5 w-3.5 text-navy/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Submissions & Verification List -->
    <div class="space-y-4" id="submission-cards-list">
        @foreach ($submissions as $item)
            @php
                $badgeClasses = match ($item['status']) {
                    'verified_live' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                    'pending_verification' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                    'issue_flagged' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                    default => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                };
            @endphp
            <article
                class="submission-card panel p-5 sm:p-6 transition hover:shadow-md"
                data-sub-id="{{ $item['id'] }}"
                data-status="{{ $item['status'] }}"
                data-search="{{ strtolower($item['id'].' '.$item['order_id'].' '.$item['customer'].' '.$item['website'].' '.$item['anchor_text'].' '.$item['published_url']) }}"
            >
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">
                    <!-- Left: Publication & Link Details -->
                    <div class="flex-1 space-y-4 min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="font-mono text-xs font-bold text-navy bg-navy/5 px-2.5 py-1 rounded-lg">{{ $item['order_id'] }}</span>
                            <span class="text-xs font-medium text-navy/55">{{ $item['id'] }}</span>
                            <span class="submission-status-badge badge {{ $badgeClasses }}">{{ $item['status_label'] }}</span>
                            <span class="badge bg-canvas text-navy/70">{{ $item['service'] }}</span>
                            <span class="text-xs text-navy/50">Last check: <span class="submission-last-checked">{{ $item['last_checked'] }}</span></span>
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-navy">{{ $item['article_title'] }}</h2>
                            <p class="mt-0.5 text-xs text-navy/60">Customer: <strong class="text-navy">{{ $item['customer'] }}</strong></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 rounded-xl bg-canvas/60 p-4 border border-navy/5">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Publisher Website</p>
                                <p class="mt-0.5 text-sm font-semibold text-navy">{{ $item['website'] }}</p>
                                <div class="mt-1 flex items-center gap-2 text-[11px]">
                                    <span class="rounded bg-white px-1.5 py-0.5 text-navy font-mono font-medium ring-1 ring-navy/10">DA {{ $item['da'] }}</span>
                                    <span class="rounded bg-brand/10 px-1.5 py-0.5 text-brand font-mono font-medium">DR {{ $item['dr'] }}</span>
                                    <span class="rounded bg-white px-1.5 py-0.5 text-navy/70 font-medium ring-1 ring-navy/10">{{ $item['link_type'] }}</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Expected Target URL & Anchor</p>
                                <span class="mt-1 inline-block rounded bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand">
                                    "{{ $item['anchor_text'] }}"
                                </span>
                                <a href="{{ $item['target_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-1 block text-xs text-navy/75 hover:text-brand break-all">
                                    {{ $item['target_url'] }}
                                </a>
                            </div>
                        </div>

                        <!-- Verification Checklist Pills -->
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="check-http rounded-lg px-2.5 py-1 font-medium {{ $item['http_check'] === '200 OK' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-canvas text-navy/60' }}">
                                HTTP: {{ $item['http_check'] }}
                            </span>
                            <span class="check-target rounded-lg px-2.5 py-1 font-medium {{ $item['target_match'] ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200' }}">
                                Target URL: {{ $item['target_match'] ? 'Matched' : 'Not Matched' }}
                            </span>
                            <span class="check-anchor rounded-lg px-2.5 py-1 font-medium {{ $item['anchor_match'] ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200' }}">
                                Anchor Text: {{ $item['anchor_match'] ? 'Matched' : 'Mismatch / Pending' }}
                            </span>
                        </div>
                    </div>

                    <!-- Right: Submit Published URL & Verify Backlink -->
                    <div class="w-full xl:w-96 shrink-0 rounded-xl bg-canvas/50 p-4 border border-navy/10 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-navy">Published URL & Verification</h3>

                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-navy/55 mb-1">
                                Published Article URL
                            </label>
                            <input
                                type="url"
                                value="{{ $item['published_url'] }}"
                                placeholder="https://publisher-domain.com/published-post"
                                class="published-url-input block w-full rounded-xl border border-navy/20 bg-white px-3 py-2 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                            >
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <button
                                type="button"
                                class="submit-url-btn btn-navy text-xs py-2 cursor-pointer"
                            >
                                Save Published URL
                            </button>
                            <button
                                type="button"
                                class="verify-live-btn btn-primary text-xs py-2 cursor-pointer"
                            >
                                Verify Backlink
                            </button>
                        </div>

                        <div class="flex items-center justify-between pt-1 border-t border-navy/10">
                            <button
                                type="button"
                                class="flag-issue-btn text-xs font-semibold text-rose-600 hover:underline cursor-pointer"
                            >
                                Flag Link Issue
                            </button>
                            <span class="text-[11px] text-navy/55">Triggers customer update & credit confirmation</span>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach

        <div id="submissions-empty-state" class="hidden panel p-10 text-center">
            <p class="text-sm font-semibold text-navy">No submissions match the selected filter.</p>
            <p class="mt-1 text-xs text-navy/60">Try clearing the search query or switching tabs.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let activeFilter = 'all';
    const tabs = document.querySelectorAll('.submission-tab');
    const cards = Array.from(document.querySelectorAll('.submission-card'));
    const searchInput = document.getElementById('submission-search-input');
    const emptyState = document.getElementById('submissions-empty-state');
    const toast = document.getElementById('submission-action-toast');
    const toastText = document.getElementById('submission-action-toast-text');

    const badgeMap = {
        verified_live: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        pending_verification: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        issue_flagged: 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
        ready_to_publish: 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
    };

    const refreshCounts = () => {
        ['ready_to_publish', 'pending_verification', 'verified_live'].forEach((status) => {
            const el = document.querySelector(`[data-sub-count="${status}"]`);
            if (el) {
                el.textContent = cards.filter((c) => c.dataset.status === status).length;
            }
        });
    };

    const applyFilters = () => {
        const query = (searchInput.value || '').trim().toLowerCase();
        let visibleCount = 0;

        cards.forEach((card) => {
            const matchesTab = activeFilter === 'all' || card.dataset.status === activeFilter;
            const matchesQuery = query === '' || (card.dataset.search || '').includes(query);
            const show = matchesTab && matchesQuery;
            card.classList.toggle('hidden', !show);
            if (show) visibleCount++;
        });

        emptyState.classList.toggle('hidden', visibleCount > 0);
    };

    const setStatus = (card, status, label, message) => {
        card.dataset.status = status;
        const badge = card.querySelector('.submission-status-badge');
        badge.className = `submission-status-badge badge ${badgeMap[status]}`;
        badge.textContent = label;
        card.querySelector('.submission-last-checked').textContent = 'Just now';

        toastText.textContent = message;
        toast.classList.remove('hidden');
        refreshCounts();
        applyFilters();
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activeFilter = tab.dataset.filter;
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

    document.querySelectorAll('.submit-url-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.submission-card');
            const urlInput = card.querySelector('.published-url-input');
            if (!urlInput.value.trim()) {
                urlInput.focus();
                return;
            }
            card.querySelector('.check-http').className = 'check-http rounded-lg px-2.5 py-1 font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
            card.querySelector('.check-http').textContent = 'HTTP: 200 OK';
            setStatus(card, 'pending_verification', 'Pending Verification', `Published URL saved for ${card.dataset.subId} (UI preview — backend integration pending).`);
        });
    });

    document.querySelectorAll('.verify-live-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.submission-card');
            card.querySelector('.check-http').className = 'check-http rounded-lg px-2.5 py-1 font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
            card.querySelector('.check-http').textContent = 'HTTP: 200 OK';
            card.querySelector('.check-target').className = 'check-target rounded-lg px-2.5 py-1 font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
            card.querySelector('.check-target').textContent = 'Target URL: Matched';
            card.querySelector('.check-anchor').className = 'check-anchor rounded-lg px-2.5 py-1 font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
            card.querySelector('.check-anchor').textContent = 'Anchor Text: Matched';
            setStatus(card, 'verified_live', 'Verified Live', `Backlink ${card.dataset.subId} verified live with matching target URL and anchor text (UI preview).`);
        });
    });

    document.querySelectorAll('.flag-issue-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.submission-card');
            card.querySelector('.check-anchor').className = 'check-anchor rounded-lg px-2.5 py-1 font-medium bg-rose-50 text-rose-700 ring-1 ring-rose-200';
            card.querySelector('.check-anchor').textContent = 'Anchor Text: Mismatch / Pending';
            setStatus(card, 'issue_flagged', 'Issue Flagged', `Verification issue flagged on ${card.dataset.subId} (UI preview).`);
        });
    });

    document.getElementById('submission-toast-close')?.addEventListener('click', () => {
        toast.classList.add('hidden');
    });
});
</script>
@endpush
