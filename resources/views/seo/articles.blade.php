@extends('layouts.seo')

@section('title', 'Articles')

@section('content')
@php
    $authorName = auth()->user()?->name ?? 'SEO Staff';

    $articles = $articles ?? [
        [
            'id' => 'ART-1042',
            'order_id' => 'BM-20261111-014',
            'title' => 'Enterprise AI Adoption Trends for SaaS Marketing Teams in 2026',
            'customer' => 'Noah Williams',
            'author' => $authorName,
            'website' => 'techpulsejournal.com',
            'da' => 68,
            'dr' => 72,
            'word_count' => 1250,
            'target_url' => 'https://noahwilliams.example/ai-marketing-suite',
            'anchor_text' => 'enterprise AI marketing',
            'requirements' => 'Place the anchor naturally in the second H2 section and avoid promotional fluff.',
            'status' => 'draft',
            'status_label' => 'Draft',
            'updated_at' => 'Nov 11, 2026 · 09:40 AM',
            'admin_feedback' => 'Not submitted yet. Finish the article draft and send it to Admin for approval.',
            'excerpt' => 'Modern B2B marketing teams are moving beyond simple prompt wrappers toward governed AI workflows that connect CRM attribution directly to organic search demand...',
            'content' => "Modern B2B marketing teams are moving beyond simple prompt wrappers toward governed AI workflows that connect CRM attribution directly to organic search demand.\n\nTeams implementing enterprise AI marketing platforms report faster campaign iteration when editorial guidelines, brand terminology, and target landing pages are standardized before outreach begins.\n\nTo sustain organic visibility, editors should pair original benchmark data with contextual citations rather than relying on generic listicles.",
        ],
        [
            'id' => 'ART-1041',
            'order_id' => 'BM-20261111-019',
            'title' => 'How Sustainable Packaging Reduces Last-Mile E-Commerce Waste',
            'customer' => 'Morgan Digital',
            'author' => $authorName,
            'website' => 'greencommerceweekly.io',
            'da' => 59,
            'dr' => 63,
            'word_count' => 980,
            'target_url' => 'https://morgandigital.example/eco-packaging',
            'anchor_text' => 'sustainable ecommerce packaging',
            'requirements' => 'Use UK/US neutral spelling and include one bullet list of fulfillment tips.',
            'status' => 'revisions_requested',
            'status_label' => 'Revisions Requested',
            'updated_at' => 'Nov 10, 2026 · 04:15 PM',
            'admin_feedback' => 'Admin Note: Anchor text currently points to the homepage instead of /eco-packaging. Please update the link and add ~150 words to the intro.',
            'excerpt' => 'Direct-to-consumer brands face rising shipping costs and stricter retail sustainability rules across North America and Europe...',
            'content' => "Direct-to-consumer brands face rising shipping costs and stricter retail sustainability rules across North America and Europe.\n\nSwitching to right-sized recyclable mailers helps warehouses cut dimensional weight fees while improving unboxing perception.\n\nPartners evaluating sustainable ecommerce packaging should audit void-fill material ratios before Q1 carrier rate updates.",
        ],
        [
            'id' => 'ART-1040',
            'order_id' => 'BM-20261111-027',
            'title' => 'Payroll Automation Benchmarks for Distributed Remote Teams',
            'customer' => 'Northstar HR',
            'author' => $authorName,
            'website' => 'workplaceledger.com',
            'da' => 64,
            'dr' => 68,
            'word_count' => 1120,
            'target_url' => 'https://northstarhr.example/global-payroll',
            'anchor_text' => 'global payroll software',
            'requirements' => 'Exclude competitor mentions and verify anchor appears once above the fold.',
            'status' => 'pending_admin_approval',
            'status_label' => 'Pending Admin Approval',
            'updated_at' => 'Nov 11, 2026 · 08:10 AM',
            'admin_feedback' => 'Submitted to Admin. Waiting for approval before publishing to publisher website.',
            'excerpt' => 'Managing multi-currency contractor payouts alongside full-time statutory filings requires tighter treasury coordination...',
            'content' => "Managing multi-currency contractor payouts alongside full-time statutory filings requires tighter treasury coordination.\n\nModern finance teams adopt global payroll software to consolidate tax withholding rules and cut monthly reconciliation cycles in half.\n\nStandardizing approval workflows early prevents compliance bottlenecks as headcount expands into new regions.",
        ],
        [
            'id' => 'ART-1039',
            'order_id' => 'BM-20261111-023',
            'title' => 'Cloud FinOps Playbook: Cutting Kubernetes Idle Spend by 32%',
            'customer' => 'Vertex Cloud Labs',
            'author' => $authorName,
            'website' => 'devopsinsider.net',
            'da' => 74,
            'dr' => 77,
            'word_count' => 1420,
            'target_url' => 'https://vertexcloud.example/kubernetes-cost-control',
            'anchor_text' => 'Kubernetes cost optimization',
            'requirements' => 'Technical tone for engineering leads; dofollow contextual placement inside body paragraph.',
            'status' => 'admin_approved',
            'status_label' => 'Admin Approved',
            'updated_at' => 'Nov 09, 2026 · 02:05 PM',
            'admin_feedback' => 'Approved by Admin: Target URL, anchor placement, and formatting verified. Ready to publish.',
            'excerpt' => 'Over-provisioned node pools quietly drain cloud budgets when autoscaling policies are left on staging defaults...',
            'content' => "Over-provisioned node pools quietly drain cloud budgets when autoscaling policies are left on staging defaults.\n\nEngineering leads who prioritize Kubernetes cost optimization combine spot-instance fallbacks with weekly namespace right-sizing reviews.\n\nClear cost ownership tags turn monthly cloud invoices into actionable sprint metrics.",
        ],
    ];

    $availableOrders = $availableOrders ?? [
        [
            'order_id' => 'BM-20261111-035',
            'customer' => 'Pulse Analytics',
            'website' => 'fintechdigest.co',
            'da' => 66,
            'dr' => 70,
            'target_url' => 'https://pulseanalytics.example/saas-retention-metrics',
            'anchor_text' => 'SaaS retention benchmarks',
            'requirements' => 'Include B2B SaaS cohort examples and place anchor naturally.',
        ],
        [
            'order_id' => 'BM-20261111-038',
            'customer' => 'Cedar Logistics',
            'website' => 'supplychainbrief.com',
            'da' => 57,
            'dr' => 61,
            'target_url' => 'https://cedarlogistics.example/freight-tracking',
            'anchor_text' => 'freight visibility software',
            'requirements' => 'Keep tone informative and focus on warehouse tracking efficiency.',
        ],
    ];

    $counts = [
        'total' => count($articles),
        'draft' => collect($articles)->where('status', 'draft')->count(),
        'pending_admin_approval' => collect($articles)->where('status', 'pending_admin_approval')->count(),
        'admin_approved' => collect($articles)->where('status', 'admin_approved')->count(),
    ];
