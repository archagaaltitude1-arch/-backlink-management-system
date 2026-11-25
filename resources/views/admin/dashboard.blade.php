@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $statusStyles = [
            'active' => 'bg-emerald-50 text-emerald-700',
            'pending' => 'bg-amber-50 text-amber-800',
            'inactive' => 'bg-canvas text-navy/70',
            'suspended' => 'bg-red-50 text-red-700',
        ];

        $actions = [
            ['label' => 'Add Website', 'detail' => 'Register a publisher site', 'href' => route('admin.websites'), 'mark' => 'bg-brand'],
            ['label' => 'Add Service', 'detail' => 'Create a backlink service', 'href' => route('admin.services'), 'mark' => 'bg-navy'],
            ['label' => 'Add Package', 'detail' => 'Define a service package', 'href' => route('admin.packages'), 'mark' => 'bg-emerald-600'],
            ['label' => 'View Orders', 'detail' => 'Review the order list', 'href' => route('admin.orders'), 'mark' => 'bg-sky-600'],
        ];
        $statTones = [
            ['bar' => 'border-t-brand', 'wash' => 'from-brand/10'],
            ['bar' => 'border-t-navy', 'wash' => 'from-navy/10'],
            ['bar' => 'border-t-emerald-500', 'wash' => 'from-emerald-50'],
            ['bar' => 'border-t-sky-500', 'wash' => 'from-sky-50'],
            ['bar' => 'border-t-amber-500', 'wash' => 'from-amber-50'],
            ['bar' => 'border-t-violet-500', 'wash' => 'from-violet-50'],
            ['bar' => 'border-t-rose-500', 'wash' => 'from-rose-50'],
            ['bar' => 'border-t-sky-700', 'wash' => 'from-sky-50'],
        ];
        $nicheTones = ['bg-brand/10 text-brand', 'bg-sky-50 text-sky-800', 'bg-emerald-50 text-emerald-800', 'bg-amber-50 text-amber-800', 'bg-violet-50 text-violet-800'];
        $avatarTones = ['bg-brand', 'bg-navy', 'bg-emerald-600', 'bg-sky-600', 'bg-violet-600'];
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
                {{ session('success') }}
            </div>
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

        <section class="overflow-hidden rounded-2xl bg-navy text-white shadow-[0_18px_40px_rgba(21,32,41,0.16)]">
            <div class="h-1.5 bg-brand"></div>
            <div class="flex flex-col gap-6 bg-[radial-gradient(ellipse_at_0%_0%,rgba(255,73,23,0.22),transparent_42%)] p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-medium tracking-wide text-brand uppercase">Welcome, Admin</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $adminName }}</h1>
                    <p class="mt-3 inline-flex min-h-8 items-center rounded-full bg-white/10 px-3 text-sm text-white/80">{{ now()->format('l, F j, Y') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.websites') }}" class="btn-primary">Add Website</a>
                    <a href="{{ route('admin.services') }}" class="btn-inverse">Add Service</a>
                    <a href="{{ route('admin.packages') }}" class="btn-inverse">Add Package</a>
                    <a href="{{ route('admin.orders') }}" class="btn-inverse">View Orders</a>
                </div>
            </div>
        </section>

        <section class="panel overflow-hidden">
            <div class="flex flex-col gap-3 border-t-[3px] border-t-amber-500 bg-gradient-to-r from-amber-50 to-transparent px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-amber-800 uppercase">Queue</p>
                    <h2 class="mt-1 text-lg font-semibold">Customer orders</h2>
                    <p class="mt-1 text-sm text-navy/60">Paid orders arrive here for approval. After you approve an order, assign each placement to an SEO staff member.</p>
                </div>
                <p class="flex flex-wrap items-center gap-2 text-sm text-navy/70">
                    <span>Active staff:</span>
                    @forelse ($assignmentStaff as $member)
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">{{ $member->name }}</span>
                    @empty
                        <span>none yet</span>
                    @endforelse
                </p>
            </div>
            @foreach ($awaitingApprovalOrders as $approvalOrder)
                <form method="POST" action="{{ route('admin.orders.approve', $approvalOrder) }}" class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    @csrf
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-medium">{{ $approvalOrder->order_number }}</p>
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-800">Awaiting approval</span>
                        </div>
                        <p class="mt-1 text-xs text-navy/55">{{ $approvalOrder->customer?->name ?? 'Customer' }} · Payment complete · Approve before SEO assignment</p>
                    </div>
                    <button type="submit" class="btn-primary">Approve order</button>
                </form>
            @endforeach
            @forelse ($unassignedItems as $item)
                <form method="POST" action="{{ route('admin.orders.assign', [$item->order, $item]) }}" class="grid gap-3 border-b border-navy/10 px-5 py-4 last:border-b-0 sm:px-6 lg:grid-cols-[minmax(0,1fr)_16rem_auto] lg:items-end">
                    @csrf
                    <div class="min-w-0">
                        <p class="font-medium">{{ $item->order?->order_number ?? 'Order' }} · {{ $item->item_name }}</p>
                        <p class="mt-1 truncate text-xs text-navy/55">{{ $item->order?->customer?->name ?? 'Guest checkout' }}</p>
                    </div>
                    <div>
                        <label for="dashboard-staff-{{ $item->id }}" class="text-sm font-medium">SEO staff</label>
                        <select id="dashboard-staff-{{ $item->id }}" name="staff_id" required class="mt-2 w-full rounded-xl border border-navy/15 bg-[#F7F8FA] px-4 py-2.5 text-sm text-navy outline-none focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/20" @disabled($assignmentStaff->isEmpty())>
                            <option value="" disabled @selected(old('staff_id') === null)>Choose a staff member</option>
                            @foreach ($assignmentStaff as $member)
                                <option value="{{ $member->id }}" @selected((string) old('staff_id') === (string) $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-navy" @disabled($assignmentStaff->isEmpty())>Assign to SEO staff</button>
                </form>
            @empty
                <div class="grid gap-3 px-5 py-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_16rem_auto] lg:items-end">
                    <p class="text-sm text-navy/60">No approved orders are waiting for SEO staff. Approve a paid order before assigning the work.</p>
                    <div>
                        <label for="dashboard-staff-empty" class="text-sm font-medium">SEO staff</label>
                        <select id="dashboard-staff-empty" class="mt-2 w-full rounded-xl border border-navy/15 bg-[#F7F8FA] px-4 py-2.5 text-sm text-navy/45 outline-none" disabled>
                            <option>Choose a staff member</option>
                        </select>
                    </div>
                    <button type="button" class="btn-navy" disabled>Assign to SEO staff</button>
                </div>
            @endforelse
        </section>

        <section class="panel overflow-hidden" id="blog-reviews">
            <div class="flex flex-col gap-3 border-t-[3px] border-t-violet-500 bg-gradient-to-r from-violet-50 to-transparent px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                <div>
                    <p class="text-xs font-semibold tracking-wider text-violet-700 uppercase">Reviews</p>
                    <h2 class="mt-1 text-lg font-semibold">Blog</h2>
                    <p class="mt-1 text-sm text-navy/60">Blogs sent from the SEO staff Articles page. Review, approve, or reject them here.</p>
                </div>
                <a href="{{ route('admin.articles') }}" class="text-sm font-semibold text-violet-700">All blog reviews</a>
            </div>
            @forelse ($pendingBlogs as $article)
                <form method="POST" action="{{ route('admin.articles.decide', $article) }}" class="grid gap-3 border-b border-navy/10 px-5 py-4 last:border-b-0 sm:px-6">
                    @csrf
                    <div class="min-w-0">
                        <p class="font-medium">{{ $article->title }}</p>
                        <p class="mt-1 text-xs text-navy/55">{{ $article->staff?->name ?? 'SEO staff' }} · {{ $article->orderItem?->order?->order_number ?? 'Order' }}</p>
                        <p class="mt-2 text-sm leading-6 text-navy/70">{{ \Illuminate\Support\Str::limit($article->body, 220) }}</p>
                    </div>
                    <div>
                        <label for="blog-feedback-{{ $article->id }}" class="text-sm font-medium">Reviewer feedback</label>
                        <textarea id="blog-feedback-{{ $article->id }}" name="feedback" rows="3" placeholder="Example: Move the link into the second paragraph and use the anchor &quot;guest post services&quot;." class="mt-2 w-full rounded-xl border border-navy/15 bg-[#F7F8FA] px-4 py-3 text-sm leading-6 text-navy outline-none placeholder:text-navy/40 focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/20">{{ (string) old('_article_id') === (string) $article->id ? old('feedback') : '' }}</textarea>
                    </div>
                    <input type="hidden" name="_article_id" value="{{ $article->id }}">
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" name="decision" value="review" class="btn bg-white text-navy ring-1 ring-navy/15 hover:bg-canvas">Review</button>
                        <button type="submit" name="decision" value="rejected" class="btn bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">Reject</button>
                        <button type="submit" name="decision" value="approve" class="btn-primary">Approval</button>
                    </div>
                </form>
            @empty
                <p class="px-5 py-6 text-sm text-navy/60 sm:px-6">No blogs are waiting. SEO staff send a blog from their own Articles page.</p>
            @endforelse
        </section>

        @if ($insights)
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('admin.reports') }}" class="panel border-t-[3px] border-t-brand bg-gradient-to-br from-brand/10 to-white p-5 transition hover:-translate-y-0.5 hover:shadow-[0_14px_32px_rgba(21,32,41,0.08)]">
                    <p class="text-sm text-navy/60">Reports this month</p>
                    <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $insights['monthOrders'] }}</p>
                    <p class="mt-2 text-xs text-navy/55">{{ $insights['todayOrders'] }} today · {{ $insights['weekOrders'] }} this week · ${{ number_format($insights['monthRevenue'], 2) }} revenue</p>
                </a>
                <a href="{{ route('admin.staff-performance') }}" class="panel border-t-[3px] border-t-emerald-500 bg-gradient-to-br from-emerald-50 to-white p-5 transition hover:-translate-y-0.5 hover:shadow-[0_14px_32px_rgba(21,32,41,0.08)]">
                    <p class="text-sm text-navy/60">SEO staff this month</p>
                    <ul class="mt-3 space-y-1 text-sm">
                        @forelse ($insights['staff'] as $member)
                            <li class="flex justify-between gap-3"><span class="truncate">{{ $member['name'] }}</span><span class="tabular-nums text-navy/60">{{ $member['completed'] }}/{{ $member['assigned'] }}</span></li>
                        @empty
                            <li class="text-navy/60">No staff accounts yet.</li>
                        @endforelse
                    </ul>
                </a>
                <a href="{{ route('admin.notifications') }}" class="panel border-t-[3px] border-t-amber-500 bg-gradient-to-br from-amber-50 to-white p-5 transition hover:-translate-y-0.5 hover:shadow-[0_14px_32px_rgba(21,32,41,0.08)]">
                    <p class="text-sm text-navy/60">Admin notifications</p>
                    <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $insights['unread'] }}</p>
                    <p class="mt-2 text-xs text-navy/55">Unread updates for your admin account</p>
                </a>
                <a href="{{ route('admin.customer-statistics') }}" class="panel border-t-[3px] border-t-sky-500 bg-gradient-to-br from-sky-50 to-white p-5 transition hover:-translate-y-0.5 hover:shadow-[0_14px_32px_rgba(21,32,41,0.08)]">
                    <p class="text-sm text-navy/60">Customers</p>
                    <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $insights['customerTotal'] }}</p>
                    <p class="mt-2 text-xs text-navy/55">{{ $insights['customerMonth'] }} new this month · {{ $insights['customerActive'] }} active</p>
                </a>
            </section>
        @endif

        <section aria-label="Statistics">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($stats as $stat)
                    @php $tone = $statTones[$loop->index % count($statTones)]; @endphp
                    <article class="panel overflow-hidden border-t-[3px] {{ $tone['bar'] }} bg-gradient-to-br {{ $tone['wash'] }} to-white p-5">
                        <p class="text-sm text-navy/60">{{ $stat['label'] }}</p>
                        <p class="mt-3 font-display text-3xl font-semibold tracking-tight text-navy tabular-nums">{{ $stat['value'] }}</p>
                        <p class="mt-2 text-xs text-navy/45">{{ $stat['hint'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="panel overflow-hidden">
                <div class="flex items-center justify-between gap-4 border-t-[3px] border-t-amber-500 bg-gradient-to-r from-amber-50 to-transparent px-5 py-4 sm:px-6">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-amber-800 uppercase">Publishing</p>
                        <h2 class="mt-1 text-lg font-semibold">Assignment queue</h2>
                        <p class="mt-1 text-sm text-navy/60">Who is publishing each customer order.</p>
                    </div>
                    <a href="{{ route('admin.orders') }}" class="text-sm font-semibold text-amber-800">Assign orders</a>
                </div>
                <ul class="divide-y divide-navy/10">
                    @forelse ($recentOrders as $order)
                        @php
                            $needsStaff = $order->payment_status === 'paid'
                                && in_array($order->order_status, ['processing', 'completed'], true)
                                && ($order->items->isEmpty() || $order->items->contains(fn ($item) => $item->assignment === null));
                            $assignees = $order->items->map(fn ($item) => $item->assignment?->staff?->name)->filter()->unique()->join(', ');
                            $queueLabel = match (true) {
                                $order->payment_status !== 'paid' => 'Awaiting payment',
                                $order->order_status === 'awaiting_approval' => 'Awaiting approval',
                                $needsStaff => 'Needs SEO staff',
                                default => $assignees !== '' ? $assignees : 'Assigned',
                            };
                            $queueTone = match ($queueLabel) {
                                'Awaiting payment' => 'bg-canvas text-navy/70',
                                'Awaiting approval', 'Needs SEO staff' => 'bg-amber-50 text-amber-800',
                                default => 'bg-emerald-50 text-emerald-800',
                            };
                        @endphp
                        <li class="flex items-center justify-between gap-4 px-5 py-3.5 text-sm sm:px-6">
                            <div class="min-w-0">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-medium hover:text-brand">{{ $order->order_number }}</a>
                                <p class="truncate text-xs text-navy/55">{{ $order->customer?->name ?? 'Guest checkout' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $queueTone }}">{{ $queueLabel }}</p>
                                <a href="{{ route('admin.orders') }}" class="mt-1 block text-xs font-semibold text-brand">Assign</a>
                            </div>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-sm text-navy/60 sm:px-6">No customer orders have arrived yet.</li>
                    @endforelse
                </ul>
            </div>
            <div class="panel overflow-hidden">
                <div class="flex items-center justify-between gap-4 border-t-[3px] border-t-emerald-500 bg-gradient-to-r from-emerald-50 to-transparent px-5 py-4 sm:px-6">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-emerald-700 uppercase">Money in</p>
                        <h2 class="mt-1 text-lg font-semibold">Recent payments</h2>
                        <p class="mt-1 text-sm text-navy/60">Latest payment records.</p>
                    </div>
                    <a href="{{ route('admin.payments') }}" class="text-sm font-semibold text-emerald-700">View all</a>
                </div>
                <ul class="divide-y divide-navy/10">
                    @forelse ($recentPayments as $payment)
                        @php
                            $paymentTone = match ($payment->status) {
                                'successful' => 'bg-emerald-50 text-emerald-700',
                                'pending' => 'bg-amber-50 text-amber-800',
                                'failed' => 'bg-rose-50 text-rose-700',
                                default => 'bg-canvas text-navy/70',
                            };
                        @endphp
                        <li class="flex items-center justify-between gap-4 px-5 py-3.5 text-sm sm:px-6">
                            <div class="min-w-0">
                                <a href="{{ route('admin.payments.show', $payment) }}" class="font-medium hover:text-brand">{{ $payment->transaction_id }}</a>
                                <p class="truncate text-xs text-navy/55">{{ $payment->order?->order_number ?? $payment->customer?->name ?? 'Account payment' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium tabular-nums">${{ number_format((float) $payment->amount, 2) }}</p>
                                <p class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $paymentTone }}">{{ ucfirst($payment->status) }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-sm text-navy/60 sm:px-6">No payments have been recorded yet.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="panel overflow-hidden xl:col-span-2">
                <div class="border-t-[3px] border-t-brand bg-gradient-to-r from-brand/10 to-transparent px-5 py-4 sm:px-6">
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Directory</p>
                    <h2 class="mt-1 text-lg font-semibold">Publisher Websites</h2>
                    <p class="mt-1 text-sm text-navy/60">Latest websites stored in the directory.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[760px] w-full text-left text-sm">
                        <caption class="sr-only">Publisher websites</caption>
                        <thead class="bg-navy text-xs tracking-wide text-white/70 uppercase">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Website</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Domain</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Niches</th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold">Price</th>
                                <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy/10">
                            @forelse ($recentWebsites as $website)
                                <tr class="hover:bg-canvas/80">
                                    <td class="px-5 py-3.5 font-medium whitespace-nowrap sm:px-6">{{ $website->website_name }}</td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-navy/70">{{ $website->domain }}</td>
                                    <td class="px-4 py-3.5">{{ $website->niches->pluck('niche_name')->join(', ') ?: '—' }}</td>
                                    <td class="px-4 py-3.5 text-right font-medium whitespace-nowrap tabular-nums">${{ number_format((float) $website->price, 2) }}</td>
                                    <td class="px-5 py-3.5 whitespace-nowrap sm:px-6">
                                        <span class="badge {{ $statusStyles[$website->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($website->status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-sm text-navy/60 sm:px-6">No websites have been added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-sky-500 bg-gradient-to-r from-sky-50 to-transparent px-5 py-4 sm:px-6">
                    <p class="text-xs font-semibold tracking-wider text-sky-700 uppercase">Accounts</p>
                    <h2 class="mt-1 text-lg font-semibold">Recent Customers</h2>
                    <p class="mt-1 text-sm text-navy/60">Newest customer accounts.</p>
                </div>
                <ul class="divide-y divide-navy/10 px-5 sm:px-6">
                    @forelse ($recentCustomers as $customer)
                        <li class="flex items-center gap-3 py-3.5">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full text-xs font-semibold text-white {{ $avatarTones[$loop->index % count($avatarTones)] }}">{{ collect(explode(' ', $customer->name))->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->join('') }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ $customer->name }}</p>
                                <p class="truncate text-xs text-navy/55">{{ $customer->email }}</p>
                            </div>
                            <p class="ml-auto shrink-0 text-xs text-navy/45">{{ $customer->created_at?->format('M j, Y') ?? '—' }}</p>
                        </li>
                    @empty
                        <li class="py-6 text-sm text-navy/60">No customer accounts yet.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-brand bg-gradient-to-r from-brand/10 to-transparent px-5 py-4 sm:px-6">
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Catalogue</p>
                    <h2 class="mt-1 text-lg font-semibold">Services</h2>
                </div>
                <ul class="divide-y divide-navy/10 px-5 sm:px-6">
                    @forelse ($services as $service)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <span class="text-sm font-medium">{{ $service->service_name }}</span>
                            <span class="text-sm text-navy/60 tabular-nums">${{ number_format((float) $service->base_price, 2) }}</span>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-navy/60">No services yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-sky-500 bg-gradient-to-r from-sky-50 to-transparent px-5 py-4 sm:px-6">
                    <p class="text-xs font-semibold tracking-wider text-sky-700 uppercase">Catalogue</p>
                    <h2 class="mt-1 text-lg font-semibold">Packages</h2>
                </div>
                <ul class="divide-y divide-navy/10 px-5 sm:px-6">
                    @forelse ($packages as $package)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <span>
                                <span class="block text-sm font-medium">{{ $package->package_name }}</span>
                                <span class="block text-xs text-navy/55">{{ $package->credit_amount > 0 ? $package->credit_amount.' backlinks' : 'Custom campaign' }}</span>
                            </span>
                            <span class="text-sm text-navy/60 tabular-nums">{{ (float) $package->price > 0 ? '$'.number_format((float) $package->price, 2) : 'Not set' }}</span>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-navy/60">No packages yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-emerald-500 bg-gradient-to-r from-emerald-50 to-transparent px-5 py-4 sm:px-6">
                    <p class="text-xs font-semibold tracking-wider text-emerald-700 uppercase">Catalogue</p>
                    <h2 class="mt-1 text-lg font-semibold">Niches</h2>
                </div>
                <div class="flex flex-wrap gap-2 p-5 sm:p-6">
                    @forelse ($niches as $niche)
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $nicheTones[$loop->index % count($nicheTones)] }}">{{ $niche->niche_name }}</span>
                    @empty
                        <p class="text-sm text-navy/60">No niches yet.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-navy bg-gradient-to-r from-navy/10 to-transparent px-5 py-4 sm:px-6">
                    <p class="text-xs font-semibold tracking-wider text-navy/70 uppercase">Directory</p>
                    <h2 class="mt-1 text-lg font-semibold">Website Overview</h2>
                    <p class="mt-1 text-sm text-navy/60">Directory status from stored websites.</p>
                </div>
                <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 sm:p-6">
                    @foreach ($websiteSummary as $website)
                        @php
                            $summaryWash = match (true) {
                                str_contains($website['tone'], 'emerald') => 'border-t-emerald-500 bg-emerald-50',
                                str_contains($website['tone'], 'brand') => 'border-t-brand bg-brand/10',
                                str_contains($website['tone'], 'navy/60') => 'border-t-navy/30 bg-canvas',
                                default => 'border-t-navy bg-sky-50',
                            };
                        @endphp
                        <div class="overflow-hidden rounded-xl border-t-[3px] px-4 py-4 {{ $summaryWash }}">
                            <p class="text-sm text-navy/60">{{ $website['label'] }}</p>
                            <p class="mt-2 font-display text-2xl font-semibold tabular-nums {{ $website['tone'] }}">{{ $website['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="quick-actions" class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-brand bg-gradient-to-r from-brand/10 to-transparent px-5 py-4 sm:px-6">
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Shortcuts</p>
                    <h2 class="mt-1 text-lg font-semibold">Quick Actions</h2>
                    <p class="mt-1 text-sm text-navy/60">Shortcuts for the modules you own.</p>
                </div>
                <div class="grid gap-3 p-5 sm:p-6">
                    @foreach ($actions as $action)
                        <a
                            href="{{ $action['href'] }}"
                            class="flex w-full items-center gap-4 rounded-xl bg-[#F7F8FA] px-4 py-4 text-left ring-1 ring-navy/10 transition hover:bg-brand/10"
                        >
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-lg font-semibold text-white {{ $action['mark'] }}" aria-hidden="true">+</span>
                            <span>
                                <span class="block text-sm font-semibold">{{ $action['label'] }}</span>
                                <span class="mt-0.5 block text-xs text-navy/55">{{ $action['detail'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
