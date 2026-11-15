@extends('layouts.public')

@section('document_title', $site['label'].' – BacklinkPro')

@section('meta_description', $site['about'])

@section('content')
    @php
        $compact = fn ($value) => \App\Support\DemoNicheData::compact(is_numeric($value) ? (int) $value : null);
        $savedHref = \Illuminate\Support\Facades\Route::has('customer.saved-websites')
            ? route('customer.saved-websites')
            : (\Illuminate\Support\Facades\Route::has('auth.login') ? route('auth.login') : url('/login'));
        $known = [];
        foreach (\App\Support\DemoNicheData::names() as $name) {
            $known[\Illuminate\Support\Str::slug($name)] = $name;
        }
        $flat = $groups !== null
            ? collect($groups)->flatMap(fn (array $group) => $group['rows'])->all()
            : $rows;
        $categories = [];
        foreach ($flat as $row) {
            foreach ($row['niches'] ?? [] as $niche) {
                if (is_string($niche) && $niche !== '' && $niche !== 'Publisher') {
                    $categories[$niche] = \Illuminate\Support\Str::slug($niche);
                }
            }
        }
        ksort($categories);
        $languages = [];
        $countries = [];
        foreach ($flat as $row) {
            $language = trim((string) ($row['language'] ?? ''));
            $country = strtoupper((string) ($row['top_countries'][0][0] ?? ''));
            if ($language !== '') {
                $languages[$language] = $language;
            }
            if ($country !== '') {
                $countries[$country] = $country;
            }
        }
        ksort($languages);
        ksort($countries);
        $chartSlug = function (string $label): string {
            $slug = \Illuminate\Support\Str::slug($label);

            return $slug !== '' ? $slug : 'unknown';
        };
        $hasChart = $site['kind'] !== 'favorites' && (($groups !== null && $groups !== []) || $rows !== []);
    @endphp

    <style>
        .site-front { background: #fff; }
        .site-front-wrap { max-width: none; margin: 0 auto; padding: 28px 20px 8px; color: #152029; }
        .site-front-crumb { margin: 0 0 18px; color: #4A5560; font-size: 14px; font-weight: 700; }
        .site-front-crumb a { color: inherit; text-decoration: none; }
        .site-front-crumb a:hover { color: #C0340D; }
        .site-front h1 {
            margin: 0;
            font-family: Montserrat, sans-serif;
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 800;
            line-height: 1.15;
        }
        .site-front-about, .site-front-detail {
            margin: 16px 0 0;
            max-width: 820px;
            color: #4A5560;
            font-size: 16px;
            line-height: 1.7;
        }
        .site-front h2 {
            margin: 28px 0 0;
            max-width: 820px;
            font-family: Montserrat, sans-serif;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.3;
        }
        .site-front-checks {
            margin: 16px 0 0;
            max-width: 820px;
            padding: 0;
            list-style: none;
            color: #4A5560;
            font-size: 16px;
            line-height: 1.7;
        }
        .site-front-checks li + li { margin-top: 10px; }
        .site-front-note {
            margin: 16px 0 0;
            max-width: 820px;
            border-radius: 12px;
            background: #FFF1EC;
            padding: 12px 14px;
            color: #C0340D;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
        }
        .site-body { background: #F2F2F2; }
        .site-body-wrap { max-width: none; margin: 0 auto; padding: 28px 20px 72px; }
        .site-specs { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin: 0 0 16px; }
        .site-specs select, .site-specs input { min-height: 44px; border: 1px solid #D9DDE3; border-radius: 8px; background: #fff; color: #152029; font: inherit; font-size: 14px; font-weight: 600; }
        .site-specs select { padding: 0 28px 0 12px; }
        .site-specs input { width: min(100%, 280px); padding: 0 12px; font-weight: 500; }
        .site-spec-count { margin: 0 0 12px; color: #152029; font-size: 16px; font-weight: 800; }
    </style>

    <section class="site-front">
        <div class="site-front-wrap">
            <nav class="site-front-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('sites.index') }}">Sites</a>
                <span aria-hidden="true"> / </span>
                <span>{{ $site['label'] }}</span>
            </nav>

            <h1>{{ $site['label'] }}</h1>
            <p class="site-front-about">{{ $site['about'] }}</p>
            <h2>{{ $site['headline'] }}</h2>
            <p class="site-front-detail">{{ $site['detail'] }}</p>
            <ul class="site-front-checks">
                @foreach ($site['checks'] as $check)
                    <li>{{ $check }}</li>
                @endforeach
            </ul>
            @if ($demo && $site['kind'] !== 'favorites')
                <p class="site-front-note">Sample data for preview. These figures belong to fictional .example publishers.</p>
            @endif

            @include('customer.sites.partials.also-check', [
                'siteOptions' => $sites,
                'currentSlug' => $site['slug'],
            ])
        </div>
    </section>

    <section class="site-body">
        <div class="site-body-wrap">
            <div class="rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                <h2 class="font-display text-[22px] font-extrabold text-navy">Categories</h2>
                @if ($site['kind'] === 'favorites')
                    <p class="mt-3 text-[15px] leading-7 text-[#4A5560]">Categories appear after you save publishers. Sign in to open the websites on your account.</p>
                @elseif ($categories === [])
                    <p class="mt-3 text-[15px] leading-7 text-[#4A5560]">No niche is stored for the publishers on this list.</p>
                @else
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($categories as $name => $slug)
                            <li>
                                @if (isset($known[$slug]))
                                    <a href="{{ route('niches.show', $slug) }}" class="inline-flex min-h-11 items-center rounded-full border border-[#E3E5E8] px-4 text-[14px] font-bold text-navy hover:bg-[#FFF1EC] hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $name }}</a>
                                @else
                                    <span class="inline-flex min-h-11 items-center rounded-full border border-[#E3E5E8] px-4 text-[14px] font-bold text-navy">{{ $name }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($site['kind'] === 'favorites')
                <div class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-6">
                    <p class="text-[16px] leading-7 text-[#4A5560]">Save a publisher with the heart on its listing. Those websites stay on your account.</p>
                    <a href="{{ $savedHref }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#D63A10] px-6 text-[15px] font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Open favorite websites</a>
                </div>
            @elseif ($hasChart)
                <div class="mt-6 rounded-[20px] border border-[#E3E5E8] bg-white p-5 sm:p-6">
                    <div class="site-specs" aria-label="Website specifications">
                        @include('customer.sites.partials.spec-filters', [
                            'languages' => $languages,
                            'countries' => $countries,
                            'categories' => $categories,
                        ])
                        <input id="site-spec-search" type="search" placeholder="Search by website or niche" aria-label="Search by website or niche">
                    </div>
                    <p class="site-spec-count" id="site-spec-count" aria-live="polite"></p>
                    <div id="site-lists">
                        @if ($groups !== null)
                            @foreach ($groups as $group)
                                <div data-site-country>
                                    <h2 id="site-country-{{ $chartSlug((string) $group['country']) }}" class="mt-2 scroll-mt-24 font-display text-[22px] font-extrabold text-navy">{{ $group['country'] }}</h2>
                                    <div class="mt-4">
                                        @include('customer.sites.table', ['rows' => $group['rows'], 'compact' => $compact, 'filterable' => true, 'demo' => $demo])
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @include('customer.sites.table', ['rows' => $rows, 'compact' => $compact, 'filterable' => true, 'demo' => $demo])
                        @endif
                    </div>
                    <p class="mt-4 text-[15px] leading-7 text-[#4A5560]" id="site-spec-empty" hidden>No publisher matches these filters.</p>
                </div>
            @elseif ($rows === [])
                <p class="mt-8 rounded-[20px] border border-[#E3E5E8] bg-white px-5 py-4 text-[15px] leading-7 text-[#4A5560]">No publisher is listed for this yet.</p>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var root = document.getElementById('site-lists');
            var count = document.getElementById('site-spec-count');
            var empty = document.getElementById('site-spec-empty');
            var search = document.getElementById('site-spec-search');
            if (!root || !count || !search) {
                return;
            }
            var rows = Array.prototype.slice.call(root.querySelectorAll('[data-publisher-row]'));
            var number = function (row, name) { return Number(row.getAttribute(name) || 0); };
            var selected = function (name) {
                var field = document.querySelector('.site-specs [data-filter="' + name + '"]');
                return field ? field.value : '';
            };
            var matches = function (row) {
                var query = (search.value || '').trim().toLowerCase();
                if (query && (row.getAttribute('data-search') || '').indexOf(query) === -1) {
                    return false;
                }
                var minimums = ['as', 'da', 'dr', 'pa', 'tf', 'traffic', 'semrush', 'backlinks', 'referring'];
                for (var i = 0; i < minimums.length; i++) {
                    var min = selected(minimums[i]);
                    if (min !== '' && number(row, 'data-' + minimums[i]) < Number(min)) {
                        return false;
                    }
                }
                var maxPrice = selected('price');
                if (maxPrice !== '' && number(row, 'data-price') > Number(maxPrice)) {
                    return false;
                }
                var spamMax = selected('spam');
                if (spamMax !== '' && number(row, 'data-spam') > Number(spamMax)) {
                    return false;
                }
                var exact = ['language', 'country', 'type', 'validity', 'sports', 'pharmacy', 'crypto', 'news', 'sponsored', 'foreign'];
                for (var j = 0; j < exact.length; j++) {
                    var value = selected(exact[j]);
                    if (value !== '' && row.getAttribute('data-' + exact[j]) !== value) {
                        return false;
                    }
                }
                var niche = selected('niche');
                if (niche !== '' && ('|' + (row.getAttribute('data-niche') || '') + '|').indexOf('|' + niche + '|') === -1) {
                    return false;
                }
                return true;
            };
            var paint = function () {
                var visible = rows.filter(matches);
                rows.forEach(function (row) { row.hidden = visible.indexOf(row) === -1; });
                root.querySelectorAll('[data-site-country]').forEach(function (group) {
                    group.hidden = group.querySelector('[data-publisher-row]:not([hidden])') === null;
                });
                count.textContent = 'Showing ' + visible.length + ' of ' + rows.length + ' publishers';
                empty.hidden = visible.length !== 0;
            };
            document.querySelectorAll('.site-specs [data-filter]').forEach(function (field) {
                field.addEventListener('change', paint);
            });
            search.addEventListener('input', paint);
            paint();
        })();
    </script>
@endpush