@endphp

<div class="space-y-6" id="articles-workspace">
    <!-- Header Banner -->
    <div class="panel p-6 sm:p-8 border-l-4 border-l-brand">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Article Writing & Admin Approval</p>
                <h1 class="mt-1 text-2xl font-bold text-navy">Articles</h1>
                <p class="mt-1.5 max-w-2xl text-sm text-navy/70">
                    Write and edit articles for your assigned orders, verify target URL & anchor placement, and send completed drafts to Admin for approval.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" id="open-write-article-btn" class="btn-primary text-xs px-4 py-2 cursor-pointer">
                    + Write New Article
                </button>
                <a href="{{ route('seo.submissions') }}" class="btn-navy text-xs px-4 py-2">
                    Go to Submissions &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Action Feedback Banner -->
    <div id="article-action-toast" class="hidden rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span id="article-action-toast-text">Article updated.</span>
        </div>
        <button type="button" id="article-toast-close" class="text-xs font-semibold text-emerald-700 hover:underline cursor-pointer">Dismiss</button>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="panel p-5 border-t-4 border-t-brand">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">My Articles</p>
            <p class="mt-1 font-display text-3xl font-bold text-navy" data-count-role="total">{{ $counts['total'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Articles prepared by you</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-sky-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Drafts</p>
            <p class="mt-1 font-display text-3xl font-bold text-sky-600" data-count-role="draft">{{ $counts['draft'] }}</p>
            <p class="mt-2 text-xs text-navy/55">In progress before submission</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-amber-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Pending Admin Approval</p>
            <p class="mt-1 font-display text-3xl font-bold text-amber-600" data-count-role="pending_admin_approval">{{ $counts['pending_admin_approval'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Sent to Admin for review</p>
        </div>
        <div class="panel p-5 border-t-4 border-t-emerald-500">
            <p class="text-xs font-semibold uppercase tracking-wider text-navy/60">Admin Approved</p>
            <p class="mt-1 font-display text-3xl font-bold text-emerald-600" data-count-role="admin_approved">{{ $counts['admin_approved'] }}</p>
            <p class="mt-2 text-xs text-navy/55">Approved & ready to publish</p>
        </div>
    </div>

    <!-- Filter Tabs & Search -->
    <div class="panel p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex flex-wrap items-center gap-1.5" id="article-filter-tabs">
            @php
                $tabs = [
                    'all' => 'All Articles',
                    'draft' => 'Drafts',
                    'pending_admin_approval' => 'Pending Admin Approval',
                    'revisions_requested' => 'Revisions Requested',
                    'admin_approved' => 'Admin Approved',
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

    <!-- Staff Article Cards -->
    <div class="space-y-4" id="article-cards-list">
        @foreach ($articles as $article)
            @php
                $badgeClasses = match ($article['status']) {
                    'admin_approved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                    'pending_admin_approval' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                    'revisions_requested' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
                    default => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
                };
            @endphp
            <article
                class="article-card panel p-5 sm:p-6 transition hover:shadow-md"
                data-article-id="{{ $article['id'] }}"
                data-order-id="{{ $article['order_id'] }}"
                data-status="{{ $article['status'] }}"
                data-title="{{ $article['title'] }}"
                data-website="{{ $article['website'] }}"
                data-target="{{ $article['target_url'] }}"
                data-anchor="{{ $article['anchor_text'] }}"
                data-requirements="{{ $article['requirements'] }}"
                data-content="{{ $article['content'] }}"
                data-search="{{ strtolower($article['id'].' '.$article['order_id'].' '.$article['title'].' '.$article['customer'].' '.$article['website'].' '.$article['anchor_text']) }}"
            >
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <!-- Left: Article & Backlink Specs -->
                    <div class="flex-1 space-y-4 min-w-0">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="font-mono text-xs font-bold text-navy bg-navy/5 px-2.5 py-1 rounded-lg">{{ $article['order_id'] }}</span>
                            <span class="text-xs font-medium text-navy/55">{{ $article['id'] }}</span>
                            <span class="article-status-badge badge {{ $badgeClasses }}">{{ $article['status_label'] }}</span>
                            <span class="badge bg-canvas text-navy/70">Author: {{ $article['author'] }}</span>
                            <span class="article-updated-at text-xs text-navy/50">Updated: {{ $article['updated_at'] }}</span>
                        </div>

                        <div>
                            <h2 class="article-card-title text-base sm:text-lg font-bold text-navy leading-snug">
                                {{ $article['title'] }}
                            </h2>
                            <p class="article-card-excerpt mt-1.5 text-xs text-navy/70 leading-relaxed">
                                {{ $article['excerpt'] }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-xl bg-canvas/60 p-4 border border-navy/5">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Customer & Length</p>
                                <p class="mt-0.5 text-sm font-semibold text-navy">{{ $article['customer'] }}</p>
                                <p class="article-word-count mt-0.5 text-xs text-navy/60">{{ number_format($article['word_count']) }} words</p>
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

                    <!-- Right: Admin Approval Status & Staff Writing Actions -->
                    <div class="w-full lg:w-80 shrink-0 rounded-xl bg-canvas/50 p-4 border border-navy/10 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-navy">Admin Approval Workflow</h3>
                            <button
                                type="button"
                                class="preview-article-btn text-xs font-semibold text-brand hover:underline cursor-pointer"
                            >
                                Preview &rarr;
                            </button>
                        </div>

                        <div class="rounded-xl bg-white p-3 border border-navy/10 space-y-1">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Admin Feedback</p>
                            <p class="article-admin-feedback text-xs text-navy/75 leading-relaxed">
                                {{ $article['admin_feedback'] }}
                            </p>
                        </div>

                        <div class="article-actions-slot space-y-2">
                            @if ($article['status'] === 'admin_approved')
                                <a
                                    href="{{ route('seo.submissions') }}"
                                    class="btn-primary w-full text-xs py-2"
                                >
                                    Proceed to Submissions &rarr;
                                </a>
                                <button
                                    type="button"
                                    class="edit-article-btn btn w-full bg-white text-navy ring-1 ring-navy/15 hover:bg-canvas text-xs py-2 cursor-pointer"
                                >
                                    View / Edit Approved Copy
                                </button>
                            @elseif ($article['status'] === 'pending_admin_approval')
                                <button
                                    type="button"
                                    class="recall-draft-btn btn w-full bg-white text-navy ring-1 ring-navy/15 hover:bg-canvas text-xs py-2 cursor-pointer"
                                >
                                    Recall to Draft to Edit
                                </button>
                                <button
                                    type="button"
                                    class="edit-article-btn btn-navy w-full text-xs py-2 cursor-pointer"
                                >
                                    View Draft Copy
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="send-approval-btn btn-primary w-full text-xs py-2 cursor-pointer"
                                >
                                    {{ $article['status'] === 'revisions_requested' ? 'Resend for Admin Approval' : 'Send for Admin Approval' }}
                                </button>
                                <button
                                    type="button"
                                    class="edit-article-btn btn w-full bg-white text-navy ring-1 ring-navy/15 hover:bg-canvas text-xs py-2 cursor-pointer"
                                >
                                    Edit Article Draft
                                </button>
                            @endif
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

<!-- Write / Edit Article Modal -->
<div id="article-editor-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-navy/60 p-4">
    <div class="panel w-full max-w-3xl overflow-hidden shadow-xl">
        <div class="flex items-start justify-between gap-4 border-b border-navy/10 bg-navy px-6 py-4 text-white">
            <div>
                <p id="editor-modal-subtitle" class="text-xs font-semibold uppercase tracking-wider text-brand">SEO Article Editor</p>
                <h2 id="editor-modal-heading" class="mt-0.5 text-lg font-bold text-white">Write Article</h2>
            </div>
            <button type="button" id="editor-modal-close" class="rounded-lg p-1.5 text-white/70 hover:bg-white/10 hover:text-white cursor-pointer">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="article-editor-form" class="p-6 space-y-4 max-h-[78vh] overflow-y-auto">
            <input type="hidden" id="editor-article-id" value="">

            <div id="editor-order-picker-wrap">
                <label class="block text-xs font-semibold uppercase tracking-wider text-navy/70 mb-1">Select Assigned Order</label>
                <select id="editor-order-select" class="block w-full rounded-xl border border-navy/20 bg-white px-3 py-2 text-xs text-navy focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
                    @foreach ($availableOrders as $orderOption)
                        <option
                            value="{{ $orderOption['order_id'] }}"
                            data-customer="{{ $orderOption['customer'] }}"
                            data-website="{{ $orderOption['website'] }}"
                            data-da="{{ $orderOption['da'] }}"
                            data-dr="{{ $orderOption['dr'] }}"
                            data-target="{{ $orderOption['target_url'] }}"
                            data-anchor="{{ $orderOption['anchor_text'] }}"
                            data-requirements="{{ $orderOption['requirements'] }}"
                        >
                            {{ $orderOption['order_id'] }} · {{ $orderOption['customer'] }} ({{ $orderOption['website'] }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl bg-canvas/70 p-3.5 text-xs space-y-1 border border-navy/10">
                <div><span class="font-semibold text-navy">Order & Publisher:</span> <span id="editor-order-meta" class="text-navy/80"></span></div>
                <div><span class="font-semibold text-navy">Anchor Text:</span> <span id="editor-anchor-meta" class="font-semibold text-brand"></span></div>
                <div><span class="font-semibold text-navy">Target URL:</span> <span id="editor-target-meta" class="text-navy/80 break-all"></span></div>
                <div><span class="font-semibold text-navy">Requirements:</span> <span id="editor-requirements-meta" class="text-navy/75"></span></div>
            </div>

            <div>
                <label for="editor-title-input" class="block text-xs font-semibold uppercase tracking-wider text-navy/70 mb-1">Article Title</label>
                <input
                    id="editor-title-input"
                    type="text"
                    required
                    placeholder="Enter SEO article headline..."
                    class="block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                >
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="editor-content-input" class="block text-xs font-semibold uppercase tracking-wider text-navy/70">Article Body</label>
                    <span id="editor-word-counter" class="text-xs font-mono text-navy/60">0 words</span>
                </div>
                <textarea
                    id="editor-content-input"
                    rows="8"
                    required
                    placeholder="Write the full backlink article here with the required anchor text and target URL context..."
                    class="block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm leading-6 text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                ></textarea>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-navy/10">
                <button type="button" id="editor-save-draft-btn" class="btn bg-white text-navy ring-1 ring-navy/15 hover:bg-canvas text-xs px-4 py-2 cursor-pointer">
                    Save as Draft
                </button>
                <button type="submit" class="btn-primary text-xs px-4 py-2 cursor-pointer">
                    Send for Admin Approval
                </button>
            </div>
        </form>
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
    const cardsList = document.getElementById('article-cards-list');
    const searchInput = document.getElementById('article-search-input');
    const emptyState = document.getElementById('articles-empty-state');
    const toast = document.getElementById('article-action-toast');
    const toastText = document.getElementById('article-action-toast-text');
    const submissionsRoute = @json(route('seo.submissions'));
    const currentAuthor = @json($authorName);

    const badgeMap = {
        admin_approved: { className: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', label: 'Admin Approved' },
        pending_admin_approval: { className: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200', label: 'Pending Admin Approval' },
        revisions_requested: { className: 'bg-rose-50 text-rose-700 ring-1 ring-rose-200', label: 'Revisions Requested' },
        draft: { className: 'bg-sky-50 text-sky-700 ring-1 ring-sky-200', label: 'Draft' },
    };

    const getCards = () => Array.from(document.querySelectorAll('.article-card'));

    const refreshCounts = () => {
        const cards = getCards();
        const totalEl = document.querySelector('[data-count-role="total"]');
        if (totalEl) totalEl.textContent = cards.length;

        ['draft', 'pending_admin_approval', 'admin_approved'].forEach((status) => {
            const el = document.querySelector(`[data-count-role="${status}"]`);
            if (el) {
                el.textContent = cards.filter((c) => c.dataset.status === status).length;
            }
        });
    };

    const applyFilters = () => {
        const query = (searchInput.value || '').trim().toLowerCase();
        let visibleCount = 0;

        getCards().forEach((card) => {
            const matchesTab = activeFilter === 'all' || card.dataset.status === activeFilter;
            const matchesQuery = query === '' || (card.dataset.search || '').includes(query);
            const show = matchesTab && matchesQuery;
            card.classList.toggle('hidden', !show);
            if (show) visibleCount++;
        });

        emptyState.classList.toggle('hidden', visibleCount > 0);
    };

    const showToast = (msg) => {
        toastText.textContent = msg;
        toast.classList.remove('hidden');
    };

    const countWords = (text) => {
        const cleaned = (text || '').trim();
        return cleaned ? cleaned.split(/\s+/).length : 0;
    };

    const renderActionSlot = (card, status) => {
        const slot = card.querySelector('.article-actions-slot');
        if (status === 'admin_approved') {
            slot.innerHTML = `
                <a href="${submissionsRoute}" class="btn-primary w-full text-xs py-2">Proceed to Submissions &rarr;</a>
                <button type="button" class="edit-article-btn btn w-full bg-white text-navy ring-1 ring-navy/15 hover:bg-canvas text-xs py-2 cursor-pointer">View / Edit Approved Copy</button>
            `;
        } else if (status === 'pending_admin_approval') {
            slot.innerHTML = `
                <button type="button" class="recall-draft-btn btn w-full bg-white text-navy ring-1 ring-navy/15 hover:bg-canvas text-xs py-2 cursor-pointer">Recall to Draft to Edit</button>
                <button type="button" class="edit-article-btn btn-navy w-full text-xs py-2 cursor-pointer">View Draft Copy</button>
            `;
        } else {
            const sendLabel = status === 'revisions_requested' ? 'Resend for Admin Approval' : 'Send for Admin Approval';
            slot.innerHTML = `
                <button type="button" class="send-approval-btn btn-primary w-full text-xs py-2 cursor-pointer">${sendLabel}</button>
                <button type="button" class="edit-article-btn btn w-full bg-white text-navy ring-1 ring-navy/15 hover:bg-canvas text-xs py-2 cursor-pointer">Edit Article Draft</button>
            `;
        }
    };

    const setCardStatus = (card, status, feedbackText, message) => {
        card.dataset.status = status;
        const badge = card.querySelector('.article-status-badge');
        badge.className = `article-status-badge badge ${badgeMap[status].className}`;
        badge.textContent = badgeMap[status].label;
        card.querySelector('.article-updated-at').textContent = 'Updated: Just now';
        if (feedbackText) {
            card.querySelector('.article-admin-feedback').textContent = feedbackText;
        }
        renderActionSlot(card, status);
        refreshCounts();
        applyFilters();
        showToast(message);
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

    document.getElementById('article-toast-close')?.addEventListener('click', () => {
        toast.classList.add('hidden');
    });

    // Editor Modal
    const editorModal = document.getElementById('article-editor-modal');
    const editorForm = document.getElementById('article-editor-form');
    const editorArticleId = document.getElementById('editor-article-id');
    const editorOrderPickerWrap = document.getElementById('editor-order-picker-wrap');
    const editorOrderSelect = document.getElementById('editor-order-select');
    const editorTitleInput = document.getElementById('editor-title-input');
    const editorContentInput = document.getElementById('editor-content-input');
    const editorWordCounter = document.getElementById('editor-word-counter');

    const syncOrderMetaFromSelect = () => {
        const option = editorOrderSelect.options[editorOrderSelect.selectedIndex];
        if (!option) return;
        document.getElementById('editor-order-meta').textContent = `${option.value} · ${option.dataset.website} (DA ${option.dataset.da} / DR ${option.dataset.dr})`;
        document.getElementById('editor-anchor-meta').textContent = `"${option.dataset.anchor}"`;
        document.getElementById('editor-target-meta').textContent = option.dataset.target;
        document.getElementById('editor-requirements-meta').textContent = option.dataset.requirements;
    };

    const updateWordCounter = () => {
        editorWordCounter.textContent = `${countWords(editorContentInput.value)} words`;
    };

    editorOrderSelect?.addEventListener('change', syncOrderMetaFromSelect);
    editorContentInput?.addEventListener('input', updateWordCounter);

    document.getElementById('open-write-article-btn')?.addEventListener('click', () => {
        editorArticleId.value = '';
        editorOrderPickerWrap.classList.remove('hidden');
        document.getElementById('editor-modal-heading').textContent = 'Write New Article';
        document.getElementById('editor-modal-subtitle').textContent = 'SEO Article Authoring';
        editorTitleInput.value = '';
        editorContentInput.value = '';
        syncOrderMetaFromSelect();
        updateWordCounter();
        editorModal.classList.remove('hidden');
        editorModal.classList.add('flex');
    });

    const closeEditorModal = () => {
        editorModal.classList.add('hidden');
        editorModal.classList.remove('flex');
    };

    document.getElementById('editor-modal-close')?.addEventListener('click', closeEditorModal);
    editorModal?.addEventListener('click', (e) => {
        if (e.target === editorModal) closeEditorModal();
    });

    const createArticleCardElement = (data, status) => {
        const article = document.createElement('article');
        article.className = 'article-card panel p-5 sm:p-6 transition hover:shadow-md';
        article.dataset.articleId = data.id;
        article.dataset.orderId = data.orderId;
        article.dataset.status = status;
        article.dataset.title = data.title;
        article.dataset.website = data.website;
        article.dataset.target = data.target;
        article.dataset.anchor = data.anchor;
        article.dataset.requirements = data.requirements;
        article.dataset.content = data.content;
        article.dataset.search = `${data.id} ${data.orderId} ${data.title} ${data.customer} ${data.website} ${data.anchor}`.toLowerCase();

        const words = countWords(data.content);
        const excerpt = data.content.slice(0, 165) + (data.content.length > 165 ? '...' : '');
        const feedback = status === 'pending_admin_approval'
            ? 'Submitted to Admin. Waiting for approval before publishing to publisher website.'
            : 'Saved as draft. Review and send to Admin for approval when ready.';

        article.innerHTML = `
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div class="flex-1 space-y-4 min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="font-mono text-xs font-bold text-navy bg-navy/5 px-2.5 py-1 rounded-lg"></span>
                        <span class="text-xs font-medium text-navy/55"></span>
                        <span class="article-status-badge badge ${badgeMap[status].className}"></span>
                        <span class="badge bg-canvas text-navy/70"></span>
                        <span class="article-updated-at text-xs text-navy/50">Updated: Just now</span>
                    </div>
                    <div>
                        <h2 class="article-card-title text-base sm:text-lg font-bold text-navy leading-snug"></h2>
                        <p class="article-card-excerpt mt-1.5 text-xs text-navy/70 leading-relaxed"></p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 rounded-xl bg-canvas/60 p-4 border border-navy/5">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Customer & Length</p>
                            <p class="new-customer mt-0.5 text-sm font-semibold text-navy"></p>
                            <p class="article-word-count mt-0.5 text-xs text-navy/60"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Publisher Website</p>
                            <p class="new-website mt-0.5 text-sm font-semibold text-navy"></p>
                            <div class="mt-1 flex items-center gap-2 text-[11px]">
                                <span class="new-da rounded bg-white px-1.5 py-0.5 text-navy font-mono font-medium ring-1 ring-navy/10"></span>
                                <span class="new-dr rounded bg-brand/10 px-1.5 py-0.5 text-brand font-mono font-medium"></span>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Target URL & Anchor</p>
                            <p class="mt-0.5"><span class="new-anchor inline-block rounded bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand"></span></p>
                            <a class="new-target mt-1 inline-flex items-center gap-1 text-xs text-navy/75 hover:text-brand break-all" target="_blank" rel="noopener noreferrer"></a>
                        </div>
                    </div>
                    <div class="rounded-xl bg-white p-3.5 border border-navy/10 text-xs text-navy/75">
                        <span class="font-semibold text-navy">Customer Requirements:</span>
                        <span class="new-req"></span>
                    </div>
                </div>
                <div class="w-full lg:w-80 shrink-0 rounded-xl bg-canvas/50 p-4 border border-navy/10 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-navy">Admin Approval Workflow</h3>
                        <button type="button" class="preview-article-btn text-xs font-semibold text-brand hover:underline cursor-pointer">Preview &rarr;</button>
                    </div>
                    <div class="rounded-xl bg-white p-3 border border-navy/10 space-y-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-navy/50">Admin Feedback</p>
                        <p class="article-admin-feedback text-xs text-navy/75 leading-relaxed"></p>
                    </div>
                    <div class="article-actions-slot space-y-2"></div>
                </div>
            </div>
        `;

        const badgesRow = article.querySelector('.flex-wrap').children;
        badgesRow[0].textContent = data.orderId;
        badgesRow[1].textContent = data.id;
        badgesRow[2].textContent = badgeMap[status].label;
        badgesRow[3].textContent = `Author: ${currentAuthor}`;

        article.querySelector('.article-card-title').textContent = data.title;
        article.querySelector('.article-card-excerpt').textContent = excerpt;
        article.querySelector('.new-customer').textContent = data.customer;
        article.querySelector('.article-word-count').textContent = `${words} words`;
        article.querySelector('.new-website').textContent = data.website;
        article.querySelector('.new-da').textContent = `DA ${data.da}`;
        article.querySelector('.new-dr').textContent = `DR ${data.dr}`;
        article.querySelector('.new-anchor').textContent = `"${data.anchor}"`;
        const targetLink = article.querySelector('.new-target');
        targetLink.href = data.target;
        targetLink.textContent = data.target;
        article.querySelector('.new-req').textContent = data.requirements;
        article.querySelector('.article-admin-feedback').textContent = feedback;

        renderActionSlot(article, status);
        return article;
    };

    const saveEditorArticle = (targetStatus) => {
        const title = editorTitleInput.value.trim();
        const content = editorContentInput.value.trim();
        if (!title || !content) {
            editorForm.reportValidity();
            return;
        }

        const existingId = editorArticleId.value;
        if (existingId) {
            const card = document.querySelector(`.article-card[data-article-id="${existingId}"]`);
            if (card) {
                card.dataset.title = title;
                card.dataset.content = content;
                card.querySelector('.article-card-title').textContent = title;
                card.querySelector('.article-card-excerpt').textContent = content.slice(0, 165) + (content.length > 165 ? '...' : '');
                card.querySelector('.article-word-count').textContent = `${countWords(content)} words`;

                const feedback = targetStatus === 'pending_admin_approval'
                    ? 'Submitted to Admin. Waiting for approval before publishing to publisher website.'
                    : 'Draft updated. Send to Admin for approval when ready.';
                const msg = targetStatus === 'pending_admin_approval'
                    ? `Article ${existingId} saved and sent to Admin for approval (UI preview — backend integration pending).`
                    : `Draft changes saved for ${existingId} (UI preview — backend integration pending).`;
                setCardStatus(card, targetStatus, feedback, msg);
            }
        } else {
            const option = editorOrderSelect.options[editorOrderSelect.selectedIndex];
            const newId = `ART-${Math.floor(1043 + Math.random() * 50)}`;
            const newCard = createArticleCardElement({
                id: newId,
                orderId: option.value,
                customer: option.dataset.customer,
                website: option.dataset.website,
                da: option.dataset.da,
                dr: option.dataset.dr,
                target: option.dataset.target,
                anchor: option.dataset.anchor,
                requirements: option.dataset.requirements,
                title,
                content,
            }, targetStatus);

            cardsList.prepend(newCard);
            refreshCounts();
            applyFilters();
            showToast(
                targetStatus === 'pending_admin_approval'
                    ? `New article ${newId} created and sent to Admin for approval (UI preview).`
                    : `New article ${newId} saved as draft (UI preview).`
            );
        }

        closeEditorModal();
    };

    editorForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        saveEditorArticle('pending_admin_approval');
    });

    document.getElementById('editor-save-draft-btn')?.addEventListener('click', () => {
        saveEditorArticle('draft');
    });

    // Preview Modal
    const previewModal = document.getElementById('article-preview-modal');
    const closePreviewModal = () => {
        previewModal.classList.add('hidden');
        previewModal.classList.remove('flex');
    };
    document.getElementById('modal-article-close')?.addEventListener('click', closePreviewModal);
    previewModal?.addEventListener('click', (e) => {
        if (e.target === previewModal) closePreviewModal();
    });

    // Delegated card actions (works for initial + newly added cards)
    cardsList?.addEventListener('click', (e) => {
        const card = e.target.closest('.article-card');
        if (!card) return;

        if (e.target.closest('.send-approval-btn')) {
            setCardStatus(
                card,
                'pending_admin_approval',
                'Submitted to Admin. Waiting for approval before publishing to publisher website.',
                `Article ${card.dataset.articleId} sent to Admin for approval (UI preview — backend integration pending).`
            );
            return;
        }

        if (e.target.closest('.recall-draft-btn')) {
            setCardStatus(
                card,
                'draft',
                'Recalled to draft for further editing before sending to Admin.',
                `Article ${card.dataset.articleId} moved back to Draft.`
            );
            return;
        }

        if (e.target.closest('.edit-article-btn')) {
            editorArticleId.value = card.dataset.articleId;
            editorOrderPickerWrap.classList.add('hidden');
            document.getElementById('editor-modal-heading').textContent = `Edit Article · ${card.dataset.articleId}`;
            document.getElementById('editor-modal-subtitle').textContent = `${card.dataset.orderId} · ${card.dataset.website}`;
            document.getElementById('editor-order-meta').textContent = `${card.dataset.orderId} · ${card.dataset.website}`;
            document.getElementById('editor-anchor-meta').textContent = `"${card.dataset.anchor}"`;
            document.getElementById('editor-target-meta').textContent = card.dataset.target;
            document.getElementById('editor-requirements-meta').textContent = card.dataset.requirements;

            editorTitleInput.value = card.dataset.title || '';
            editorContentInput.value = card.dataset.content || '';
            updateWordCounter();

            editorModal.classList.remove('hidden');
            editorModal.classList.add('flex');
            return;
        }

        if (e.target.closest('.preview-article-btn')) {
            document.getElementById('modal-article-meta').textContent = `${card.dataset.orderId} · ${card.dataset.website}`;
            document.getElementById('modal-article-title').textContent = card.dataset.title;
            document.getElementById('modal-article-anchor').textContent = card.dataset.anchor;
            document.getElementById('modal-article-target').textContent = card.dataset.target;

            const bodyContainer = document.getElementById('modal-article-body');
            bodyContainer.innerHTML = '';
            (card.dataset.content || '').split(/\n\n+/).forEach((paragraph) => {
                const p = document.createElement('p');
                p.textContent = paragraph;
                bodyContainer.appendChild(p);
            });

            previewModal.classList.remove('hidden');
            previewModal.classList.add('flex');
        }
    });
});
</script>
@endpush
