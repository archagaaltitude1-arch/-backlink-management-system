@extends('layouts.admin')

@section('title', $website->website_name)

@section('content')
    @php
        $statusStyles = [
            'active' => 'bg-emerald-50 text-emerald-700',
            'pending' => 'bg-amber-50 text-amber-800',
            'inactive' => 'bg-canvas text-navy/70',
        ];
        $metric = $website->seoMetric;
    @endphp
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.websites') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Website Management</a>
                <h1 class="mt-2 text-2xl font-semibold">{{ $website->website_name }}</h1>
                <p class="mt-2 text-sm text-navy/70">{{ $website->url }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.websites.edit', $website) }}" class="btn-primary">Edit</a>
                <form method="POST" action="{{ route('admin.websites.destroy', $website) }}" onsubmit="return confirm(@js('Delete '.$website->website_name.' and its SEO metrics?'))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100">Delete</button>
                </form>
            </div>
        </section>
        @include('admin.niches.partials.alerts')
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="panel p-5"><p class="text-sm text-navy/60">Domain</p><p class="mt-3 font-semibold">{{ $website->domain }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">Price</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">${{ number_format((float) $website->price, 2) }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">Credit cost</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $website->credit_cost }}</p></article>
            <article class="panel p-5"><p class="text-sm text-navy/60">Status</p><p class="mt-3"><span class="badge {{ $statusStyles[$website->status] ?? 'bg-canvas text-navy' }}">{{ ucfirst($website->status) }}</span></p></article>
        </section>
        <section class="panel p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Niches</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @forelse ($website->niches as $niche)
                    <a href="{{ route('admin.niches.show', $niche) }}" class="rounded-full bg-canvas px-3 py-1 text-xs font-medium hover:bg-brand/10">{{ $niche->niche_name }}</a>
                @empty
                    <p class="text-sm text-navy/60">No niches are linked.</p>
                @endforelse
            </div>
        </section>
        <section class="panel overflow-hidden">
            <div class="border-b border-navy/10 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold">SEO metrics</h2>
            </div>
            <div class="grid grid-cols-2 gap-4 p-5 sm:grid-cols-4 sm:p-6">
                @foreach (['DR' => 'DR', 'DA' => 'DA', 'TF' => 'TF', 'CF' => 'CF', 'traffic' => 'Traffic', 'spam_score' => 'Spam score', 'backlinks' => 'Backlinks'] as $field => $label)
                    <div class="rounded-xl bg-canvas px-4 py-4">
                        <p class="text-sm text-navy/60">{{ $label }}</p>
                        <p class="mt-2 font-display text-2xl font-semibold tabular-nums">{{ $metric?->{$field} ?? '—' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
