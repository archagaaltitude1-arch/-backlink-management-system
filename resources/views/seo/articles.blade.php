@extends('layouts.seo')

@section('title', 'Articles')

@section('content')
@php
    $articles = $articles ?? [
        [
            'id' => 'ART-1042',
            'order_id' => 'BM-20261111-014',
            'title' => 'Enterprise AI Adoption Trends for SaaS Marketing Teams in 2026',
            'customer' => 'Noah Williams',
            'source' => 'Customer Submitted',
            'website' => 'techpulsejournal.com',
            'da' => 68,
            'dr' => 72,
            'word_count' => 1250,
            'target_url' => 'https://noahwilliams.example/ai-marketing-suite',
            'anchor_text' => 'enterprise AI marketing',
            'requirements' => 'Place the anchor naturally in the second H2 section and avoid promotional fluff.',
            'status' => 'pending_review',
            'status_label' => 'Pending Review',
            'submitted_at' => 'Nov 11, 2026 · 09:40 AM',
            'feedback' => '',
            'excerpt' => 'Modern B2B marketing teams are moving beyond simple prompt wrappers toward governed AI workflows that connect CRM attribution directly to organic search demand...',
            'body' => [
                'Modern B2B marketing teams are moving beyond simple prompt wrappers toward governed AI workflows that connect CRM attribution directly to organic search demand.',
                'Teams implementing enterprise AI marketing platforms report faster campaign iteration when editorial guidelines, brand terminology, and target landing pages are standardized before outreach begins.',
                'To sustain organic visibility, editors should pair original benchmark data with contextual citations rather than relying on generic listicles.',
            ],
        ],
        [
            'id' => 'ART-1041',
            'order_id' => 'BM-20261111-019',
            'title' => 'How Sustainable Packaging Reduces Last-Mile E-Commerce Waste',
            'customer' => 'Morgan Digital',
            'source' => 'Customer Submitted',
            'website' => 'greencommerceweekly.io',
            'da' => 59,
            'dr' => 63,
            'word_count' => 980,
            'target_url' => 'https://morgandigital.example/eco-packaging',
            'anchor_text' => 'sustainable ecommerce packaging',
            'requirements' => 'Use UK/US neutral spelling and include one bullet list of fulfillment tips.',
            'status' => 'corrections_requested',
            'status_label' => 'Corrections Requested',
            'submitted_at' => 'Nov 10, 2026 · 04:15 PM',
            'feedback' => 'Anchor text currently links to homepage instead of /eco-packaging, and introduction needs 150 more words.',
            'excerpt' => 'Direct-to-consumer brands face rising shipping costs and stricter retail sustainability rules across North America and Europe...',
            'body' => [
                'Direct-to-consumer brands face rising shipping costs and stricter retail sustainability rules across North America and Europe.',
                'Switching to right-sized recyclable mailers helps warehouses cut dimensional weight fees while improving unboxing perception.',
                'Partners evaluating sustainable ecommerce packaging should audit void-fill material ratios before Q1 carrier rate updates.',
            ],
        ],
        [
            'id' => 'ART-1039',
            'order_id' => 'BM-20261111-023',
            'title' => 'Cloud FinOps Playbook: Cutting Kubernetes Idle Spend by 32%',
            'customer' => 'Vertex Cloud Labs',
            'source' => 'Staff Prepared',
            'website' => 'devopsinsider.net',
            'da' => 74,
            'dr' => 77,
            'word_count' => 1420,
            'target_url' => 'https://vertexcloud.example/kubernetes-cost-control',
            'anchor_text' => 'Kubernetes cost optimization',
            'requirements' => 'Technical tone for engineering leads; dofollow contextual placement inside body paragraph.',
            'status' => 'approved',
            'status_label' => 'Approved',
            'submitted_at' => 'Nov 09, 2026 · 02:05 PM',
            'feedback' => 'Reviewed target URL, anchor placement, and formatting. Ready for publisher scheduling.',
            'excerpt' => 'Over-provisioned node pools quietly drain cloud budgets when autoscaling policies are left on staging defaults...',
            'body' => [
                'Over-provisioned node pools quietly drain cloud budgets when autoscaling policies are left on staging defaults.',
                'Engineering leads who prioritize Kubernetes cost optimization combine spot-instance fallbacks with weekly namespace right-sizing reviews.',
                'Clear cost ownership tags turn monthly cloud invoices into actionable sprint metrics.',
            ],
        ],
        [
            'id' => 'ART-1037',
            'order_id' => 'BM-20261111-027',
            'title' => 'Payroll Automation Benchmarks for Distributed Remote Teams',
            'customer' => 'Northstar HR',
            'source' => 'Customer Submitted',
            'website' => 'workplaceledger.com',
            'da' => 64,
            'dr' => 68,
            'word_count' => 1120,
            'target_url' => 'https://northstarhr.example/global-payroll',
            'anchor_text' => 'global payroll software',
            'requirements' => 'Exclude competitor mentions and verify anchor appears once above the fold.',
            'status' => 'pending_review',
            'status_label' => 'Pending Review',
            'submitted_at' => 'Nov 11, 2026 · 08:10 AM',
            'feedback' => '',
            'excerpt' => 'Managing multi-currency contractor payouts alongside full-time statutory filings requires tighter treasury coordination...',
            'body' => [
                'Managing multi-currency contractor payouts alongside full-time statutory filings requires tighter treasury coordination.',
                'Modern finance teams adopt global payroll software to consolidate tax withholding rules and cut monthly reconciliation cycles in half.',
                'Standardizing approval workflows early prevents compliance bottlenecks as headcount expands into new regions.',
            ],
        ],
        [
            'id' => 'ART-1034',
            'order_id' => 'BM-20261111-031',
            'title' => 'B2B Link Building Outreach Pitfalls That Trigger Editorial Rejections',
            'customer' => 'Harbor SaaS',
            'source' => 'Customer Submitted',
            'website' => 'searchmetricsdaily.org',
            'da' => 61,
            'dr' => 65,
            'word_count' => 640,
            'target_url' => 'https://harborsaas.example/outreach-templates',
            'anchor_text' => 'click here now',
            'requirements' => 'Need editorial authority placement with natural branded anchor.',
            'status' => 'rejected',
            'status_label' => 'Rejected',
            'submitted_at' => 'Nov 08, 2026 · 11:30 AM',
            'feedback' => 'Word count is below publisher minimum (1,000 words) and anchor text uses generic CTA phrasing.',
            'excerpt' => 'Publishers reject cold guest post drafts within seconds when openings sound AI-templated or overly sales-driven...',
            'body' => [
                'Publishers reject cold guest post drafts within seconds when openings sound AI-templated or overly sales-driven.',
                'Editors look for original practitioner examples, clean outbound citations, and relevant anchor phrasing.',
            ],
        ],
    ];

    $counts = [
        'total' => count($articles),
        'pending_review' => collect($articles)->where('status', 'pending_review')->count(),
        'corrections_requested' => collect($articles)->where('status', 'corrections_requested')->count(),
        'approved' => collect($articles)->where('status', 'approved')->count(),
    ];
