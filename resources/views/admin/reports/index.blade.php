@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('content')
    <div class="space-y-6">
        <section>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
            <h1 class="mt-2 text-2xl font-semibold">Reports & Analytics</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">Counts and SEO averages from the records already stored in this system.</p>
        </section>
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Customers</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $customerCount }}</p>
                <p class="mt-1 text-xs text-navy/55">{{ $activeCustomers }} active</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Staff</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $staffCount }}</p>
                <p class="mt-1 text-xs text-navy/55">{{ $activeStaff }} active</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Websites</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $websiteCount }}</p>
                <p class="mt-1 text-xs text-navy/55">{{ $activeWebsites }} active, {{ $pendingWebsites }} pending</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Services</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $serviceCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Niches</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $nicheCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Packages</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $packageCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Active credit prices</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $activeCreditPrices }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Orders / payments</p>
                <p class="mt-3 font-display text-3xl font-semibold">{{ $hasOrders && $hasPayments ? 'Available' : '—' }}</p>
            </article>
        </section>
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Average domain rating</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $metrics?->avg_dr !== null ? number_format((float) $metrics->avg_dr, 1) : '—' }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Average domain authority</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $metrics?->avg_da !== null ? number_format((float) $metrics->avg_da, 1) : '—' }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Average traffic</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $metrics?->avg_traffic !== null ? number_format((float) $metrics->avg_traffic) : '—' }}</p>
            </article>
        </section>
        <section class="panel overflow-hidden">
            <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold">Highest domain ratings</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[640px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Website</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">DR</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">DA</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Traffic</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($topWebsites as $metric)
                            <tr>
                                <td class="px-5 py-3.5 font-medium sm:px-6">
                                    @if ($metric->website)
                                        <a href="{{ route('admin.websites.show', $metric->website) }}" class="hover:text-brand">{{ $metric->website->website_name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $metric->DR }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $metric->DA }}</td>
                                <td class="px-5 py-3.5 text-right tabular-nums sm:px-6">{{ number_format((int) $metric->traffic) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No SEO metrics have been recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <article class="panel overflow-hidden">
                <div class="border-b border-navy/10 px-5 py-4 sm:px-6"><h2 class="text-lg font-semibold">Services</h2></div>
                <ul class="divide-y divide-navy/10">
                    @forelse ($services as $service)
                        <li class="flex items-center justify-between gap-4 px-5 py-3.5 text-sm sm:px-6">
                            <a href="{{ route('admin.services.show', $service) }}" class="font-medium hover:text-brand">{{ $service->service_name }}</a>
                            <span class="tabular-nums text-navy/70">${{ number_format((float) $service->base_price, 2) }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-sm text-navy/60 sm:px-6">No services have been added.</li>
                    @endforelse
                </ul>
            </article>
            <article class="panel overflow-hidden">
                <div class="border-b border-navy/10 px-5 py-4 sm:px-6"><h2 class="text-lg font-semibold">Packages</h2></div>
                <ul class="divide-y divide-navy/10">
                    @forelse ($packages as $package)
                        <li class="flex items-center justify-between gap-4 px-5 py-3.5 text-sm sm:px-6">
                            <a href="{{ route('admin.packages.show', $package) }}" class="font-medium hover:text-brand">{{ $package->package_name }}</a>
                            <span class="tabular-nums text-navy/70">{{ (float) $package->price === 0.0 ? 'Not set' : '$'.number_format((float) $package->price, 2) }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-6 text-sm text-navy/60 sm:px-6">No packages have been added.</li>
                    @endforelse
                </ul>
            </article>
        </section>
    </div>
@endsection
