@extends('layouts.admin')

@section('title', 'Niche Management')

@section('content')
    @php
        $statusStyles = [
            'active' => 'bg-emerald-50 text-emerald-700',
            'inactive' => 'bg-canvas text-navy/70',
        ];
    @endphp

    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Admin</p>
                <h1 class="mt-2 text-2xl font-semibold">Niche Management</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">Content niches used across websites and packages.</p>
            </div>
            <a href="{{ route('admin.niches.create') }}" class="btn-primary">Add niche</a>
        </section>

        @include('admin.niches.partials.alerts')

        <section aria-label="Niche summary" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Total niches</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $nicheCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Websites with a niche</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $linkedWebsiteCount }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Active specifications</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $activeSpecificationCount }}</p>
            </article>
        </section>

        <section class="panel overflow-hidden">
            <form method="GET" action="{{ route('admin.niches') }}" class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-end sm:px-6">
                <div class="min-w-0 flex-1">
                    <label for="q" class="text-sm font-medium">Search</label>
                    <input id="q" name="q" type="search" value="{{ $search }}" placeholder="Search by name or description" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label for="linked" class="text-sm font-medium">Websites</label>
                    <select id="linked" name="linked" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 sm:w-48">
                        <option value="" @selected($linked === '')>All niches</option>
                        <option value="yes" @selected($linked === 'yes')>Linked to websites</option>
                        <option value="no" @selected($linked === 'no')>Not linked</option>
                    </select>
                </div>
                <button type="submit" class="btn-navy">Apply</button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-[760px] w-full text-left text-sm">
                    <caption class="sr-only">Niches</caption>
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Niche</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Description</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Websites</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Added</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($niches as $niche)
                            <tr class="hover:bg-canvas/80">
                                <td class="px-5 py-3.5 font-medium whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.niches.show', $niche) }}" class="hover:text-brand">{{ $niche->niche_name }}</a>
                                </td>
                                <td class="max-w-sm px-4 py-3.5 text-navy/70">{{ $niche->description ?: '—' }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $niche->websites_count }}</td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-navy/70">{{ $niche->created_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.niches.show', $niche) }}" class="font-semibold text-navy hover:text-brand">View</a>
                                    <a href="{{ route('admin.niches.edit', $niche) }}" class="ml-3 font-semibold text-brand hover:text-brand-dark">Edit</a>
                                    @if ($niche->websites_count === 0)
                                        <form method="POST" action="{{ route('admin.niches.destroy', $niche) }}" class="inline" onsubmit="return confirm(@js('Delete '.$niche->niche_name.'?'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-3 font-semibold text-red-700 hover:text-red-800">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No niches match this search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($niches->hasPages())
                <div class="flex flex-col gap-3 border-t border-navy/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <p class="text-sm text-navy/60">Showing {{ $niches->firstItem() }}–{{ $niches->lastItem() }} of {{ $niches->total() }}</p>
                    <div class="flex gap-2">
                        @if ($niches->onFirstPage())
                            <span class="rounded-xl px-3 py-2 text-sm text-navy/35">Previous</span>
                        @else
                            <a href="{{ $niches->previousPageUrl() }}" class="rounded-xl bg-canvas px-3 py-2 text-sm font-medium hover:bg-brand/10">Previous</a>
                        @endif
                        @if ($niches->hasMorePages())
                            <a href="{{ $niches->nextPageUrl() }}" class="rounded-xl bg-canvas px-3 py-2 text-sm font-medium hover:bg-brand/10">Next</a>
                        @else
                            <span class="rounded-xl px-3 py-2 text-sm text-navy/35">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        <section class="panel overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-lg font-semibold">Niche specifications</h2>
                    <p class="mt-1 text-sm text-navy/60">Shared website filters. These rules are not stored against one niche.</p>
                </div>
                <a href="{{ route('admin.niche-specifications.create') }}" class="text-sm font-semibold text-brand hover:text-brand-dark">Add specification</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[680px] w-full text-left text-sm">
                    <caption class="sr-only">Niche specifications</caption>
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Specification</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Field type</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Minimum</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Maximum</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($specifications as $specification)
                            <tr>
                                <td class="px-5 py-3.5 font-medium sm:px-6">{{ $specification->specification_name }}</td>
                                <td class="px-4 py-3.5 capitalize text-navy/70">{{ $specification->field_type }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $specification->min_value ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">{{ $specification->max_value ?? '—' }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="badge {{ $statusStyles[$specification->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($specification->status) }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap sm:px-6">
                                    <a href="{{ route('admin.niche-specifications.edit', $specification) }}" class="font-semibold text-brand hover:text-brand-dark">Edit</a>
                                    <form method="POST" action="{{ route('admin.niche-specifications.destroy', $specification) }}" class="inline" onsubmit="return confirm(@js('Delete '.$specification->specification_name.'?'))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 font-semibold text-red-700">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No specifications have been added.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
