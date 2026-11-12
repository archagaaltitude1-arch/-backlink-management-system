@extends('layouts.admin')

@section('title', 'Service Management')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
                <h1 class="mt-2 text-2xl font-semibold">Service Management</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">Services available for publisher placements.</p>
            </div>
            <a href="{{ route('admin.services.create') }}" class="btn-primary">Add service</a>
        </section>
        @include('admin.niches.partials.alerts')
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Services</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $total }}</p>
            </article>
        </section>
        <section class="panel overflow-hidden">
            <form method="GET" action="{{ route('admin.services') }}" class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-end sm:px-6">
                <div class="min-w-0 flex-1">
                    <label for="q" class="text-sm font-medium">Search</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search by service name or description" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                </div>
                <button type="submit" class="btn-navy">Apply</button>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Service</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Description</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Base price</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Credits</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($services as $service)
                            <tr class="hover:bg-canvas/80">
                                <td class="px-5 py-3.5 font-medium whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.services.show', $service) }}" class="hover:text-brand">{{ $service->service_name }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-navy/70">{{ $service->description ?: '—' }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">${{ number_format((float) $service->base_price, 2) }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $service->credit_cost }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.services.show', $service) }}" class="font-semibold text-navy hover:text-brand">View</a>
                                    <a href="{{ route('admin.services.edit', $service) }}" class="ml-3 font-semibold text-brand">Edit</a>
                                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}" class="inline" onsubmit="return confirm(@js('Delete '.$service->service_name.'?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 font-semibold text-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No services match this search.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['records' => $services])
        </section>
    </div>
@endsection
