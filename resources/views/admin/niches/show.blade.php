@extends('layouts.admin')

@section('title', $niche->niche_name)

@section('content')
    @php
        $websiteStatus = [
            'active' => 'bg-emerald-50 text-emerald-700',
            'pending' => 'bg-amber-50 text-amber-800',
            'inactive' => 'bg-canvas text-navy/70',
        ];
        $specificationStatus = [
            'active' => 'bg-emerald-50 text-emerald-700',
            'inactive' => 'bg-canvas text-navy/70',
        ];
    @endphp

    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.niches') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Niche Management</a>
                <h1 class="mt-2 text-2xl font-semibold">{{ $niche->niche_name }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">{{ $niche->description ?: 'No description has been added.' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.niches.edit', $niche) }}" class="btn-primary">Edit</a>
                @if ($niche->websites_count === 0)
                    <form method="POST" action="{{ route('admin.niches.destroy', $niche) }}" onsubmit="return confirm(@js('Delete '.$niche->niche_name.'?'))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100">Delete</button>
                    </form>
                @endif
            </div>
        </section>

        @include('admin.niches.partials.alerts')

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Linked websites</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $niche->websites_count }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Added</p>
                <p class="mt-3 font-display text-3xl font-semibold">{{ $niche->created_at?->format('M j, Y') ?? '—' }}</p>
            </article>
        </section>

        <section class="panel overflow-hidden">
            <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold">Websites in this niche</h2>
                @if ($niche->websites_count > 0)
                    <p class="mt-1 text-sm text-navy/60">This niche stays in the catalogue while websites are linked to it.</p>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[640px] w-full text-left text-sm">
                    <caption class="sr-only">Websites in {{ $niche->niche_name }}</caption>
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Website</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Domain</th>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($niche->websites as $website)
                            <tr>
                                <td class="px-5 py-3.5 font-medium sm:px-6">
                                    <a href="{{ route('admin.websites.show', $website) }}" class="hover:text-brand">{{ $website->website_name }}</a>
                                </td>
                                <td class="px-4 py-3.5 text-navy/70">{{ $website->domain }}</td>
                                <td class="px-5 py-3.5 sm:px-6">
                                    <span class="badge {{ $websiteStatus[$website->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($website->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No websites are linked to this niche.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-lg font-semibold">Niche specifications</h2>
                    <p class="mt-1 text-sm text-navy/60">Shared filters available for every niche.</p>
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
                                    <span class="badge {{ $specificationStatus[$specification->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($specification->status) }}</span>
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