@endphp

<div class="space-y-6" id="articles-workspace">
    <!-- Header Banner -->
    <div class="panel p-6 sm:p-8 border-l-4 border-l-brand">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Article Review & Content Management</p>
                <h1 class="mt-1 text-2xl font-bold text-navy">Articles</h1>
                <p class="mt-1.5 max-w-2xl text-sm text-navy/70">
                    Review customer-submitted drafts and staff-prepared articles, verify target URL & anchor text placement, and approve content for publishing.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="badge bg-navy text-white px-3 py-1.5 text-xs font-medium">
                    Queue: {{ $counts['total'] }} Articles
                </span>
                <a href="{{ route('seo.submissions') }}" class="btn-primary text-xs px-4 py-2">
                    Go to Submissions &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Action Feedback Banner (client-side preview state) -->
    <div id="article-action-toast" class="hidden rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span id="article-action-toast-text">Review status updated.</span>
        </div>
        <button type="button" id="article-toast-close" class="text-xs font-semibold text-emerald-700 hover:underline">Dismiss</button>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="panel p-5 border-t-4 border-t-brand">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Total Articles</p>
            <p class="mt-1 font-display text-3xl font-bold text-navy">{{ $counts['total'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Customer & staff drafts</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-amber-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Pending Review</p>
            <p class="mt-1 font-display text-3xl font-bold text-amber-600" data-count-role="pending_review">{{ $counts['pending_review'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Awaiting editorial check</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-sky-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Needs Corrections</p>
            <p class="mt-1 font-display text-3xl font-bold text-sky-600" data-count-role="corrections_requested">{{ $counts['corrections_requested'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Revision feedback sent</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-emerald-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Approved</p>
            <p class="mt-1 font-display text-3xl font-bold text-emerald-600" data-count-role="approved">{{ $counts['approved'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Ready for publisher queue</p>
        </div>
    </div>

    <!-- Filter Tabs & Search -->
    <div class="panel p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex flex-wrap items-center gap-1.5" id="article-filter-tabs">
            @php
                $tabs = [
                    'all' => 'All Articles',
                    'pending_review' => 'Pending Review',
                    'corrections_requested' => 'Corrections Requested',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ];
            @endphp
            @foreach ($tabs as $key => $label)
                <button
                    type="button"
                    data-filter="{{ $key }}"
                    @class([
                        'article-tab rounded-xl px-3.5 py-1.5 text-xs font-medium transition cursor-pointer',
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
                id="article-search-input"
                placeholder="Search title, order, domain, anchor..."
                class="block w-full rounded-xl border border-navy/20 bg-white py-2 pl-9 pr-3 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
            >
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-3.5 w-3.5 text-navy/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Article Review Cards -->
    <div class="space-y-4" id="article-cards-list">
        @foreach ($articles as $article)
            @php
                $badgeClasses = match ($article['status']) {
                    'approved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                    'corrections_requested' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                    'rejected' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                    default => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                };
            @endphp
            <article
                class="article-card panel p-5 sm:p-6 transition hover:shadow-md"
                data-article-id="{{ $article['id'] }}"
                data-status="{{ $article['status'] }}"
                data-search="{{ strtolower($article['id'].' '.$article['order_id'].' '.$article['title'].' '.$article['customer'].' '.$article['website'].' '.$article['anchor_text']) }}"
            >
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <!-- Left: Article & Backlink Specs -->
                    <div class="flex-1 space-y-4 min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="font-mono text-xs font-bold text-navy bg-navy/5 px-2.5 py-1 rounded-lg">{{ $article['order_id'] }}</span>
                            <span class="text-xs font-medium text-navy/55">{{ $article['id'] }}</span>
                            <span class="article-status-badge badge {{ $badgeClasses }}">{{ $article['status_label'] }}</span>
                            <span class="badge bg-canvas text-navy/70">{{ $article['source'] }}</span>
                            <span class="text-xs text-navy/50">Submitted: {{ $article['submitted_at'] }}</span>
                        </div>

                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-navy leading-snug">
                                {{ $article['title'] }}
                            </h2>
                            <p class="mt-1.5 text-xs text-navy/70 leading-relaxed">
                                {{ $article['excerpt'] }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-xl bg-canvas/60 p-4 border border-navy/5">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Customer & Length</p>
                                <p class="mt-0.5 text-sm font-semibold text-navy">{{ $article['customer'] }}</p>
                                <p class="mt-0.5 text-xs text-navy/60">{{ number_format($article['word_count']) }} words</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Publisher Website</p>
                                <p class="mt-0.5 text-sm font-semibold text-navy">{{ $article['website'] }}</p>
                                <div class="mt-1 flex items-center gap-2 text-[11px]">
                                    <span class="rounded bg-white px-1.5 py-0.5 text-navy font-mono font-medium ring-1 ring-navy/10">DA {{ $article['da'] }}</span>
                                    <span class="rounded bg-brand/10 px-1.5 py-0.5 text-brand font-mono font-medium">DR {{ $article['dr'] }}</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Target URL & Anchor</p>
                                <p class="mt-0.5">
                                    <span class="inline-block rounded bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand">
                                        "{{ $article['anchor_text'] }}"
                                    </span>
                                </p>
                                <a
                                    href="{{ $article['target_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-1 inline-flex items-center gap-1 text-xs text-navy/75 hover:text-brand break-all"
                                >
                                    {{ $article['target_url'] }}
                                </a>
                            </div>
                        </div>

                        <div class="rounded-xl bg-white p-3.5 border border-navy/10 text-xs text-navy/75">
                            <span class="font-semibold text-navy">Customer Requirements:</span>
                            {{ $article['requirements'] }}
                        </div>
                    </div>

                    <!-- Right: Review Actions -->
                    <div class="w-full lg:w-80 shrink-0 rounded-xl bg-canvas/50 p-4 border border-navy/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-navy">Article Review</h3>
                            <button
                                type="button"
                                class="preview-article-btn text-xs font-semibold text-brand hover:underline cursor-pointer"
                                data-title="{{ $article['title'] }}"
                                data-order="{{ $article['order_id'] }}"
                                data-anchor="{{ $article['anchor_text'] }}"
                                data-target="{{ $article['target_url'] }}"
                                data-website="{{ $article['website'] }}"
                                data-body='@json($article['body'])'
                            >
                                Preview Content &rarr;
                            </button>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-navy/55 mb-1">
                                Reviewer Feedback
                            </label>
                            <textarea
                                rows="3"
                                placeholder="Add editorial notes or correction instructions..."
                                class="article-feedback-input block w-full rounded-xl border border-navy/20 bg-white px-3 py-2 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                            >{{ $article['feedback'] }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <button
                                type="button"
                                class="article-decision-btn btn-primary w-full text-xs py-2 cursor-pointer"
                                data-decision="approved"
                                data-label="Approved"
                            >
                                Approve Article
                            </button>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    class="article-decision-btn btn w-full bg-white text-navy ring-1 ring-navy/15 hover:bg-canvas text-xs py-2 cursor-pointer"
                                    data-decision="corrections_requested"
                                    data-label="Corrections Requested"
                                >
                                    Request Edits
                                </button>
                                <button
                                    type="button"
                                    class="article-decision-btn btn w-full bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100 text-xs py-2 cursor-pointer"
                                    data-decision="rejected"
                                    data-label="Rejected"
                                >
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach

        <div id="articles-empty-state" class="hidden panel p-10 text-center">
            <p class="text-sm font-semibold text-navy">No articles match the selected filter.</p>
            <p class="mt-1 text-xs text-navy/60">Try clearing the search query or switching to All Articles.</p>
        </div>
    </div>
</div>

<!-- Article Content Preview Modal -->
<div id="article-preview-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/60 p-4">
    <div class="panel w-full max-w-2xl overflow-hidden shadow-xl">
        <div class="flex items-start justify-between gap-4 border-b border-navy/10 bg-canvas/40 px-6 py-4">
            <div>
                <p id="modal-article-meta" class="text-xs font-semibold uppercase tracking-wider text-brand"></p>
                <h2 id="modal-article-title" class="mt-1 text-lg font-bold text-navy"></h2>
            </div>
            <button type="button" id="modal-article-close" class="rounded-lg p-1.5 text-navy/60 hover:bg-navy/10 hover:text-navy cursor-pointer">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="rounded-xl bg-canvas/60 p-4 text-xs space-y-1 border border-navy/10">
                <div><span class="font-semibold text-navy">Expected Anchor:</span> <span id="modal-article-anchor" class="font-semibold text-brand"></span></div>
                <div><span class="font-semibold text-navy">Target URL:</span> <span id="modal-article-target" class="text-navy/80 break-all"></span></div>
            </div>
            <div id="modal-article-body" class="space-y-3 text-sm leading-6 text-navy/85"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let activeFilter = 'all';
    const tabs = document.querySelectorAll('.article-tab');
    const cards = Array.from(document.querySelectorAll('.article-card'));
    const searchInput = document.getElementById('article-search-input');
    const emptyState = document.getElementById('articles-empty-state');
    const toast = document.getElementById('article-action-toast');
    const toastText = document.getElementById('article-action-toast-text');

    const badgeMap = {
        approved: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        corrections_requested: 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
        rejected: 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
        pending_review: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    };

    const refreshCounts = () => {
        ['pending_review', 'corrections_requested', 'approved'].forEach((status) => {
            const el = document.querySelector(`[data-count-role="${status}"]`);
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

    document.querySelectorAll('.article-decision-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.article-card');
            const decision = btn.dataset.decision;
            const label = btn.dataset.label;
            const badge = card.querySelector('.article-status-badge');

            card.dataset.status = decision;
            badge.className = `article-status-badge badge ${badgeMap[decision]}`;
            badge.textContent = label;

            toastText.textContent = `Article ${card.dataset.articleId} marked as "${label}" (UI preview — backend integration pending).`;
            toast.classList.remove('hidden');
            refreshCounts();
            applyFilters();
        });
    });

    document.getElementById('article-toast-close')?.addEventListener('click', () => {
        toast.classList.add('hidden');
    });

    const modal = document.getElementById('article-preview-modal');
    document.querySelectorAll('.preview-article-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById('modal-article-meta').textContent = `${btn.dataset.order} · ${btn.dataset.website}`;
            document.getElementById('modal-article-title').textContent = btn.dataset.title;
            document.getElementById('modal-article-anchor').textContent = btn.dataset.anchor;
            document.getElementById('modal-article-target').textContent = btn.dataset.target;

            const bodyContainer = document.getElementById('modal-article-body');
            bodyContainer.innerHTML = '';
            JSON.parse(btn.dataset.body || '[]').forEach((paragraph) => {
                const p = document.createElement('p');
                p.textContent = paragraph;
                bodyContainer.appendChild(p);
            });

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    document.getElementById('modal-article-close')?.addEventListener('click', () => {
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
