@extends('layouts.admin')

@section('title', 'Package Management')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
                <h1 class="mt-2 text-2xl font-semibold">Package Management</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">Credit packages and the credit price tiers used with them.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.credit-pricing.create') }}" class="rounded-xl bg-canvas px-4 py-2.5 text-sm font-medium hover:bg-brand/10">Add credit price</a>
                <a href="{{ route('admin.packages.create') }}" class="btn-primary">Add package</a>
            </div>
        </section>
        @include('admin.niches.partials.alerts')
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Packages</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $total }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Active credit prices</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $activePricingCount }}</p>
            </article>
        </section>
        <section class="panel overflow-hidden">
            <form method="GET" action="{{ route('admin.packages') }}" class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-end sm:px-6">
                <div class="min-w-0 flex-1">
                    <label for="q" class="text-sm font-medium">Search packages</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search by package name" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                </div>
                <button type="submit" class="btn-navy">Apply</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-[720px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Package</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Price</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Credits</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($packages as $package)
                            <tr class="hover:bg-canvas/80">
                                <td class="px-5 py-3.5 font-medium sm:px-6">
                                    <a href="{{ route('admin.packages.show', $package) }}" class="hover:text-brand">{{ $package->package_name }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ (float) $package->price === 0.0 ? 'Not set' : '$'.number_format((float) $package->price, 2) }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ (int) $package->credit_amount === 0 ? 'Custom' : $package->credit_amount }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.packages.show', $package) }}" class="font-semibold text-navy hover:text-brand">View</a>
                                    <a href="{{ route('admin.packages.edit', $package) }}" class="ml-3 font-semibold text-brand">Edit</a>
                                    <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="inline" onsubmit="return confirm(@js('Delete '.$package->package_name.'?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 font-semibold text-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No packages match this search.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['records' => $packages])
        </section>
        <section class="panel overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-lg font-semibold">Credit pricing</h2>
                    <p class="mt-1 text-sm text-navy/60">How many credits a customer receives for a listed price.</p>
                </div>
                <a href="{{ route('admin.credit-pricing.create') }}" class="text-sm font-semibold text-brand hover:text-brand-dark">Add credit price</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[640px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Credits</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Price</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($creditPricing as $pricing)
                            <tr>
                                <td class="px-5 py-3.5 text-right tabular-nums sm:px-6">{{ $pricing->credit_amount }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">${{ number_format((float) $pricing->price, 2) }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="badge {{ $pricing->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-canvas text-navy/70' }}">{{ ucfirst($pricing->status) }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.credit-pricing.edit', $pricing) }}" class="font-semibold text-brand">Edit</a>
                                    <form method="POST" action="{{ route('admin.credit-pricing.destroy', $pricing) }}" class="inline" onsubmit="return confirm(@js('Delete the '.$pricing->credit_amount.' credit price?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 font-semibold text-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No credit prices have been added.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
