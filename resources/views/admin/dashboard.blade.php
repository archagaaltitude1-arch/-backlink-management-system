@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $adminName = 'Admin User';

        $stats = [
            ['label' => 'Total Customers', 'value' => '128', 'hint' => 'Registered accounts'],
            ['label' => 'Total Staff', 'value' => '16', 'hint' => 'Active team members'],
            ['label' => 'Total Websites', 'value' => '84', 'hint' => 'In the directory'],
            ['label' => 'Total Orders', 'value' => '342', 'hint' => 'All time'],
            ['label' => 'Pending Orders', 'value' => '27', 'hint' => 'Awaiting action'],
            ['label' => 'Completed Orders', 'value' => '291', 'hint' => 'Fulfilled'],
            ['label' => 'Total Revenue', 'value' => '$48,250', 'hint' => 'Sample total', 'featured' => true],
        ];

        $orders = [
            ['id' => 'ORD-10482', 'customer' => 'Priya Fernando', 'service' => 'Guest Post', 'date' => 'Nov 7, 2026', 'amount' => '$240.00', 'status' => 'Completed'],
            ['id' => 'ORD-10481', 'customer' => 'James Okonkwo', 'service' => 'Niche Edit', 'date' => 'Nov 7, 2026', 'amount' => '$180.00', 'status' => 'Pending'],
            ['id' => 'ORD-10480', 'customer' => 'Sofia Alvarez', 'service' => 'Link Insertion', 'date' => 'Nov 6, 2026', 'amount' => '$95.00', 'status' => 'In Progress'],
            ['id' => 'ORD-10479', 'customer' => 'Liam Chen', 'service' => 'Guest Post', 'date' => 'Nov 6, 2026', 'amount' => '$320.00', 'status' => 'Completed'],
            ['id' => 'ORD-10478', 'customer' => 'Amara Singh', 'service' => 'Citation', 'date' => 'Nov 5, 2026', 'amount' => '$410.00', 'status' => 'Pending'],
        ];

        $statusStyles = [
            'Completed' => 'bg-emerald-50 text-emerald-700',
            'Pending' => 'bg-amber-50 text-amber-800',
            'In Progress' => 'bg-sky-50 text-sky-700',
        ];

        $customers = [
            ['initials' => 'NW', 'name' => 'Noah Williams', 'email' => 'noah.williams@example.com', 'date' => 'Nov 8, 2026'],
            ['initials' => 'HS', 'name' => 'Hana Sato', 'email' => 'hana.sato@example.com', 'date' => 'Nov 8, 2026'],
            ['initials' => 'EP', 'name' => 'Elena Petrova', 'email' => 'elena.petrova@example.com', 'date' => 'Nov 7, 2026'],
            ['initials' => 'OH', 'name' => 'Omar Haddad', 'email' => 'omar.haddad@example.com', 'date' => 'Nov 6, 2026'],
        ];

        $websites = [
            ['label' => 'Total Websites', 'value' => '84', 'tone' => 'text-navy'],
            ['label' => 'Active Websites', 'value' => '61', 'tone' => 'text-emerald-700'],
            ['label' => 'Pending Approval', 'value' => '14', 'tone' => 'text-brand'],
            ['label' => 'Inactive Websites', 'value' => '9', 'tone' => 'text-navy/60'],
        ];

        $actions = [
            ['label' => 'Add Website', 'detail' => 'Register a publisher site', 'href' => route('admin.websites')],
            ['label' => 'Add Service', 'detail' => 'Create a backlink service', 'href' => route('admin.services')],
            ['label' => 'Add Package', 'detail' => 'Define a service package', 'href' => route('admin.packages')],
            ['label' => 'View Orders', 'detail' => 'Review the order list', 'href' => route('admin.orders')],
        ];
    @endphp

    <div class="space-y-6">
        <section class="overflow-hidden rounded-2xl bg-navy text-white shadow-sm">
            <div class="h-1.5 bg-brand"></div>
            <div class="flex flex-col gap-6 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-medium tracking-wide text-brand uppercase">Welcome, Admin</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $adminName }}</h1>
                    <p class="mt-2 text-sm text-white/70">{{ now()->format('l, F j, Y') }}</p>
                    <p class="mt-1 text-xs text-white/45">Figures on this page are sample data.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.websites') }}" class="btn-primary">Add Website</a>
                    <a href="{{ route('admin.services') }}" class="btn-inverse">Add Service</a>
                    <a href="{{ route('admin.packages') }}" class="btn-inverse">Add Package</a>
                    <a href="{{ route('admin.orders') }}" class="btn-inverse">View Orders</a>
                </div>
            </div>
        </section>

        <section aria-label="Statistics">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($stats as $stat)
                    <article @class(['panel p-5', 'ring-brand/40' => ! empty($stat['featured'])])>
                        <p class="text-sm text-navy/60">{{ $stat['label'] }}</p>
                        <p @class([
                            'mt-3 font-display text-3xl font-semibold tracking-tight tabular-nums',
                            'text-brand' => ! empty($stat['featured']),
                            'text-navy' => empty($stat['featured']),
                        ])>{{ $stat['value'] }}</p>
                        <p class="mt-2 text-xs text-navy/45">{{ $stat['hint'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="panel overflow-hidden xl:col-span-2">
                <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-semibold">Recent Orders</h2>
                    <p class="mt-1 text-sm text-navy/60">Latest sample orders across services.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[760px] w-full text-left text-sm">
                        <caption class="sr-only">Recent orders</caption>
                        <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Order ID</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Customer Name</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Service</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Order Date</th>
                                <th scope="col" class="px-4 py-3 text-right font-semibold">Amount</th>
                                <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy/10">
                            @foreach ($orders as $order)
                                <tr class="hover:bg-canvas/80">
                                    <td class="px-5 py-3.5 font-medium whitespace-nowrap sm:px-6">{{ $order['id'] }}</td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">{{ $order['customer'] }}</td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">{{ $order['service'] }}</td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-navy/70">{{ $order['date'] }}</td>
                                    <td class="px-4 py-3.5 text-right font-medium whitespace-nowrap tabular-nums">{{ $order['amount'] }}</td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <span class="badge {{ $statusStyles[$order['status']] ?? 'bg-canvas text-navy' }}">{{ $order['status'] }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right sm:px-6">
                                        <button type="button" class="text-sm font-semibold text-brand hover:text-brand-dark" aria-label="View order {{ $order['id'] }}">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-semibold">Recent Customer Registrations</h2>
                    <p class="mt-1 text-sm text-navy/60">Newest sample accounts.</p>
                </div>
                <ul class="divide-y divide-navy/10 px-5 sm:px-6">
                    @foreach ($customers as $customer)
                        <li class="flex items-center gap-3 py-3.5">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-navy text-xs font-semibold text-white">{{ $customer['initials'] }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ $customer['name'] }}</p>
                                <p class="truncate text-xs text-navy/55">{{ $customer['email'] }}</p>
                            </div>
                            <p class="ml-auto shrink-0 text-xs text-navy/45">{{ $customer['date'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="panel p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Website Overview</h2>
                <p class="mt-1 text-sm text-navy/60">Directory status from sample records.</p>
                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($websites as $website)
                        <div class="rounded-xl bg-canvas px-4 py-4">
                            <p class="text-sm text-navy/60">{{ $website['label'] }}</p>
                            <p class="mt-2 font-display text-2xl font-semibold tabular-nums {{ $website['tone'] }}">{{ $website['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="quick-actions" class="panel p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Quick Actions</h2>
                <p class="mt-1 text-sm text-navy/60">Shortcuts for the modules you own.</p>
                <div class="mt-5 grid gap-3">
                    @foreach ($actions as $action)
                        <a
                            href="{{ $action['href'] }}"
                            class="flex w-full items-center gap-4 rounded-xl bg-canvas px-4 py-4 text-left transition hover:bg-brand/10"
                        >
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand text-lg font-semibold text-white" aria-hidden="true">+</span>
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
