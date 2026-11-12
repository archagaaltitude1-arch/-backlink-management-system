@extends('layouts.public')

@php
    use App\Support\DemoNicheData;
    use Illuminate\Support\Facades\Route;

    $intro = $custom_intro ?? [
        'Looking to reach readers in '.$name.'? Browse our list of '.$name.' websites that accept guest posts, contextual links, niche edits and text links. Every site is checked for real traffic before it\'s listed.',
        'Compare publishers by DR, DA, monthly traffic, spam score and price, then add the ones that fit your campaign to your cart.',
    ];
    $meta = \Illuminate\Support\Str::limit($intro[0], 160, '');
    $loginHref = Route::has('auth.login') ? route('auth.login') : url('/login');
    $canSave = auth()->check() && Route::has('customer.saved-websites.store');
    $canCart = auth()->check() && Route::has('orders.cart.items.store');
    $viewMode = request('view') === 'cards' ? 'cards' : 'table';
    $from = $websites->firstItem() ?? 0;
    $to = $websites->lastItem() ?? 0;

    $chip = function (?int $value): string {
        if ($value === null) {
            return 'background:#F2F2F2;color:#4A5560';
        }
        if ($value >= 50) {
            return 'background:#E7F8F2;color:#0F766E';
        }
        if ($value >= 30) {
            return 'background:#FFF6E8;color:#B45309';
        }

        return 'background:#F2F2F2;color:#4A5560';
    };

    $spamStyle = function (?int $value): string {
        if ($value === null) {
            return 'background:#F2F2F2;color:#4A5560';
        }
        if ($value <= 3) {
            return 'background:#E7F8F2;color:#0F766E';
        }
        if ($value <= 7) {
            return 'background:#FFF6E8;color:#B45309';
        }

        return 'background:#FDECEC;color:#B91C1C';
    };

    $filters = [
        'search' => request('search'),
        'bulk' => request('bulk'),
        'as_min' => request('as_min'),
        'da_min' => request('da_min'),
        'dr_min' => request('dr_min'),
        'price_max' => request('price_max'),
        'traffic_min' => request('traffic_min'),
        'semrush_min' => request('semrush_min'),
        'language' => request('language'),
        'sports' => request('sports'),
        'pharmacy' => request('pharmacy'),
        'crypto' => request('crypto'),
        'backlinks_min' => request('backlinks_min'),
        'link_type' => request('link_type'),
        'link_validity' => request('link_validity'),
        'google_news' => request('google_news'),
        'sponsored' => request('sponsored'),
        'country' => request('country'),
        'foreign_language' => request('foreign_language'),
        'per_page' => request('per_page') && (string) request('per_page') !== '20' ? request('per_page') : null,
        'sort' => request('sort') && request('sort') !== 'traffic_desc' ? request('sort') : null,
    ];
    $filtersActive = collect($filters)->contains(fn ($value) => filled($value));
@endphp

@section('document_title', $name.' Guest Posting Sites ('.$total.') – BacklinkPro')

@section('meta_description', $meta)

