@extends('layouts.admin')

@section('title', 'Coupon Management')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
                <h1 class="mt-2 text-2xl font-semibold">Coupon Management</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">Create coupon codes, choose who receives them, and review how much discount each code has given.</p>
            </div>
            <a href="{{ route('admin.coupons.create') }}" class="btn-primary">Add coupon</a>
        </section>
        @include('admin.niches.partials.alerts')
        @include('admin.coupons.partials.campaigns')
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Coupons</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $total }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Active</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $activeCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Discount given</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">${{ number_format($discountTotal, 2) }}</p>
            </article>
        </section>
        <section class="panel overflow-hidden">
            <form method="GET" action="{{ route('admin.coupons') }}" class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-end sm:px-6">
                <div class="min-w-0 flex-1">
                    <label for="q" class="text-sm font-medium">Search coupons</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search by code" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label for="view" class="text-sm font-medium">Show</label>
                    <select id="view" name="view" class="mt-2 rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm">
                        <option value="">All coupons</option>
                        <option value="active" @selected($view === 'active')>Active</option>
                        <option value="expired" @selected($view === 'expired')>Expired</option>
                    </select>
                </div>
                <button type="submit" class="btn-navy">Apply</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-[860px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Code</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Discount</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Packages</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Uses</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Discount given</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($coupons as $coupon)
                            <tr class="hover:bg-canvas/80">
                                <td class="px-5 py-3.5 font-medium sm:px-6">
                                    <a href="{{ route('admin.coupons.show', $coupon) }}" class="hover:text-brand">{{ $coupon->code }}</a>
                                </td>
                                <td class="px-4 py-3.5">{{ $coupon->discountSummary() }}</td>
                                <td class="px-4 py-3.5">{{ $coupon->packageLabel() }}</td>
                                <td class="px-4 py-3.5">{{ $coupon->expires_at?->isPast() ? 'Expired' : ($coupon->is_active ? 'Active' : 'Inactive') }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $coupon->usages_count }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">${{ number_format((float) $coupon->discount_total, 2) }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.coupons.show', $coupon) }}" class="font-semibold text-navy hover:text-brand">View</a>
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="ml-3 font-semibold text-brand">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No coupons match this view.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['records' => $coupons])
        </section>
    </div>
@endsection
