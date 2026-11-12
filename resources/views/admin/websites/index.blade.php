@extends('layouts.admin')

@section('title', 'Website Management')

@section('content')
    @php
        $statusStyles = [
            'active' => 'bg-emerald-50 text-emerald-700',
            'pending' => 'bg-amber-50 text-amber-800',
            'inactive' => 'bg-canvas text-navy/70',
        ];
    @endphp
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
                <h1 class="mt-2 text-2xl font-semibold">Website Management</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">Publisher websites, niches, and SEO metrics.</p>
            </div>
            <a href="{{ route('admin.websites.create') }}" class="btn-primary">Add website</a>
        </section>
        @include('admin.niches.partials.alerts')
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-5"><p class="text-sm text-navy/60">Total</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $total }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">Active</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums text-emerald-700">{{ $activeCount }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">Pending</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums text-brand">{{ $pendingCount }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">Inactive</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $inactiveCount }}</p></article>
        </section>
        <section class="panel overflow-hidden">
            <form method="GET" action="{{ route('admin.websites') }}" class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-end sm:px-6">
                <div class="min-w-0 flex-1">
                    <label for="q" class="text-sm font-medium">Search</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search by name, domain, or URL" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label for="status" class="text-sm font-medium">Status</label>
                    <select id="status" name="status" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 sm:w-40">
                        <option value="" @selected($status === '')>All statuses</option>
                        @foreach (['active', 'pending', 'inactive'] as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-navy">Apply</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-[860px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Website</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Domain</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Niches</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Price</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($websites as $website)
                            <tr class="hover:bg-canvas/80">
                                <td class="px-5 py-3.5 font-medium sm:px-6"><a href="{{ route('admin.websites.show', $website) }}" class="hover:text-brand">{{ $website->website_name }}</a></td>
                                <td class="px-4 py-3.5 text-navy/70">{{ $website->domain }}</td>
                                <td class="px-4 py-3.5">{{ $website->niches->pluck('niche_name')->join(', ') ?: '—' }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">${{ number_format((float) $website->price, 2) }}</td>
                                <td class="px-4 py-3.5"><span class="badge {{ $statusStyles[$website->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($website->status) }}</span></td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.websites.show', $website) }}" class="font-semibold text-navy hover:text-brand">View</a>
                                    <a href="{{ route('admin.websites.edit', $website) }}" class="ml-3 font-semibold text-brand">Edit</a>
                                    <form method="POST" action="{{ route('admin.websites.destroy', $website) }}" class="inline" onsubmit="return confirm(@js('Delete '.$website->website_name.' and its SEO metrics?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 font-semibold text-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No websites match this search.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['records' => $websites])
        </section>
    </div>
@endsection