@section('content')
    <style>
        .nd-h1 { margin: 0; font-size: 30px; line-height: 1.15; font-weight: 800; }
        .nd-section { padding: 56px 0; }
        .nd-toolbar { background: #fff; border-bottom: 1px solid #E3E5E8; }
        .nd-pills { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 12px; }
        .nd-select { appearance: none; min-height: 44px; max-width: 100%; border: 1px solid #D9DDE3; border-radius: 8px; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M2.5 4.5 6 8l3.5-3.5' fill='none' stroke='%23152029' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") right 12px center no-repeat; padding: 0 34px 0 12px; color: #152029; font: inherit; font-size: 14px; font-weight: 600; }
        .nd-select.is-set { border-color: #FF4917; color: #D63A10; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath d='M2.5 4.5 6 8l3.5-3.5' fill='none' stroke='%23D63A10' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); }
        .nd-select:focus { outline: 2px solid #D63A10; outline-offset: 2px; }
        .nd-results { display: flex; flex-direction: column; align-items: stretch; gap: 12px; margin-top: 14px; padding-top: 14px; border-top: 1px solid #E3E5E8; }
        .nd-count { margin: 0; color: #152029; font-size: 16px; font-weight: 800; }
        .nd-tools { display: flex; flex-direction: column; align-items: stretch; gap: 8px; min-width: 0; }
        .nd-tool-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .nd-search { display: flex; align-items: center; gap: 6px; width: 100%; min-width: 0; min-height: 44px; border: 1px solid #D9DDE3; border-radius: 10px; background: #fff; padding-left: 12px; }
        .nd-search:focus-within { outline: 2px solid #D63A10; outline-offset: 2px; }
        .nd-search input { width: 100%; min-width: 0; border: 0; background: transparent; color: #152029; font: inherit; font-size: 14px; }
        .nd-search input:focus { outline: none; }
        .nd-search button { display: inline-flex; width: 44px; height: 44px; flex: none; align-items: center; justify-content: center; border: 0; background: transparent; color: #4A5560; }
        .nd-search button:focus-visible { outline: 2px solid #D63A10; outline-offset: -2px; }
        .nd-sr { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
        .nd-bulk { display: inline-flex; align-items: center; min-height: 44px; border: 1.5px solid #FF4917; border-radius: 8px; background: #fff; padding: 0 14px; color: #D63A10; font-size: 13px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; }
        .nd-bulk:focus-visible, .nd-bulk-panel textarea:focus { outline: 2px solid #D63A10; outline-offset: 2px; }
        .nd-bulk-panel { display: none; flex: 1 1 100%; }
        .nd-bulk-panel.is-open { display: block; }
        .nd-bulk-panel textarea { width: 100%; min-height: 88px; margin-top: 8px; border: 1px solid #D9DDE3; border-radius: 10px; padding: 10px 12px; color: #152029; font: inherit; font-size: 14px; }
        .nd-table-wrap { overflow-x: hidden; background: #fff; }
        .nd-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .nd-table col.c-url { width: 17%; }
        .nd-table col.c-num { width: 4.2%; }
        .nd-table col.c-niche { width: 10.5%; }
        .nd-table col.c-lang { width: 7.5%; }
        .nd-table col.c-chart { width: 9.5%; }
        .nd-table col.c-keys { width: 6.5%; }
        .nd-table col.c-countries { width: 8.4%; }
        .nd-table col.c-price { width: 7.5%; }
        .nd-table col.c-buy { width: 11%; }
        .nd-table th { padding: 10px 6px; border-bottom: 1px solid #E3E5E8; background: #F7F8F9; color: #152029; font-size: 12px; font-weight: 800; line-height: 1.25; text-align: left; vertical-align: bottom; }
        .nd-table td { padding: 10px 6px; border-bottom: 1px solid #F0F1F3; vertical-align: top; font-size: 13px; color: #152029; }
        .nd-table th:first-child, .nd-table td:first-child { padding-left: 12px; }
        .nd-table th:last-child, .nd-table td:last-child { padding-right: 12px; }
        .nd-table .nd-spark { width: 100%; height: 32px; }
        .nd-table .nd-publish { min-height: 40px; gap: 4px; padding: 0 8px; font-size: 12px; letter-spacing: 0.02em; }
        .nd-table .nd-url { gap: 6px; min-width: 0; }
        .nd-table .nd-url a { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
        .nd-table .nd-niches { font-size: 12px; line-height: 1.35; }
        .nd-table th.c-num, .nd-table td.c-num, .nd-table th.c-keys, .nd-table td.c-keys { text-align: center; }
        .nd-table tbody tr:last-child td { border-bottom: 0; }
        .nd-url { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; }
        .nd-url a { white-space: nowrap; }
        .nd-metric { font-size: 15px; font-weight: 800; line-height: 1.2; color: #152029; white-space: nowrap; }
        .nd-niches { margin: 0; padding: 0; list-style: none; color: #4A5560; font-size: 13px; line-height: 1.55; }
        .nd-niches li + li { margin-top: 2px; }
        .nd-chart { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
        .nd-spark { display: block; width: 104px; height: 36px; }
        .nd-countries { display: flex; flex-direction: column; gap: 4px; }
        .nd-country { display: flex; align-items: center; gap: 6px; color: #152029; font-size: 13px; font-weight: 700; white-space: nowrap; }
        .nd-publish { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; border: 0; border-radius: 10px; background: #D63A10; padding: 0 16px; color: #fff; font-size: 13px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; text-decoration: none; }
        .nd-publish:hover { background: #FF4917; color: #fff; }
        .nd-publish:disabled,
        .nd-publish:disabled:hover { background: #D63A10; color: #fff; opacity: 1; cursor: not-allowed; }
        .nd-publish:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .nd-board { width: 100%; padding: 16px 24px 20px; }
        .nd-list { width: 100%; padding: 24px 24px 40px; }
        .nd-cards { display: flex; flex-direction: column; gap: 10px; }
        .nd-site-card { border: 1px solid #E3E5E8; border-radius: 20px; background: #fff; padding: 14px 16px; }
        .nd-site-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-start; gap: 8px 20px; }
        .nd-site-id { min-width: 0; flex: 0 1 auto; }
        .nd-site-niches { display: flex; flex-wrap: wrap; gap: 2px 12px; margin-top: 4px; }
        .nd-site-niches li { margin: 0; }
        .nd-site-lang { margin: 2px 0 0; color: #4A5560; font-size: 13px; }
        .nd-site-offer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: 8px 12px; margin-left: auto; }
        .nd-site-credits { color: #4A5560; font-size: 13px; }
        .nd-site-stats { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 10px 20px; margin-top: 10px; padding-top: 10px; border-top: 1px solid #F0F1F3; }
        .nd-site-metrics { display: grid; grid-template-columns: repeat(2, max-content); gap: 8px 14px; margin: 0; }
        .nd-site-metrics dt, .nd-site-label { display: block; color: #4A5560; font-size: 12px; font-weight: 700; }
        .nd-site-metrics dd { margin: 2px 0 0; color: #152029; font-size: 16px; font-weight: 800; }
        .nd-site-label { margin-bottom: 4px; }
        .nd-pill { display: inline-flex; min-width: 36px; align-items: center; justify-content: center; border-radius: 999px; padding: 4px 8px; font-weight: 800; }
        .nd-page { display: inline-flex; min-width: 44px; min-height: 44px; align-items: center; justify-content: center; border: 1px solid #E3E5E8; border-radius: 12px; padding: 0 12px; color: #152029; font-weight: 700; text-decoration: none; }
        .nd-page[aria-current="page"] { border-color: #D63A10; background: #D63A10; color: #fff; }
        .nd-page[aria-disabled="true"] { color: #B8C2CC; }
        @media (min-width: 800px) {
            .nd-results { flex-direction: row; align-items: center; justify-content: space-between; }
            .nd-tools { flex: 1; flex-direction: row; align-items: center; justify-content: flex-end; }
            .nd-search { width: 280px; }
            .nd-views { flex: none; }
            .nd-site-metrics { grid-template-columns: repeat(4, max-content); gap: 0 14px; }
        }
        @media (max-width: 1099px) {
            .nd-table-wrap { overflow-x: auto; }
            .nd-table { width: 1100px; }
        }
        @media (min-width: 900px) {
            .nd-h1 { font-size: 44px; }
            .nd-section { padding: 56px 0; }
            .nd-board, .nd-list { padding-left: 32px; padding-right: 32px; }
            .nd-list { padding-top: 16px; padding-bottom: 28px; }
            .nd-site-stats { gap: 8px 28px; }
        }
    </style>

    <section class="bg-[#F2F2F2]" aria-labelledby="niche-heading">
        <div class="mx-auto max-w-[1240px] px-6 nd-section">
            <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('niches.index') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Niches</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">{{ $name }}</span>
            </nav>
            <div class="mt-6 flex flex-wrap items-center gap-4">
                <span class="grid h-14 w-14 place-items-center rounded-2xl bg-[#FFF1EC] text-[#D63A10]" aria-hidden="true">
                    <x-ui.icon name="layers" />
                </span>
                <h1 id="niche-heading" class="nd-h1 font-display text-navy">{{ $name }} Guest Posting Sites</h1>
                <span class="inline-flex min-h-11 items-center rounded-full bg-[#FFF1EC] px-4 text-sm font-bold text-[#C0340D]">{{ $total }} Publishers</span>
            </div>
            <p class="mt-4">
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest', 'page' => null]) }}" class="inline-flex min-h-11 items-center text-[15px] font-bold text-[#C0340D] underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">See recently added sites →</a>
            </p>
            <div class="mt-4 max-w-[760px] text-[17px] leading-8 text-[#4A5560]">
                <p>{{ $intro[0] }}</p>
                <p class="mt-4">
                    {{ $intro[1] }}
                    @if ($custom_intro === null && $intro_related !== [])
                        Related niches:
                        @foreach ($intro_related as $relatedNiche)
                            <a href="{{ route('niches.show', $relatedNiche['slug']) }}" class="font-bold text-[#C0340D] underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $relatedNiche['name'] }}</a>@if (! $loop->last), @endif
                        @endforeach.
                    @endif
                </p>
            </div>
        </div>
    </section>

    <div class="nd-toolbar">
        <form id="niche-filters" class="nd-board" method="get" action="{{ route('niches.show', $slug) }}">
            @if ($viewMode === 'cards')
                <input type="hidden" name="view" value="cards">
            @endif
            <div class="nd-pills">
                <select id="niche-as" class="nd-select {{ request('as_min') ? 'is-set' : '' }}" name="as_min" aria-label="Minimum AS">
                    <option value="">AS</option>
                    @foreach ([20, 30, 40, 50, 60] as $option)
                        <option value="{{ $option }}" @selected((string) request('as_min') === (string) $option)>AS {{ $option }}+</option>
                    @endforeach
                </select>
                <select id="niche-da" class="nd-select {{ request('da_min') ? 'is-set' : '' }}" name="da_min" aria-label="Minimum DA">
                    <option value="">DA</option>
                    @foreach ([20, 30, 40, 50, 60] as $option)
                        <option value="{{ $option }}" @selected((string) request('da_min') === (string) $option)>DA {{ $option }}+</option>
                    @endforeach
                </select>
                <select id="niche-dr" class="nd-select {{ request('dr_min') ? 'is-set' : '' }}" name="dr_min" aria-label="Minimum DR">
                    <option value="">DR</option>
                    @foreach ([20, 30, 40, 50, 60] as $option)
                        <option value="{{ $option }}" @selected((string) request('dr_min') === (string) $option)>DR {{ $option }}+</option>
                    @endforeach
                </select>
                <select id="niche-price" class="nd-select {{ request('price_max') ? 'is-set' : '' }}" name="price_max" aria-label="Price range">
                    <option value="">Price range</option>
                    @foreach (['100' => 'Up to $100', '250' => 'Up to $250', '500' => 'Up to $500', '1000' => 'Up to $1,000'] as $value => $label)
                        <option value="{{ $value }}" @selected((string) request('price_max') === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select id="niche-traffic" class="nd-select {{ request('traffic_min') ? 'is-set' : '' }}" name="traffic_min" aria-label="Minimum Ahrefs traffic">
                    <option value="">Ahrefs traffic</option>
                    @foreach (['1000' => '1K+', '5000' => '5K+', '10000' => '10K+', '50000' => '50K+'] as $value => $label)
                        <option value="{{ $value }}" @selected((string) request('traffic_min') === (string) $value)>Ahrefs {{ $label }}</option>
                    @endforeach
                </select>
                <select id="niche-semrush" class="nd-select {{ request('semrush_min') ? 'is-set' : '' }}" name="semrush_min" aria-label="Minimum Semrush traffic">
                    <option value="">Semrush traffic</option>
                    @foreach (['1000' => '1K+', '5000' => '5K+', '10000' => '10K+', '50000' => '50K+'] as $value => $label)
                        <option value="{{ $value }}" @selected((string) request('semrush_min') === (string) $value)>Semrush {{ $label }}</option>
                    @endforeach
                </select>
                <select id="niche-language" class="nd-select {{ request('language') ? 'is-set' : '' }}" name="language" aria-label="Language">
                    <option value="">Language</option>
                    @foreach ($languages as $language)
                        <option value="{{ $language }}" @selected(request('language') === $language)>{{ $language }}</option>
                    @endforeach
                </select>
                @foreach ([
                    'sports' => 'Sports/Gaming allowed?',
                    'pharmacy' => 'Pharmacy allowed?',
                    'crypto' => 'Crypto allowed?',
                ] as $field => $label)
                    <select id="niche-{{ $field }}" class="nd-select {{ request($field) ? 'is-set' : '' }}" name="{{ $field }}" aria-label="{{ $label }}">
                        <option value="">{{ $label }}</option>
                        <option value="yes" @selected(request($field) === 'yes')>Yes</option>
                        <option value="no" @selected(request($field) === 'no')>No</option>
                    </select>
                @endforeach
                <select id="niche-backlinks" class="nd-select {{ request('backlinks_min') ? 'is-set' : '' }}" name="backlinks_min" aria-label="Number of backlinks">
                    <option value="">No. of backlinks</option>
                    @foreach (['10' => '10+', '50' => '50+', '100' => '100+', '500' => '500+', '1000' => '1,000+'] as $value => $label)
                        <option value="{{ $value }}" @selected((string) request('backlinks_min') === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select id="niche-link-type" class="nd-select {{ request('link_type') ? 'is-set' : '' }}" name="link_type" aria-label="Link type">
                    <option value="">Link type</option>
                    @foreach (['Guest post', 'Niche edit', 'Contextual', 'Text link'] as $type)
                        <option value="{{ $type }}" @selected(request('link_type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                <select id="niche-validity" class="nd-select {{ request('link_validity') ? 'is-set' : '' }}" name="link_validity" aria-label="Link validity">
                    <option value="">Link validity</option>
                    @foreach (['Permanent', '12 months', '6 months'] as $validity)
                        <option value="{{ $validity }}" @selected(request('link_validity') === $validity)>{{ $validity }}</option>
                    @endforeach
                </select>
                @foreach ([
                    'google_news' => 'Google News',
                    'sponsored' => 'Marked as sponsored',
                ] as $field => $label)
                    <select id="niche-{{ $field }}" class="nd-select {{ request($field) ? 'is-set' : '' }}" name="{{ $field }}" aria-label="{{ $label }}">
                        <option value="">{{ $label }}</option>
                        <option value="yes" @selected(request($field) === 'yes')>Yes</option>
                        <option value="no" @selected(request($field) === 'no')>No</option>
                    </select>
                @endforeach
                <select id="niche-country" class="nd-select {{ request('country') ? 'is-set' : '' }}" name="country" aria-label="Country">
                    <option value="">Country</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country }}" @selected(request('country') === $country)>{{ $country }}</option>
                    @endforeach
                </select>
                <select id="niche-foreign" class="nd-select {{ request('foreign_language') ? 'is-set' : '' }}" name="foreign_language" aria-label="Foreign language allowed">
                    <option value="">Foreign lang. allowed?</option>
                    <option value="yes" @selected(request('foreign_language') === 'yes')>Yes</option>
                    <option value="no" @selected(request('foreign_language') === 'no')>No</option>
                </select>
                <button type="button" id="bulk-toggle" class="nd-bulk" aria-expanded="{{ filled(request('bulk')) ? 'true' : 'false' }}" aria-controls="bulk-panel">Bulk search</button>
                @if ($filtersActive)
                    <a href="{{ route('niches.show', $slug) }}" class="inline-flex min-h-11 items-center px-2 text-sm font-bold text-[#C0340D] underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Clear filters</a>
                @endif
                <div id="bulk-panel" class="nd-bulk-panel {{ filled(request('bulk')) ? 'is-open' : '' }}">
                    <label class="nd-sr" for="niche-bulk">Domains to search, one per line</label>
                    <textarea id="niche-bulk" name="bulk" placeholder="One domain per line">{{ request('bulk') }}</textarea>
                </div>
            </div>
            <div class="nd-results">
                <p class="nd-count">Showing {{ $from }} – {{ $to }} of {{ $total }} publishers</p>
                <div class="nd-tools">
                    <div class="nd-search">
                        <label class="nd-sr" for="niche-search">Search {{ $name }} websites</label>
                        <input id="niche-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search by website or niche">
                        <button type="submit" aria-label="Search websites">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                    <div class="nd-tool-row">
                    <select id="niche-per-page" class="nd-select" name="per_page" aria-label="Publishers per page">
                        @foreach ([20, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} per page</option>
                        @endforeach
                    </select>
                    <select id="niche-sort" class="nd-select {{ request('sort') && request('sort') !== 'traffic_desc' ? 'is-set' : '' }}" name="sort" aria-label="Sort publishers">
                        @foreach (['traffic_desc' => 'Sort by Ahrefs traffic', 'semrush_desc' => 'Sort by Semrush traffic', 'dr_desc' => 'Sort by DR', 'da_desc' => 'Sort by DA', 'as_desc' => 'Sort by AS', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'newest' => 'Newest'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('sort', 'traffic_desc') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="nd-views flex gap-2" role="group" aria-label="Change website view">
                        <a href="{{ request()->fullUrlWithQuery(['view' => 'table', 'page' => null]) }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-bold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10] {{ $viewMode === 'table' ? 'bg-navy text-white' : 'border border-[#D9DDE3] text-navy' }}" @if ($viewMode === 'table') aria-current="true" @endif>Table</a>
                        <a href="{{ request()->fullUrlWithQuery(['view' => 'cards', 'page' => null]) }}" class="inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-bold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10] {{ $viewMode === 'cards' ? 'bg-navy text-white' : 'border border-[#D9DDE3] text-navy' }}" @if ($viewMode === 'cards') aria-current="true" @endif>Cards</a>
                    </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <section class="bg-white" aria-labelledby="publishers-heading">
        <div class="nd-list">
            <h2 id="publishers-heading" class="sr-only">{{ $name }} publisher websites</h2>
            @if ($demo)
                <p class="mb-4 text-sm font-bold text-[#4A5560]">Sample data for preview</p>
            @endif

            @if ($websites->isEmpty())
                <div class="rounded-[20px] border border-[#E3E5E8] bg-[#F2F2F2] p-8 text-center">
                    <p class="text-[17px] font-bold text-navy">No {{ $name }} websites match these filters.</p>
                    <a href="{{ route('niches.show', $slug) }}" class="mt-4 inline-flex min-h-11 items-center font-bold text-[#C0340D] underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Clear filters</a>
                </div>
            @elseif ($viewMode === 'cards')
                <div class="nd-cards">
                    @foreach ($websites as $site)
                        @include('customer.niches.site-card', ['site' => $site, 'demo' => $demo, 'canSave' => $canSave, 'canCart' => $canCart, 'loginHref' => $loginHref, 'chip' => $chip, 'spamStyle' => $spamStyle])
                    @endforeach
                </div>
            @else
                <div class="nd-table-wrap rounded-[20px] border border-[#E3E5E8]">
                    <table class="nd-table">
                        <colgroup>
                            <col class="c-url">
                            <col class="c-num">
                            <col class="c-num">
                            <col class="c-num">
                            <col class="c-niche">
                            <col class="c-lang">
                            <col class="c-chart">
                            <col class="c-keys">
                            <col class="c-countries">
                            <col class="c-chart">
                            <col class="c-price">
                            <col class="c-buy">
                        </colgroup>
                        <thead>
                            <tr>
                                <th scope="col">Website URL</th>
                                <th class="c-num" scope="col">AS</th>
                                <th class="c-num" scope="col">DA</th>
                                <th class="c-num" scope="col">DR</th>
                                <th scope="col">Niche</th>
                                <th scope="col">Language</th>
                                <th scope="col">Ahrefs<br>traffic</th>
                                <th class="c-keys" scope="col">Ahrefs<br>keywords</th>
                                <th scope="col">Countries</th>
                                <th scope="col">Semrush<br>traffic</th>
                                <th scope="col">Price</th>
                                <th scope="col">Buy now</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($websites as $site)
                                <tr>
                                    <td>
                                        <div class="nd-url">
                                            <a href="{{ $site['demo'] || empty($site['id']) ? '#' : url('/marketplace/'.$site['id']) }}" class="font-bold text-navy underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $site['domain'] }}</a>
                                            <span class="text-[#FF4917]" aria-hidden="true">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="m12 3.2 2.4 5.4 5.9.6-4.4 3.9 1.3 5.8L12 16.2 6.8 19l1.3-5.8L3.7 9.2l5.9-.6L12 3.2Z"/></svg>
                                            </span>
                                            @include('customer.niches.actions', ['site' => $site, 'part' => 'save', 'demo' => $demo, 'canSave' => $canSave, 'canCart' => $canCart, 'loginHref' => $loginHref])
                                        </div>
                                    </td>
                                    <td class="c-num nd-metric">{{ $site['as'] ?? '' }}</td>
                                    <td class="c-num nd-metric">{{ $site['da'] ?? '' }}</td>
                                    <td class="c-num nd-metric">{{ $site['dr'] ?? '' }}</td>
                                    <td>
                                        <ul class="nd-niches">
                                            @foreach ($site['niches'] as $pill)
                                                <li>{{ $pill }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>{{ $site['language'] }}</td>
                                    <td>
                                        <div class="nd-chart">
                                            <x-ui.sparkline :seed="$site['domain'].'-ahrefs'" color="#E24B4B" />
                                            <span class="nd-metric">{{ DemoNicheData::compact($site['traffic']) }}</span>
                                        </div>
                                    </td>
                                    <td class="c-keys nd-metric">{{ DemoNicheData::compact($site['keywords']) }}</td>
                                    <td>
                                        <div class="nd-countries">
                                            @forelse (array_slice($site['top_countries'], 0, 3) as $country)
                                                @php
                                                    $flagCode = strtoupper((string) ($country[0] ?? ''));
                                                    $flagCode = $flagCode === 'UK' ? 'gb' : strtolower($flagCode);
                                                @endphp
                                                <span class="nd-country">
                                                    <span class="inline-flex shrink-0 overflow-hidden rounded-[2px] ring-1 ring-black/10" style="width:20px;height:14px">
                                                        <x-ui.flag :code="$flagCode" width="20" height="14" class="h-full w-full" />
                                                    </span>
                                                    {{ number_format((float) ($country[1] ?? 0), 1) }}%
                                                </span>
                                            @empty
                                                <span></span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <div class="nd-chart">
                                            <x-ui.sparkline :seed="$site['domain'].'-semrush'" color="#22A06B" />
                                            <span class="nd-metric">{{ DemoNicheData::compact($site['semrush_traffic'] ?? null) }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="nd-metric">{{ $site['price'] === null ? '' : '$'.number_format($site['price'], 2) }}</span>
                                        @if ($site['credits'] !== null)
                                            <span class="mt-1 block text-xs text-[#4A5560]">{{ $site['credits'] }} credits</span>
                                        @endif
                                    </td>
                                    <td>
                                        @include('customer.niches.actions', ['site' => $site, 'part' => 'buy', 'demo' => $demo, 'canSave' => $canSave, 'canCart' => $canCart, 'loginHref' => $loginHref])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($websites->hasPages())
                <nav class="mt-6 flex flex-wrap items-center gap-2" aria-label="Pagination">
                    @foreach ($websites->linkCollection() as $link)
                        @php
                            $raw = html_entity_decode(strip_tags((string) $link['label']));
                            $isPrev = str_contains($raw, 'Previous');
                            $isNext = str_contains($raw, 'Next');
                            $pageLabel = $isPrev ? '←' : ($isNext ? '→' : ($raw === '...' ? '…' : $raw));
                        @endphp
                        @if ($link['url'])
                            <a class="nd-page" href="{{ $link['url'] }}" @if ($link['active']) aria-current="page" @endif @if ($isPrev) aria-label="Previous page" @elseif ($isNext) aria-label="Next page" @endif>{{ $pageLabel }}</a>
                        @else
                            <span class="nd-page" aria-disabled="true" @if ($isPrev) aria-label="Previous page" @elseif ($isNext) aria-label="Next page" @endif>{{ $pageLabel }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto max-w-[1240px] px-6 pb-14">
            <div class="grid items-center gap-6 rounded-[24px] bg-[#F2F2F2] px-6 py-8 md:grid-cols-[minmax(0,1fr)_auto] md:px-10">
                <div>
                    <h3 class="font-display text-[24px] font-extrabold text-navy">Can't find the website you need?</h3>
                    <p class="mt-2 max-w-xl text-[16px] leading-7 text-[#4A5560]">Tell us the domain or niche you're after and our outreach team will try to add it.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="mailto:[YOUR EMAIL]" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#D63A10] px-5 text-[15px] font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Request a Website</a>
                    <a href="mailto:[YOUR EMAIL]" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-navy bg-white px-5 text-[15px] font-bold text-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Add Your Website as a Publisher</a>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white" aria-labelledby="related-heading">
        <div class="mx-auto max-w-[1240px] px-6 pb-14">
            <h2 id="related-heading" class="font-display text-[30px] font-extrabold text-navy">Related niches</h2>
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach ($related as $relatedNiche)
                    <a href="{{ route('niches.show', $relatedNiche['slug']) }}" class="inline-flex min-h-11 items-center rounded-full border border-[#E3E5E8] bg-white px-4 text-[15px] font-bold text-navy hover:border-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $relatedNiche['name'] }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <x-ui.country-browser heading-id="niche-countries-heading" />

    <section class="bg-[#F2F2F2]" aria-label="Why publishers are checked">
        <div class="mx-auto grid max-w-[1240px] grid-cols-1 gap-4 px-6 py-14 sm:grid-cols-2 lg:grid-cols-4 lg:py-[88px]">
            @foreach ([
                ['shield', 'Manual Outreach', 'Every placement is arranged by our team, never automated.'],
                ['clock', 'Fast Delivery', 'Most links go live within 3–14 days.'],
                ['check', 'Secure Payment', 'Pay safely by card or with wallet credits.'],
                ['file', 'Credits Back if Rejected', 'Reserved credits are released if a placement fails.'],
            ] as $badge)
                <article class="rounded-[20px] border border-[#E3E5E8] bg-white p-5">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#FFF1EC] text-[#D63A10]" aria-hidden="true">
                        <x-ui.icon :name="$badge[0]" />
                    </span>
                    <h3 class="mt-4 font-display text-lg font-extrabold text-navy">{{ $badge[1] }}</h3>
                    <p class="mt-2 text-[15px] leading-6 text-[#4A5560]">{{ $badge[2] }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('niche-filters');
            if (!form) return;
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var params = new URLSearchParams(new FormData(form));
                Array.from(params.keys()).forEach(function (key) {
                    if (!params.get(key)) params.delete(key);
                });
                window.location.assign(form.action + (params.toString() ? '?' + params.toString() : ''));
            });
            form.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', function () {
                    if (typeof form.requestSubmit === 'function') form.requestSubmit();
                    else form.dispatchEvent(new Event('submit', { cancelable: true }));
                });
            });
            var bulkToggle = document.getElementById('bulk-toggle');
            var bulkPanel = document.getElementById('bulk-panel');
            if (bulkToggle && bulkPanel) {
                bulkToggle.addEventListener('click', function () {
                    var open = bulkPanel.classList.toggle('is-open');
                    bulkToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                    if (open) {
                        var box = document.getElementById('niche-bulk');
                        if (box) box.focus();
                    }
                });
            }
        });
    </script>
@endpush
