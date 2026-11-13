@extends('layouts.seo')

@section('title', 'SEO Staff Dashboard')

@push('head')
<style>
    .seo-widgets-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.5rem;
        align-items: start;
    }
    @media (min-width: 1024px) {
        .seo-widgets-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    .seo-cal-grid-7 {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.25rem;
        align-items: center;
    }
    .seo-cal-week {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.25rem;
        align-items: center;
        border-radius: 9999px;
        padding: 0.2rem 0.25rem;
    }
    .seo-cal-week--active {
        background-color: #f2f2f2;
    }
    .seo-cal-week--has-deadline {
        background-color: rgba(242, 242, 242, 0.55);
    }
    .seo-cal-day {
        position: relative;
        margin: 0 auto;
        display: flex;
        height: 2.25rem;
        width: 2.25rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #152029;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .seo-cal-day:hover {
        background-color: #ffffff;
    }
    .seo-cal-day--muted {
        color: rgba(21, 32, 41, 0.32);
    }
    .seo-cal-day--selected {
        background-color: #152029 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(21, 32, 41, 0.25);
    }
    .seo-cal-dot {
        position: absolute;
        bottom: 0.22rem;
        height: 0.32rem;
        width: 0.32rem;
        border-radius: 9999px;
        background-color: #ff4917;
    }
</style>
@endpush

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
                    Welcome back, <strong class="text-white">{{ $currentStaff }}</strong>. Review your assigned backlink orders, inspect target URLs, anchor texts & customer requirements, and update your work status.
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

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 p-4 text-sm font-medium text-rose-800 ring-1 ring-rose-200">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
            <p class="mt-3 text-xs text-navy/55">Active customer order items</p>
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
            <p class="mt-3 text-xs text-navy/55">Articles & anchor targets</p>
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
            <p class="mt-3 text-xs text-navy/55">Backlinks confirmed & reports ready</p>
        </div>
    </div>

    <!-- Assigned Orders Section -->
    <div class="panel overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-navy/10 bg-canvas/30">
            <div>
                <h2 class="text-base font-bold text-navy">Assigned Orders</h2>
                <p class="text-xs text-navy/60 mt-0.5">Orders assigned to you with target URLs, anchor texts, and customer requirements</p>
            </div>
            <a href="{{ route('seo.assigned-orders') }}" class="text-xs font-semibold text-brand hover:underline">
                View All Assigned Orders &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-navy/10 bg-canvas/50 text-[11px] font-semibold text-navy/60 uppercase tracking-wider">
                        <th class="py-3 px-4">Order / Customer</th>
                        <th class="py-3 px-4">Publisher Website</th>
                        <th class="py-3 px-4">Target URL & Anchor</th>
                        <th class="py-3 px-4">Customer Requirements</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-navy/10">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-canvas/40 transition align-top">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-navy">{{ $order['id'] }}</div>
                                <div class="text-xs text-navy/60">{{ $order['customer'] }}</div>
                                <span class="inline-block mt-1 text-[11px] font-medium text-navy/50">{{ $order['service'] }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-navy">{{ $order['website'] }}</div>
                                <div class="flex items-center gap-2 mt-1 text-[11px]">
                                    <span class="rounded bg-navy/5 px-1.5 py-0.5 text-navy font-mono font-medium">DA {{ $order['domain_authority'] }}</span>
                                    <span class="rounded bg-brand/10 px-1.5 py-0.5 text-brand font-mono font-medium">DR {{ $order['domain_rating'] }}</span>
                                </div>
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
                                <div class="mt-1 flex items-center gap-1.5 text-xs text-navy/70">
                                    <span class="text-navy/40">Anchor:</span>
                                    <span class="font-semibold text-brand bg-brand/5 px-1.5 py-0.5 rounded">{{ $order['anchor_text'] }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 max-w-sm">
                                <div class="text-xs text-navy">
                                    <span class="font-semibold">Topic:</span> {{ $order['article_topic'] }}
                                </div>
                                <div class="mt-1 text-xs text-navy/70">
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
                                <div class="mt-1 text-[11px] text-navy/60">
                                    Staff: {{ $order['assigned_staff'] }}
                                </div>
                                <div class="mt-0.5 text-[11px] text-navy/50">
                                    By {{ $order['assigned_by'] ?? 'Admin' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a
                                    href="{{ route('seo.assigned-orders', ['search' => $order['id']]) }}"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:text-brand-dark"
                                >
                                    Update Status &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-navy/60">No customer orders are assigned to you yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Task Deadline Calendar & Staff To-Do List Widgets -->
    @php
        $calendarDeadlines = $calendarDeadlines ?? [
            [
                'date' => '2026-11-12',
                'order_id' => 'BM-20261111-014',
                'title' => 'Submit draft for Enterprise AI Adoption',
                'customer' => 'Noah Williams',
                'website' => 'techpulsejournal.com',
                'status' => 'Due Today',
                'badge' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
            ],
            [
                'date' => '2026-11-14',
                'order_id' => 'BM-20261111-019',
                'title' => 'Revise anchor link to /eco-packaging',
                'customer' => 'Morgan Digital',
                'website' => 'greencommerceweekly.io',
                'status' => 'In Progress',
                'badge' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
            ],
            [
                'date' => '2026-11-16',
                'order_id' => 'BM-20261111-023',
                'title' => 'Publish approved Cloud FinOps article',
                'customer' => 'Vertex Cloud Labs',
                'website' => 'devopsinsider.net',
                'status' => 'Admin Approved',
                'badge' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
            ],
            [
                'date' => '2026-11-18',
                'order_id' => 'BM-20261111-027',
                'title' => 'Verify live dofollow backlink & anchor',
                'customer' => 'Northstar HR',
                'website' => 'workplaceledger.com',
                'status' => 'Scheduled',
                'badge' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
            ],
            [
                'date' => '2026-11-22',
                'order_id' => 'BM-20261111-035',
                'title' => 'Prepare Niche Edit placement report',
                'customer' => 'Pulse Analytics',
                'website' => 'fintechdigest.co',
                'status' => 'Assigned',
                'badge' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
            ],
        ];

        $todoProjects = collect($orders)
            ->map(fn ($o) => ['id' => $o['id'], 'label' => $o['id'].' · '.$o['website']])
            ->whenEmpty(fn () => collect([
                ['id' => 'BM-20261111-014', 'label' => 'BM-20261111-014 · techpulsejournal.com'],
                ['id' => 'BM-20261111-019', 'label' => 'BM-20261111-019 · greencommerceweekly.io'],
                ['id' => 'BM-20261111-023', 'label' => 'BM-20261111-023 · devopsinsider.net'],
                ['id' => 'BM-20261111-027', 'label' => 'BM-20261111-027 · workplaceledger.com'],
            ]))
            ->values();

        $initialTodos = $initialTodos ?? [
            [
                'id' => 1,
                'title' => 'Verify target URL and anchor text in AI Adoption draft',
                'project' => 'BM-20261111-014',
                'completed' => false,
            ],
            [
                'id' => 2,
                'title' => 'Update /eco-packaging link per Admin revision note',
                'project' => 'BM-20261111-019',
                'completed' => false,
            ],
            [
                'id' => 3,
                'title' => 'Send Cloud FinOps article to publisher editor',
                'project' => 'BM-20261111-023',
                'completed' => true,
            ],
            [
                'id' => 4,
                'title' => 'Check HTTP 200 & dofollow status on live placement',
                'project' => 'BM-20261111-027',
                'completed' => false,
            ],
        ];
    @endphp

    <div class="seo-widgets-grid">
        <!-- Task Deadline Calendar Widget (2-part card layout) -->
        <div class="space-y-3">
            <!-- Calendar Card -->
            <div class="panel p-5 sm:p-6">
                <div class="flex items-center justify-between mb-5">
                    <button
                        type="button"
                        id="cal-prev-month"
                        class="grid h-8 w-8 place-items-center rounded-full text-navy/70 hover:bg-canvas hover:text-navy transition cursor-pointer"
                        aria-label="Previous month"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h2 id="cal-month-label" class="font-display text-sm font-bold text-navy tracking-tight">November 2026</h2>
                    <button
                        type="button"
                        id="cal-next-month"
                        class="grid h-8 w-8 place-items-center rounded-full text-navy/70 hover:bg-canvas hover:text-navy transition cursor-pointer"
                        aria-label="Next month"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <div class="seo-cal-grid-7 text-center text-xs font-medium text-navy/60 mb-2">
                    <span>Mo</span>
                    <span>Tu</span>
                    <span>We</span>
                    <span>Th</span>
                    <span>Fr</span>
                    <span>Sa</span>
                    <span>Su</span>
                </div>

                <div id="cal-weeks-grid" class="space-y-1.5"></div>
            </div>

            <!-- Selected Date Project Details Card -->
            <div class="panel p-4 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <p id="cal-selected-heading" class="text-[11px] font-semibold uppercase tracking-wider text-navy/55">
                        Project Details · 12 Nov, 2026
                    </p>
                    <button type="button" id="cal-show-all-btn" class="text-xs font-semibold text-brand hover:underline cursor-pointer">
                        All Deadlines
                    </button>
                </div>
                <div id="cal-deadlines-list" class="space-y-2.5"></div>
            </div>
        </div>

        <!-- Staff To-Do List Widget -->
        <div class="panel overflow-hidden flex flex-col">
            <div class="flex flex-wrap items-center justify-between gap-3 p-5 border-b border-navy/10 bg-canvas/30">
                <div>
                    <h2 class="text-base font-bold text-navy">My To-Do List</h2>
                    <p class="text-xs text-navy/60 mt-0.5">Add tasks for assigned projects and tick them off when done</p>
                </div>
                <span id="todo-progress-badge" class="badge bg-brand/10 text-brand text-xs font-semibold">
                    1 / 4 Completed
                </span>
            </div>

            <div class="p-5 space-y-4 flex-1 flex flex-col">
                <!-- Add To-Do Form -->
                <form id="staff-todo-form" class="space-y-2.5">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input
                            type="text"
                            id="todo-title-input"
                            required
                            placeholder="Add a new task (e.g. Check anchor placement)..."
                            class="flex-1 rounded-xl border border-navy/20 bg-white px-3.5 py-2 text-xs text-navy placeholder:text-navy/40 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                        >
                        <button type="submit" class="btn-primary text-xs px-4 py-2 shrink-0 cursor-pointer">
                            + Add Task
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="todo-project-select" class="text-[11px] font-semibold uppercase tracking-wider text-navy/55 shrink-0">
                            Project:
                        </label>
                        <select
                            id="todo-project-select"
                            class="block w-full rounded-xl border border-navy/20 bg-white px-3 py-1.5 text-xs text-navy focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                        >
                            <option value="General">General SEO Task</option>
                            @foreach ($todoProjects as $project)
                                <option value="{{ $project['id'] }}">{{ $project['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <!-- Filter Controls -->
                <div class="flex items-center justify-between border-y border-navy/10 py-2.5 text-xs">
                    <div class="flex items-center gap-1" id="todo-filter-tabs">
                        <button type="button" data-todo-filter="all" class="todo-filter-btn rounded-lg px-2.5 py-1 font-medium bg-navy text-white cursor-pointer">All</button>
                        <button type="button" data-todo-filter="active" class="todo-filter-btn rounded-lg px-2.5 py-1 font-medium text-navy/70 hover:bg-canvas cursor-pointer">Active</button>
                        <button type="button" data-todo-filter="completed" class="todo-filter-btn rounded-lg px-2.5 py-1 font-medium text-navy/70 hover:bg-canvas cursor-pointer">Completed</button>
                    </div>
                    <button type="button" id="todo-clear-completed" class="text-xs font-semibold text-navy/60 hover:text-rose-600 cursor-pointer">
                        Clear Completed
                    </button>
                </div>

                <!-- Task Items -->
                <ul id="staff-todo-list" class="space-y-2 flex-1"></ul>
                <p id="todo-empty-state" class="hidden py-6 text-center text-xs text-navy/55">
                    No tasks in this view. Add a project task above.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Task Deadline Calendar ---
    const deadlines = @json($calendarDeadlines);
    const assignedOrdersUrl = @json(route('seo.assigned-orders'));
    let currentYear = 2026;
    let currentMonth = 10; // 0-indexed: 10 = November 2026
    let selectedDate = '2026-11-12';

    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    const shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const monthLabel = document.getElementById('cal-month-label');
    const weeksGrid = document.getElementById('cal-weeks-grid');
    const deadlinesList = document.getElementById('cal-deadlines-list');
    const selectedHeading = document.getElementById('cal-selected-heading');

    const pad = (n) => String(n).padStart(2, '0');

    const formatReadableDate = (dateStr) => {
        const [y, m, d] = dateStr.split('-').map(Number);
        return `${d} ${shortMonths[m - 1]}, ${y}`;
    };

    const renderDeadlines = () => {
        const items = selectedDate
            ? deadlines.filter((d) => d.date === selectedDate)
            : deadlines.filter((d) => d.date.startsWith(`${currentYear}-${pad(currentMonth + 1)}`));

        selectedHeading.textContent = selectedDate
            ? `Project Details · ${formatReadableDate(selectedDate)}`
            : `${monthNames[currentMonth]} ${currentYear} Deadlines`;

        deadlinesList.innerHTML = '';
        if (items.length === 0) {
            deadlinesList.innerHTML = `
                <div class="flex items-center justify-between rounded-xl bg-canvas/50 px-4 py-3 text-xs text-navy/60">
                    <span>No project deadline scheduled on ${selectedDate ? formatReadableDate(selectedDate) : 'this month'}.</span>
                </div>
            `;
            return;
        }

        items.forEach((item) => {
            const card = document.createElement('div');
            card.className = 'flex items-center justify-between gap-4 rounded-xl bg-canvas/40 px-4 py-3 border border-navy/5 hover:border-brand/25 transition';

            const left = document.createElement('div');
            left.className = 'flex items-center gap-3 min-w-0';

            const icon = document.createElement('span');
            icon.className = 'grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-navy text-xs font-bold text-white uppercase';
            icon.textContent = (item.website || 'BM').slice(0, 2);

            const info = document.createElement('div');
            info.className = 'min-w-0';

            const title = document.createElement('p');
            title.className = 'truncate text-sm font-bold text-navy';
            title.textContent = item.title;

            const sub = document.createElement('p');
            sub.className = 'mt-0.5 text-xs text-navy/55 truncate';
            sub.textContent = `${formatReadableDate(item.date)} · ${item.customer} · ${item.website}`;

            info.appendChild(title);
            info.appendChild(sub);
            left.appendChild(icon);
            left.appendChild(info);

            const right = document.createElement('div');
            right.className = 'flex items-center gap-3 shrink-0';

            const meta = document.createElement('div');
            meta.className = 'text-right';

            const orderEl = document.createElement('p');
            orderEl.className = 'font-mono text-xs font-bold text-navy';
            orderEl.textContent = item.order_id;

            const statusEl = document.createElement('span');
            statusEl.className = `mt-0.5 inline-flex badge text-[10px] ${item.badge}`;
            statusEl.textContent = item.status;

            meta.appendChild(orderEl);
            meta.appendChild(statusEl);

            const actionLink = document.createElement('a');
            actionLink.href = `${assignedOrdersUrl}?search=${encodeURIComponent(item.order_id)}`;
            actionLink.className = 'grid h-8 w-8 place-items-center rounded-lg text-navy/50 hover:bg-white hover:text-brand transition';
            actionLink.title = 'Open project in Assigned Orders';
            actionLink.innerHTML = `
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 5.5a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 5.5a1.5 1.5 0 110-3 1.5 1.5 0 010 3z" />
                </svg>
            `;

            right.appendChild(meta);
            right.appendChild(actionLink);

            card.appendChild(left);
            card.appendChild(right);
            deadlinesList.appendChild(card);
        });
    };

    const renderCalendar = () => {
        monthLabel.textContent = `${monthNames[currentMonth]} ${currentYear}`;
        weeksGrid.innerHTML = '';

        // Monday-first offset: Sun(0) -> 6, Mon(1) -> 0, ...
        const jsFirstDay = new Date(currentYear, currentMonth, 1).getDay();
        const startOffset = (jsFirstDay + 6) % 7;
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const prevMonthDays = new Date(currentYear, currentMonth, 0).getDate();

        const cells = [];

        // Leading days from previous month
        for (let i = startOffset - 1; i >= 0; i--) {
            const d = prevMonthDays - i;
            const prevM = currentMonth === 0 ? 12 : currentMonth;
            const prevY = currentMonth === 0 ? currentYear - 1 : currentYear;
            cells.push({
                day: d,
                dateStr: `${prevY}-${pad(prevM)}-${pad(d)}`,
                inMonth: false,
            });
        }

        // Current month days
        for (let d = 1; d <= daysInMonth; d++) {
            cells.push({
                day: d,
                dateStr: `${currentYear}-${pad(currentMonth + 1)}-${pad(d)}`,
                inMonth: true,
            });
        }

        // Trailing days for next month
        let nextDay = 1;
        while (cells.length % 7 !== 0) {
            const nextM = currentMonth === 11 ? 1 : currentMonth + 2;
            const nextY = currentMonth === 11 ? currentYear + 1 : currentYear;
            cells.push({
                day: nextDay,
                dateStr: `${nextY}-${pad(nextM)}-${pad(nextDay)}`,
                inMonth: false,
            });
            nextDay++;
        }

        for (let w = 0; w < cells.length; w += 7) {
            const weekCells = cells.slice(w, w + 7);
            const hasSelectedInWeek = weekCells.some((c) => c.dateStr === selectedDate);
            const hasDeadlineInWeek = weekCells.some((c) => deadlines.some((d) => d.date === c.dateStr));

            const weekRow = document.createElement('div');
            weekRow.className = [
                'seo-cal-week',
                hasSelectedInWeek
                    ? 'seo-cal-week--active'
                    : hasDeadlineInWeek
                        ? 'seo-cal-week--has-deadline'
                        : '',
            ].join(' ');

            weekCells.forEach((cell) => {
                const hasDeadline = deadlines.some((d) => d.date === cell.dateStr);
                const isSelected = selectedDate === cell.dateStr;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = [
                    'seo-cal-day',
                    !cell.inMonth ? 'seo-cal-day--muted' : '',
                    isSelected ? 'seo-cal-day--selected' : '',
                ].join(' ');

                const num = document.createElement('span');
                num.textContent = cell.day;
                btn.appendChild(num);

                if (hasDeadline) {
                    const dot = document.createElement('span');
                    dot.className = 'seo-cal-dot';
                    btn.appendChild(dot);
                }

                btn.addEventListener('click', () => {
                    selectedDate = cell.dateStr;
                    renderCalendar();
                    renderDeadlines();
                });

                weekRow.appendChild(btn);
            });

            weeksGrid.appendChild(weekRow);
        }
    };

    document.getElementById('cal-prev-month')?.addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        selectedDate = null;
        renderCalendar();
        renderDeadlines();
    });

    document.getElementById('cal-next-month')?.addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        selectedDate = null;
        renderCalendar();
        renderDeadlines();
    });

    document.getElementById('cal-show-all-btn')?.addEventListener('click', () => {
        selectedDate = null;
        renderCalendar();
        renderDeadlines();
    });

    renderCalendar();
    renderDeadlines();

    // --- Staff To-Do List ---
    let todos = @json($initialTodos);
    let todoFilter = 'all';

    const todoListEl = document.getElementById('staff-todo-list');
    const todoEmptyEl = document.getElementById('todo-empty-state');
    const todoBadgeEl = document.getElementById('todo-progress-badge');
    const todoForm = document.getElementById('staff-todo-form');
    const todoTitleInput = document.getElementById('todo-title-input');
    const todoProjectSelect = document.getElementById('todo-project-select');

    const renderTodos = () => {
        const completedCount = todos.filter((t) => t.completed).length;
        todoBadgeEl.textContent = `${completedCount} / ${todos.length} Completed`;

        const visible = todos.filter((t) => {
            if (todoFilter === 'active') return !t.completed;
            if (todoFilter === 'completed') return t.completed;
            return true;
        });

        todoListEl.innerHTML = '';
        todoEmptyEl.classList.toggle('hidden', visible.length > 0);

        visible.forEach((todo) => {
            const li = document.createElement('li');
            li.className = `flex items-start justify-between gap-3 rounded-xl border p-3 transition ${
                todo.completed
                    ? 'bg-canvas/50 border-navy/5'
                    : 'bg-white border-navy/10 hover:border-brand/30'
            }`;

            const left = document.createElement('label');
            left.className = 'flex items-start gap-3 flex-1 min-w-0 cursor-pointer';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = todo.completed;
            checkbox.className = 'mt-0.5 h-4 w-4 rounded border-navy/30 accent-brand cursor-pointer shrink-0';
            checkbox.addEventListener('change', () => {
                todo.completed = checkbox.checked;
                renderTodos();
            });

            const textWrap = document.createElement('div');
            textWrap.className = 'min-w-0 flex-1';

            const title = document.createElement('p');
            title.className = `text-xs font-medium leading-snug ${
                todo.completed ? 'line-through text-navy/45' : 'text-navy'
            }`;
            title.textContent = todo.title;

            const projectBadge = document.createElement('span');
            projectBadge.className = `mt-1 inline-block rounded px-2 py-0.5 font-mono text-[10px] font-semibold ${
                todo.completed
                    ? 'bg-navy/5 text-navy/40 line-through'
                    : 'bg-brand/10 text-brand'
            }`;
            projectBadge.textContent = todo.project;

            textWrap.appendChild(title);
            textWrap.appendChild(projectBadge);
            left.appendChild(checkbox);
            left.appendChild(textWrap);

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'rounded-lg px-2 py-1 text-xs font-bold text-navy/40 hover:bg-rose-50 hover:text-rose-600 transition cursor-pointer shrink-0';
            delBtn.setAttribute('aria-label', 'Remove task');
            delBtn.textContent = '×';
            delBtn.addEventListener('click', () => {
                todos = todos.filter((t) => t.id !== todo.id);
                renderTodos();
            });

            li.appendChild(left);
            li.appendChild(delBtn);
            todoListEl.appendChild(li);
        });
    };

    todoForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const title = todoTitleInput.value.trim();
        if (!title) return;

        todos.unshift({
            id: Date.now(),
            title,
            project: todoProjectSelect.value || 'General',
            completed: false,
        });

        todoTitleInput.value = '';
        renderTodos();
    });

    document.querySelectorAll('.todo-filter-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            todoFilter = btn.dataset.todoFilter;
            document.querySelectorAll('.todo-filter-btn').forEach((b) => {
                const active = b === btn;
                b.classList.toggle('bg-navy', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('text-navy/70', !active);
                b.classList.toggle('hover:bg-canvas', !active);
            });
            renderTodos();
        });
    });

    document.getElementById('todo-clear-completed')?.addEventListener('click', () => {
        todos = todos.filter((t) => !t.completed);
        renderTodos();
    });

    renderTodos();
});
</script>
@endpush

