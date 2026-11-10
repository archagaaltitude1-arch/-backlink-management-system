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
                    <p class="mt-1 text-xs text-white/45">Counts and lists on this page come from the database.</p>
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
                    <article class="panel p-5">
                        <p class="text-sm text-navy/60">{{ $stat['label'] }}</p>
                        <p class="mt-3 font-display text-3xl font-semibold tracking-tight text-navy tabular-nums">{{ $stat['value'] }}</p>
                        <p class="mt-2 text-xs text-navy/45">{{ $stat['hint'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="panel overflow-hidden xl:col-span-2">
                <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-semibold">Publisher Websites</h2>
                    <p class="mt-1 text-sm text-navy/60">Latest websites stored in the directory.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[760px] w-full text-left text-sm">
                        <caption class="sr-only">Publisher websites</caption>
                        <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
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

            <div class="panel">
                <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-semibold">Recent Customers</h2>
                    <p class="mt-1 text-sm text-navy/60">Newest customer accounts.</p>
                </div>
                <ul class="divide-y divide-navy/10 px-5 sm:px-6">
                    @forelse ($recentCustomers as $customer)
                        <li class="flex items-center gap-3 py-3.5">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-navy text-xs font-semibold text-white">{{ collect(explode(' ', $customer->name))->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->join('') }}</span>
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
            <div class="panel p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Services</h2>
                <ul class="mt-4 divide-y divide-navy/10">
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

            <div class="panel p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Packages</h2>
                <ul class="mt-4 divide-y divide-navy/10">
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

            <div class="panel p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Niches</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse ($niches as $niche)
                        <span class="rounded-full bg-canvas px-3 py-1 text-xs font-medium text-navy">{{ $niche->niche_name }}</span>
                    @empty
                        <p class="text-sm text-navy/60">No niches yet.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="panel p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Website Overview</h2>
                <p class="mt-1 text-sm text-navy/60">Directory status from stored websites.</p>
                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($websiteSummary as $website)
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
