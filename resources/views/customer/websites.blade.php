@extends('layouts.customer')

@section('title', 'Websites')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Publisher marketplace</p>
                <h1 class="mt-2 text-3xl font-semibold">Browse websites</h1>
                <p class="mt-2 text-sm text-navy/65">{{ $websites->total() }} publishers available across {{ $niches->count() }} niches.</p>
            </div>
            <a class="btn-navy" href="{{ route('orders.cart') }}">View cart</a>
        </div>

        <form method="GET" action="{{ route('customer.websites') }}" class="panel grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_150px_180px_auto] lg:items-end">
            <div>
                <label class="text-xs font-medium" for="search">Search sites</label>
                <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or domain" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium" for="niche">Niche</label>
                <select id="niche" name="niche" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
                    <option value="">All niches</option>
                    @foreach ($niches as $niche)
                        <option value="{{ $niche }}" @selected(($filters['niche'] ?? '') === $niche)>{{ $niche }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium" for="min_dr">Minimum DR</label>
                <input id="min_dr" name="min_dr" type="number" min="0" max="100" value="{{ $filters['min_dr'] ?? '' }}" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium" for="sort">Sort by</label>
                <select id="sort" name="sort" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2 text-sm">
                    <option value="recommended" @selected(($filters['sort'] ?? 'recommended') === 'recommended')>Recommended</option>
                    <option value="dr" @selected(($filters['sort'] ?? '') === 'dr')>Domain rating</option>
                    <option value="traffic" @selected(($filters['sort'] ?? '') === 'traffic')>Traffic</option>
                    <option value="price" @selected(($filters['sort'] ?? '') === 'price')>Price</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button class="btn-primary" type="submit">Filter</button>
                <a class="btn-navy" href="{{ route('customer.websites') }}">Reset</a>
            </div>
        </form>

        @if ($websites->isEmpty())
            <div class="panel p-10 text-center text-sm text-navy/60">No publishers match those filters.</div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($websites as $website)
                    <article class="panel flex h-full flex-col p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="badge bg-orange-100 text-orange-800">{{ $website->niche }}</span>
                                <h2 class="mt-3 truncate text-lg font-semibold">{{ $website->website_name }}</h2>
                                <a class="mt-1 block truncate text-sm text-brand hover:underline" href="{{ $website->url }}" target="_blank" rel="noopener noreferrer">{{ $website->domain }}</a>
                            </div>
                            <div class="shrink-0 text-right"><p class="text-xs text-navy/50">DR</p><p class="text-2xl font-semibold">{{ $website->domain_rating }}</p></div>
                        </div>
                        <p class="mt-3 line-clamp-2 text-sm leading-5 text-navy/65">{{ $website->description }}</p>
                        <dl class="mt-4 grid grid-cols-3 gap-x-3 gap-y-3 border-y border-navy/10 py-4 text-sm">
                            <div><dt class="text-xs text-navy/50">DA</dt><dd class="mt-1 font-semibold">{{ $website->domain_authority }}</dd></div>
                            <div><dt class="text-xs text-navy/50">TF / PA</dt><dd class="mt-1 font-semibold">{{ $website->trust_flow }} / {{ $website->page_authority }}</dd></div>
                            <div><dt class="text-xs text-navy/50">Spam</dt><dd class="mt-1 font-semibold">{{ number_format($website->spam_score, 1) }}%</dd></div>
                            <div><dt class="text-xs text-navy/50">Traffic / mo</dt><dd class="mt-1 font-semibold">{{ number_format($website->monthly_traffic) }}</dd></div>
                            <div><dt class="text-xs text-navy/50">Backlinks</dt><dd class="mt-1 font-semibold">{{ number_format($website->backlinks) }}</dd></div>
                            <div><dt class="text-xs text-navy/50">Ref. domains</dt><dd class="mt-1 font-semibold">{{ number_format($website->referring_domains) }}</dd></div>
                        </dl>
                        <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-4">
                            <div><p class="text-xs text-navy/50">Guest post from</p><p class="text-xl font-semibold">${{ number_format($website->price, 2) }}</p><p class="text-xs text-navy/55">{{ $website->credit_cost }} credits</p></div>
                            <div class="flex flex-wrap gap-2">
                                @auth
                                    <form method="POST" action="{{ route('customer.saved-websites.store') }}">
                                        @csrf
                                        <input type="hidden" name="website_name" value="{{ $website->website_name }}">
                                        <input type="hidden" name="domain" value="{{ $website->domain }}">
                                        <input type="hidden" name="url" value="{{ $website->url }}">
                                        <input type="hidden" name="domain_rating" value="{{ $website->domain_rating }}">
                                        <input type="hidden" name="monthly_traffic" value="{{ $website->monthly_traffic }}">
                                        <input type="hidden" name="price" value="{{ $website->price }}">
                                        <button class="btn-navy" type="submit">Save</button>
                                    </form>
                                    <form method="POST" action="{{ route('orders.cart.items.store') }}">
                                        @csrf
                                        <input type="hidden" name="product_key" value="guest_post">
                                        <input type="hidden" name="website_id" value="{{ $website->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button class="btn-primary" type="submit">Add to cart</button>
                                    </form>
                                @else
                                    <a class="btn-primary" href="{{ route('auth.login') }}">Sign in to order</a>
                                @endauth
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div>{{ $websites->links() }}</div>
        @endif
    </div>
@endsection
