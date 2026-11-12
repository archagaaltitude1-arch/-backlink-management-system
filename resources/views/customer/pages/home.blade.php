@extends('layouts.public')

@section('document_title', 'BacklinkPro – High-Quality Backlinks from Real Websites')

@section('meta_description', 'Order text links, contextual links, guest posts and niche edits on checked publisher websites, with verified reports.')

@section('content')
    @php
        $publishers = $publisherCount === null ? '[N]+' : number_format($publisherCount).'+';
        $startHref = auth()->check()
            ? url('/marketplace')
            : (Route::has('auth.register') ? route('auth.register') : url('/register'));
        $accountHref = Route::has('customer.dashboard')
            ? route('customer.dashboard')
            : (Route::has('auth.login') ? route('auth.login') : url('/login'));
        $contactHref = 'mailto:[YOUR EMAIL]';
        $publisherListHref = route('publishers.index');

        $reasons = [
            ['icon' => 'shield', 'title' => 'Safe, Relevant Placements', 'text' => 'Your links appear on websites that match your niche and already attract real readers. We never use link farms or private blog networks.', 'link' => 'Explore Our Services', 'href' => url('/services')],
            ['icon' => 'search', 'title' => 'A Marketplace You Can Inspect', 'text' => 'See DR, DA, PA, Trust Flow, monthly traffic, spam score and price for every website, then filter by niche to build your shortlist.', 'link' => 'See the publisher list', 'href' => $publisherListHref],
            ['icon' => 'chart', 'title' => 'Track Every Order', 'text' => 'Watch each order move from submitted to published to verified, and receive the live URL and anchor details the moment it goes up.', 'link' => null, 'href' => null],
            ['icon' => 'layers', 'title' => 'From One Link to Hundreds', 'text' => 'Buy a single placement or prepaid credit packages. Credits are only reserved on submission and deducted once your link is verified.', 'link' => 'See Packages', 'href' => url('/packages')],
            ['icon' => 'file', 'title' => 'Client-Ready Reports', 'text' => 'Download white-label reports showing live URLs, anchors and metrics, ready to share with your own clients.', 'link' => null, 'href' => null],
            ['icon' => 'users', 'title' => 'Real SEO Team Support', 'text' => 'Our specialists check content relevance, review every published page and help you choose the right link type for each target URL.', 'link' => 'Contact Our Team', 'href' => $contactHref],
        ];

        $controls = [
            ['icon' => 'search', 'title' => $publishers.' Checked Publishers', 'text' => 'Each website is reviewed by our team for real traffic and genuine content before it\'s listed.'],
            ['icon' => 'filter', 'title' => 'Filter by Niche & Metrics', 'text' => 'Narrow the list by DR, DA, PA, traffic, spam score, niche and price to match your campaign.', 'count' => $nicheCount],
            ['icon' => 'eye', 'title' => 'Clear SEO Metrics', 'text' => 'Authority, traffic and spam data are shown on every listing and on each website\'s details page.'],
            ['icon' => 'ban', 'title' => 'No Hidden Networks', 'text' => 'Every publisher is a real, named website. Check the full details before you buy.'],
            ['icon' => 'sliders', 'title' => 'You Stay in Control', 'text' => 'Set your own target URL and anchor text, or submit your own article for review before it goes live.'],
            ['icon' => 'clock', 'title' => 'Delivery in 3–14 Days', 'text' => 'Most placements are live within two weeks, with a verified report when done.'],
        ];

        $countsAvailable = (bool) ($countryCounts['available'] ?? false);
        $counts = $countryCounts['counts'] ?? [];
        $sampleRows = class_exists(\App\Support\DemoNicheData::class)
            ? array_slice(\App\Support\DemoNicheData::rowsFor('Technology'), 0, 4)
            : [];
    @endphp

    <section class="bg-[#F2F2F2]" aria-labelledby="home-heading">
        <div class="mx-auto flex max-w-[1240px] flex-col items-center gap-10 px-6 py-14 md:flex-row md:items-center md:justify-between md:gap-12 lg:py-[88px]">
            <div class="min-w-0 w-full md:max-w-[640px] md:flex-1">
                <p class="inline-flex rounded-full border border-[#E3E5E8] bg-white px-4 py-2 text-sm font-bold text-[#C0340D]">Link Building Agency · White-Hat Only</p>
                <h1 id="home-heading" class="mt-5 font-display text-[36px] leading-[1.08] font-extrabold text-balance text-navy lg:text-[56px]">Earn Higher Rankings with Backlinks from <span class="text-[#FF4917]">Real, Traffic-Driven Websites</span></h1>
                <p class="mt-5 max-w-xl text-[17px] leading-8 text-[#4A5560] sm:text-[19px]">Pick from {{ $publishers }} checked publisher websites and grow your search visibility, visitors and sales with text links, contextual links, guest posts and niche edits. Every one is verified before you get your report.</p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <x-ui.button variant="primary" :href="$startHref">Start Building Links</x-ui.button>
                    <x-ui.button variant="outline" :href="$publisherListHref">See the publisher list</x-ui.button>
                </div>
                <ul class="mt-6 grid gap-x-4 gap-y-2 sm:grid-cols-2">
                    @foreach ([$publishers.' Checked Publishers', 'White-Hat Placements Only', 'Delivered in 3–14 Days', 'Every Link Verified'] as $trust)
                        <li class="flex min-h-11 items-center gap-2 text-[15px] font-bold text-navy">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[#FFF1EC] text-[#D63A10]">
                                <x-ui.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span>{{ $trust }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="mx-auto w-full max-w-[480px] min-w-0 shrink-0 md:mx-0 md:ml-auto md:w-[46%]" aria-hidden="true">
                <div class="rounded-[28px] bg-navy p-4 shadow-[0_18px_40px_rgba(21,32,41,0.18)] sm:p-6">
                    <div class="mb-4 flex items-center justify-between px-1 text-[12px] font-bold tracking-[0.08em] text-[#B8C2CC] uppercase">
                        <span>Publisher snapshot</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#7DCEA0]"></span> Live</span>
                    </div>
                    <article class="rounded-[20px] border border-[#E3E5E8] bg-white p-4 text-navy sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold tracking-wide text-[#4A5560] uppercase">Website</p>
                                <p class="mt-1 truncate font-display text-lg font-extrabold sm:text-xl">sample-site.example</p>
                            </div>
                            <span class="shrink-0 rounded-full bg-[#1F7A45] px-2.5 py-1 text-xs font-bold text-white">Dofollow</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            @foreach (['DR' => '72', 'DA' => '64', 'Traffic' => '80K'] as $metric => $value)
                        <div class="rounded-xl bg-[#F2F2F2] px-2 py-3 text-center">
                                    <p class="text-[11px] font-bold tracking-wide text-[#4A5560] uppercase">{{ $metric }}</p>
                                    <p class="mt-1 font-display text-lg font-extrabold sm:text-xl">{{ $value }}</p>
                        </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <div class="mb-1.5 flex items-center justify-between text-xs font-bold text-[#4A5560]">
                                <span>Authority</span>
                                <span class="text-[#C0340D]">72%</span>
                    </div>
                            <div class="h-2 overflow-hidden rounded-full bg-[#F2F2F2]">
                        <div class="h-full w-[72%] rounded-full bg-[#FF4917]"></div>
                    </div>
                        </div>
                    </article>
                    <div class="mt-3 grid grid-cols-1 gap-3 min-[420px]:grid-cols-2">
                        <p class="flex min-h-11 items-center gap-2 rounded-[16px] bg-white px-3 py-3 text-sm font-bold text-navy">
                            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-[#E7F6EE] text-[#1F7A45]">
                                <x-ui.icon name="check" class="h-4 w-4" />
                            </span>
                            Backlink verified
                        </p>
                        <p class="flex min-h-11 items-center justify-center rounded-[16px] bg-[#FF4917] px-3 py-3 text-center text-sm font-bold text-white">Spam score 2%</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="why-pro" aria-labelledby="why-heading">
        <style>
            .why-pro { background: #F6F7F9; }
            .why-head {
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(240px, .8fr);
                gap: 28px 48px;
                align-items: end;
            }
            .why-kicker {
                margin: 0 0 10px;
                color: #C0340D;
                font-size: 13px;
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
            }
            .why-title {
                margin: 0;
                color: #152029;
                font-size: 40px;
                line-height: 1.12;
                font-weight: 800;
            }
            .why-copy { margin: 0; color: #4A5560; font-size: 16px; line-height: 1.65; }
            .why-start {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-height: 44px;
                margin-top: 14px;
                padding: 10px 18px;
                border-radius: 12px;
                background: #D63A10;
                color: #fff;
                font-size: 15px;
                font-weight: 700;
            }
            .why-start:hover { background: #FF4917; color: #fff; }
            .why-start:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
            .why-stats {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 12px;
                margin-top: 36px;
            }
            .why-stat {
                border-radius: 14px;
                background: #fff;
                padding: 16px 18px;
            }
            .why-stat dt { margin: 0; color: #152029; font-size: 26px; line-height: 1; font-weight: 800; }
            .why-stat dd { margin: 8px 0 0; color: #4A5560; font-size: 14px; font-weight: 700; }
            .why-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 18px;
                margin-top: 18px;
            }
            .why-card {
                display: flex;
                flex-direction: column;
                border-radius: 16px;
                background: #fff;
                padding: 22px;
                box-shadow: 0 10px 28px rgba(21, 32, 41, .045);
            }
            .why-card-top { display: flex; align-items: center; gap: 12px; }
            .why-icon {
                display: grid;
                width: 44px;
                height: 44px;
                flex: none;
                place-items: center;
                border-radius: 12px;
                background: var(--wash);
                color: var(--ink);
            }
            .why-card h3 { margin: 0; color: #152029; font-size: 16px; line-height: 1.3; font-weight: 800; }
            .why-label { margin: 2px 0 0; color: #8B939C; font-size: 13px; }
            .why-label a { color: inherit; font-weight: 600; }
            .why-label a:hover { color: var(--ink); }
            .why-label a:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
            .why-rule { height: 1px; margin: 16px 0; background: #EEF0F3; }
            .why-card .why-text { margin: 0; color: #4A5560; font-size: 14.5px; line-height: 1.65; }
            @media (max-width: 980px) {
                .why-title { font-size: 32px; }
                .why-head, .why-grid { grid-template-columns: 1fr; }
                .why-stats { grid-template-columns: 1fr 1fr; }
            }
            @media (min-width: 640px) and (max-width: 980px) {
                .why-grid { grid-template-columns: 1fr 1fr; }
            }
        </style>
        <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-[88px]">
            <div class="why-head">
                <div>
                    <p class="why-kicker">Why BacklinkPro</p>
                    <h2 id="why-heading" class="why-title font-display">One Partner for Text Links, Contextual Links, Guest Posts &amp; Niche Edits</h2>
                </div>
                <div>
                    <p class="why-copy">Why brands and agencies rely on BacklinkPro for safe, lasting link building.</p>
                    <a class="why-start" href="{{ $accountHref }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.25" />
                            <path d="M5.5 19.25a6.5 6.5 0 0 1 13 0" />
                        </svg>
                        My Account
                    </a>
                </div>
                </div>

            <dl class="why-stats">
                @foreach ([
                    [$publishers, 'Checked Publishers'],
                    ['[N]+', 'Campaigns Completed'],
                    ['3–14 Days', 'Delivery Window'],
                    ['[YEAR]', 'Serving Clients Since'],
                ] as [$figure, $label])
                    <div class="why-stat">
                        <dt class="font-display">{{ $figure }}</dt>
                        <dd>{{ $label }}</dd>
                </div>
                @endforeach
            </dl>

            @php
                $reasonTones = [
                    'shield' => ['wash' => '#E8F1FF', 'ink' => '#3B82F6', 'label' => 'Checked websites'],
                    'search' => ['wash' => '#FDE8F4', 'ink' => '#DB2777', 'label' => 'Marketplace'],
                    'chart' => ['wash' => '#E7F8F2', 'ink' => '#0F766E', 'label' => 'Order updates'],
                    'layers' => ['wash' => '#FFF1E8', 'ink' => '#EA580C', 'label' => 'Credits and packages'],
                    'file' => ['wash' => '#EAF2FF', 'ink' => '#2563EB', 'label' => 'White-label reports'],
                    'users' => ['wash' => '#F3E8FF', 'ink' => '#7C3AED', 'label' => 'SEO team'],
                ];
            @endphp
            <div class="why-grid">
                @foreach ($reasons as $reason)
                    @php $tone = $reasonTones[$reason['icon']] ?? ['wash' => '#FFF1EC', 'ink' => '#FF4917', 'label' => 'BacklinkPro']; @endphp
                    <article class="why-card">
                        <div class="why-card-top">
                            <span class="why-icon" style="--wash: {{ $tone['wash'] }}; --ink: {{ $tone['ink'] }};">
                                <x-ui.icon :name="$reason['icon']" />
                            </span>
                            <div>
                                <h3 class="font-display">{{ $reason['title'] }}</h3>
                                <p class="why-label">
                                    @if ($reason['link'])
                                        <a href="{{ $reason['href'] }}">{{ $reason['link'] }}</a>
                                @else
                                        {{ $tone['label'] }}
                                @endif
                                </p>
                            </div>
                        </div>
                        <div class="why-rule"></div>
                        <p class="why-text">{{ $reason['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#F4F6FB]" aria-labelledby="services-heading">
        <style>
            .service-option,
            .service-price-btn {
                transition: background-color .15s ease, color .15s ease, box-shadow .15s ease, transform .15s ease;
            }
            .service-option {
                display: flex;
                align-items: center;
                min-height: 44px;
                padding: 8px 14px;
                border-radius: 14px;
                background: rgba(255, 255, 255, .78);
                color: var(--tone);
                font-size: 14px;
                font-weight: 700;
                line-height: 1.3;
                box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--tone) 22%, transparent);
            }
            .service-option:hover {
                background: #fff;
                box-shadow: 0 8px 16px rgba(21, 32, 41, .08), inset 0 0 0 1px var(--tone);
                transform: translateY(-1px);
            }
            .service-price-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                min-height: 48px;
                padding: 10px 16px;
                border-radius: 999px;
                background: var(--tone);
                color: #fff;
                font-size: 14px;
                font-weight: 700;
                line-height: 1.25;
                text-align: center;
                box-shadow: 0 10px 18px color-mix(in srgb, var(--tone) 32%, transparent);
            }
            .service-price-btn svg {
                display: inline-block;
                margin-left: 6px;
                vertical-align: -3px;
                transition: transform .15s ease;
            }
            .service-price-btn:hover {
                background: #152029;
                box-shadow: 0 12px 22px rgba(21, 32, 41, .18);
                transform: translateY(-1px);
            }
            .service-price-btn:hover svg {
                transform: translateX(3px);
            }
            .service-option:focus-visible,
            .service-price-btn:focus-visible {
                outline: 2px solid #D63A10;
                outline-offset: 2px;
            }
        </style>
        @php
            $serviceTones = [
                'text-link' => ['bg' => '#F3E9FB', 'ink' => '#6D28D9'],
                'contextual-link' => ['bg' => '#E7F6EF', 'ink' => '#0F766E'],
                'guest-post' => ['bg' => '#FDECDC', 'ink' => '#C2410C'],
                'niche-edit' => ['bg' => '#E7F3FC', 'ink' => '#1D4ED8'],
            ];
                                    @endphp
        <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-[88px]">
            <div class="mx-auto max-w-4xl text-center">
                <p class="text-sm font-bold tracking-[0.08em] text-[#C0340D] uppercase">Our services</p>
                <h2 id="services-heading" class="mt-3 font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[40px]">
                    <span class="text-[#5B4BDB]">BacklinkPro</span> Link Building Services Built Around Real Results
                </h2>
                <p class="mt-4 text-[17px] leading-8 font-medium text-navy sm:text-[19px]">Order text links, contextual links, guest posts and niche edits on checked publisher websites, with clear metrics, fast delivery and reports made for agencies and in-house teams.</p>
            </div>

            @unless ($servicesPublished)
                <p class="mx-auto mt-6 max-w-3xl rounded-[20px] border border-dashed border-[#C5CBD1] bg-white px-5 py-4 text-center text-base leading-7 text-[#4A5560]" role="status">Service prices are not published yet. The four services below describe what we offer. A starting price appears on a card once that service is active.</p>
            @endunless

            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($serviceCards as $card)
                    @php
                        $tone = $serviceTones[$card['slug']] ?? ['bg' => '#FFF1EC', 'ink' => '#D63A10'];
                    @endphp
                    <article class="flex h-full flex-col rounded-[22px] p-5" style="background-color: {{ $tone['bg'] }};">
                        <div class="flex items-start gap-3">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white/80" style="color: {{ $tone['ink'] }};">
                                <x-ui.icon :name="$card['icon']" />
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-display text-[20px] leading-tight font-extrabold" style="color: {{ $tone['ink'] }};">{{ $card['title'] }}</h3>
                                @if ($card['featured'])
                                    <p class="mt-1 text-xs font-bold tracking-wide uppercase" style="color: {{ $tone['ink'] }};">Most popular</p>
                                @endif
                                @if ($card['price'])
                                    <p class="mt-1 text-sm font-bold text-navy">{{ $card['price'] }}</p>
                                    @endif
                            </div>
                        </div>
                        <p class="mt-4 text-[15px] leading-6 text-navy/80">{{ $card['description'] }}</p>
                        <p class="mt-3 text-[15px] leading-6 text-[#4A5560]">Best for: {{ $card['best_for'] }}</p>
                        <p class="mt-4 text-[13px] font-bold tracking-wide text-navy uppercase">Popular options</p>
                        <ul class="mt-2 space-y-2">
                            @foreach ($card['options'] as $option)
                                <li>
                                    <a href="{{ $option['href'] }}" class="service-option" style="--tone: {{ $tone['ink'] }};">{{ $option['label'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-auto pt-5">
                            <a href="{{ url('/services/'.$card['slug']) }}" class="service-price-btn" style="--tone: {{ $tone['ink'] }};">
                                <span>{{ $card['button'] }}<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white" aria-labelledby="control-heading">
        <style>
            .control-wrap { text-align: center; }
            .control-title {
                margin: 0;
                color: #152029;
                font-size: 40px;
                line-height: 1.15;
                font-weight: 800;
            }
            .control-lead {
                max-width: 820px;
                margin: 16px auto 0;
                color: #152029;
                font-size: 18px;
                line-height: 1.55;
                font-weight: 700;
            }
            .control-layout {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 22px 36px;
                margin-top: 48px;
                text-align: left;
            }
            .control-item { display: flex; gap: 14px; align-items: flex-start; }
            .control-icon { flex: 0 0 auto; margin-top: 2px; color: var(--ink); }
            .control-item h3 { margin: 0; color: #152029; font-size: 18px; line-height: 1.3; font-weight: 800; }
            .control-item p { margin: 4px 0 0; color: #4A5560; font-size: 15px; line-height: 1.55; }
            .control-foot, .control-preview { grid-column: 1 / -1; }
            .control-preview {
                overflow: hidden;
                border: 1px solid #E3E5E8;
                border-radius: 20px;
                background: #fff;
                box-shadow: 0 18px 40px rgba(21, 32, 41, .08);
            }
            .control-preview-bar {
                display: flex;
                gap: 8px;
                align-items: center;
                padding: 12px 14px;
                border-bottom: 1px solid #E3E5E8;
                background: #F7F8F9;
            }
            .control-dot { width: 8px; height: 8px; border-radius: 99px; background: #E3E5E8; }
            .control-dot:nth-child(1) { background: #FF5F57; }
            .control-dot:nth-child(2) { background: #FEBC2E; }
            .control-dot:nth-child(3) { background: #28C840; }
            .control-chip {
                border-radius: 6px;
                background: #fff;
                color: #4A5560;
                font-size: 11px;
                font-weight: 700;
                padding: 4px 8px;
            }
            .control-sample {
                display: inline-block;
                border-radius: 99px;
                background: #FFF1EC;
                color: #C0340D;
                font-size: 10px;
                font-weight: 800;
                letter-spacing: .04em;
                padding: 2px 6px;
                text-transform: uppercase;
            }
            .diy-scroll { overflow-x: auto; }
            .diy-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
            .diy-table col.c-url { width: 19%; }
            .diy-table col.c-num { width: 5%; }
            .diy-table col.c-niche { width: 12%; }
            .diy-table col.c-lang { width: 7%; }
            .diy-table col.c-chart { width: 9%; }
            .diy-table col.c-keys { width: 6%; }
            .diy-table col.c-country { width: 7%; }
            .diy-table col.c-price { width: 7%; }
            .diy-table col.c-buy { width: 9%; }
            .diy-table th {
                padding: 10px 6px;
                background: #F7F8F9;
                color: #152029;
                font-size: 12px;
                font-weight: 800;
                line-height: 1.25;
                text-align: left;
                vertical-align: bottom;
            }
            .diy-table td {
                padding: 10px 6px;
                border-top: 1px solid #F0F1F3;
                color: #152029;
                font-size: 13px;
                vertical-align: top;
            }
            .diy-table th:first-child, .diy-table td:first-child { padding-left: 14px; }
            .diy-table th:last-child, .diy-table td:last-child { padding-right: 14px; }
            .diy-table .c-num, .diy-table .c-keys { text-align: center; }
            .diy-url { display: flex; align-items: center; gap: 6px; min-width: 0; color: #152029; font-weight: 800; }
            .diy-url span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .diy-star { flex: none; color: #FF4917; }
            .diy-metric { font-size: 14px; font-weight: 800; line-height: 1.2; white-space: nowrap; }
            .diy-chart { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }
            .diy-chart .nd-spark { width: 100%; height: 28px; }
            .diy-country { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; white-space: nowrap; }
            .diy-flag { display: inline-flex; width: 20px; height: 14px; flex: none; overflow: hidden; border-radius: 2px; box-shadow: 0 0 0 1px rgba(0,0,0,.1); }
            .diy-flag svg { width: 20px; height: 14px; }
            .diy-publish {
                display: inline-flex;
                min-height: 36px;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                background: #D63A10;
                padding: 0 10px;
                color: #fff;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            @media (max-width: 1099px) {
                .diy-table { width: 1080px; }
            }
            @media (max-width: 900px) {
                .control-title { font-size: 30px; }
                .control-lead { font-size: 16px; }
                .control-layout { grid-template-columns: 1fr; }
            }
        </style>
        <div class="control-wrap mx-auto max-w-[1240px] px-6 py-14 lg:py-[88px]">
            <p class="text-sm font-bold tracking-[0.08em] text-[#C0340D] uppercase">Do it yourself</p>
            <h2 id="control-heading" class="control-title font-display">Prefer to Choose <span class="text-[#FF4917]">Every Website</span> Yourself?</h2>
            <p class="control-lead">Shop contextual placements straight from our publisher marketplace. Filter by metrics, niche and price, see everything up front, and order in minutes.</p>

            <div class="control-layout">
                @foreach ($controls as $control)
                    @php
                        $ink = ['search' => '#3B82F6', 'filter' => '#14B8A6', 'eye' => '#7C3AED', 'ban' => '#F59E0B', 'sliders' => '#FF4917', 'clock' => '#0EA5E9'][$control['icon']] ?? '#FF4917';
                    @endphp
                    <article class="control-item">
                        <span class="control-icon" style="--ink: {{ $ink }};">
                            <x-ui.icon :name="$control['icon']" />
                        </span>
                        <div>
                            <h3 class="font-display">{{ $control['title'] }}</h3>
                            <p>{{ $control['text'] }}</p>
                            @if (array_key_exists('count', $control) && $control['count'] !== null)
                                <p class="font-bold text-[#C0340D]">{{ number_format($control['count']) }} active {{ (int) $control['count'] === 1 ? 'niche' : 'niches' }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach

                <div class="control-preview" aria-hidden="true">
                    <div class="control-preview-bar">
                        <span class="control-dot"></span>
                        <span class="control-dot"></span>
                        <span class="control-dot"></span>
                        <span class="control-chip">All</span>
                        <span class="control-chip">DR</span>
                        <span class="control-chip">Traffic</span>
                        <span class="control-chip">Niche</span>
                        <span class="control-chip">Price</span>
                        <span class="control-sample" style="margin-left:auto">Sample</span>
                    </div>
                    <div class="diy-scroll">
                        <table class="diy-table">
                            <colgroup>
                                <col class="c-url">
                                <col class="c-num">
                                <col class="c-num">
                                <col class="c-num">
                                <col class="c-niche">
                                <col class="c-lang">
                                <col class="c-chart">
                                <col class="c-keys">
                                <col class="c-country">
                                <col class="c-chart">
                                <col class="c-price">
                                <col class="c-buy">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Website URL</th>
                                    <th class="c-num">AS</th>
                                    <th class="c-num">DA</th>
                                    <th class="c-num">DR</th>
                                    <th>Niche</th>
                                    <th>Language</th>
                                    <th>Ahrefs<br>traffic</th>
                                    <th class="c-keys">Ahrefs<br>keywords</th>
                                    <th>Countries</th>
                                    <th>Semrush<br>traffic</th>
                                    <th>Price</th>
                                    <th>Buy now</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sampleRows as $sample)
                                    @php
                                        $country = $sample['top_countries'][0] ?? null;
                                        $flagCode = strtoupper((string) ($country[0] ?? ''));
                                        $flagCode = $flagCode === 'UK' ? 'gb' : strtolower($flagCode);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="diy-url">
                                                <span>{{ $sample['domain'] }}</span>
                                                <svg class="diy-star" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="m12 3.2 2.4 5.4 5.9.6-4.4 3.9 1.3 5.8L12 16.2 6.8 19l1.3-5.8L3.7 9.2l5.9-.6L12 3.2Z"/></svg>
                                            </span>
                                        </td>
                                        <td class="c-num diy-metric">{{ $sample['as'] }}</td>
                                        <td class="c-num diy-metric">{{ $sample['da'] }}</td>
                                        <td class="c-num diy-metric">{{ $sample['dr'] }}</td>
                                        <td>{{ $sample['niches'][0] ?? 'Technology' }}</td>
                                        <td>{{ $sample['language'] }}</td>
                                        <td>
                                            <div class="diy-chart">
                                                <x-ui.sparkline :seed="$sample['domain'].'-ahrefs'" color="#E24B4B" />
                                                <span class="diy-metric">{{ \App\Support\DemoNicheData::compact($sample['traffic']) }}</span>
                                            </div>
                                        </td>
                                        <td class="c-keys diy-metric">{{ \App\Support\DemoNicheData::compact($sample['keywords']) }}</td>
                                        <td>
                                            @if ($country)
                                                <span class="diy-country">
                                                    <span class="diy-flag"><x-ui.flag :code="$flagCode" width="20" height="14" /></span>
                                                    {{ number_format((float) ($country[1] ?? 0), 1) }}%
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="diy-chart">
                                                <x-ui.sparkline :seed="$sample['domain'].'-semrush'" color="#22A06B" />
                                                <span class="diy-metric">{{ \App\Support\DemoNicheData::compact($sample['semrush_traffic']) }}</span>
                                            </div>
                                        </td>
                                        <td><span class="diy-metric">${{ number_format((float) $sample['price'], 2) }}</span></td>
                                        <td><span class="diy-publish">Publish</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="control-foot">
                    <ul class="flex flex-wrap gap-2">
                        @foreach (['Verified Websites', 'Metrics Updated Regularly', 'White-Hat Only'] as $chip)
                            <li class="inline-flex min-h-11 items-center gap-2 rounded-full border border-navy bg-white px-4 text-[14px] font-bold text-navy">
                                <x-ui.icon name="check" class="h-4 w-4 text-[#D63A10]" />
                                {{ $chip }}
                            </li>
                        @endforeach
                    </ul>

                    {{-- Confirm this offer with the team before turning it on. Leave marketing.promo_code empty until then. --}}
                    @if (filled(config('marketing.promo_code')))
                        <p class="mt-6 text-base font-bold text-navy">New customer? Use code {{ config('marketing.promo_code') }} at checkout for {{ config('marketing.promo_percent') }}% off your first order.</p>
                    @endif

                    <div class="mt-6">
                        <x-ui.button variant="primary" :href="$publisherListHref">See the publisher list</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="say-section" aria-labelledby="reviews-heading" aria-roledescription="carousel">
        <style>
            .say-section { overflow: hidden; background: #fff; }
            .say-layout {
                display: grid;
                grid-template-columns: 280px minmax(0, 1fr);
                gap: 12px 28px;
                align-items: center;
                max-width: 1240px;
                margin: 0 auto;
                padding: 72px 24px 88px;
            }
            .say-title {
                margin: 0;
                color: #241B4B;
                font-size: 44px;
                line-height: 1.12;
                font-weight: 800;
            }
            .say-nav { display: flex; gap: 10px; margin-top: 28px; }
            .say-btn {
                display: grid;
                width: 48px;
                height: 48px;
                place-items: center;
                border: 1.5px solid #E4E6EA;
                border-radius: 999px;
                background: #fff;
                color: #241B4B;
                cursor: pointer;
            }
            .say-btn:hover { border-color: #FF4917; color: #FF4917; }
            .say-btn:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
            .say-stage { position: relative; min-height: 430px; overflow: hidden; }
            .say-orb {
                position: absolute;
                top: 50%;
                right: -90px;
                width: 620px;
                height: 620px;
                border-radius: 50%;
                background: radial-gradient(circle at 38% 38%, #FF8A55 0%, #FF4917 48%, #E23B0C 100%);
                transform: translateY(-50%);
            }
            .say-track {
                position: relative;
                z-index: 1;
                display: flex;
                gap: 18px;
                padding: 24px 8px 8px;
                transition: transform .4s ease;
            }
            .say-card { width: 280px; flex: none; margin: 0; }
            .say-bubble {
                position: relative;
                border-radius: 16px;
                background: #fff;
                padding: 22px 20px 24px;
                color: #3C4660;
                font-size: 14.5px;
                line-height: 1.65;
                box-shadow: 0 14px 34px rgba(21, 32, 41, .1);
            }
            .say-bubble::after {
                content: "";
                position: absolute;
                bottom: -8px;
                left: 26px;
                width: 16px;
                height: 16px;
                background: #fff;
                transform: rotate(45deg);
            }
            .say-person { display: flex; align-items: center; gap: 10px; margin-top: 20px; }
            .say-person img {
                width: 46px;
                height: 46px;
                flex: none;
                border-radius: 999px;
                object-fit: cover;
                box-shadow: 0 0 0 2px #fff;
            }
            .say-name { margin: 0; color: #241B4B; font-size: 15px; font-weight: 800; }
            .say-role {
                margin: 2px 0 0;
                color: #FF4917;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
            }
            @media (max-width: 860px) {
                .say-layout { grid-template-columns: 1fr; padding: 48px 20px 64px; }
                .say-title { font-size: 34px; }
                .say-stage { min-height: 390px; }
                .say-orb { width: 420px; height: 420px; right: -120px; }
                .say-card { width: min(280px, 78vw); }
            }
        </style>
        <div class="say-layout">
                <div>
                <h2 id="reviews-heading" class="say-title font-display">What our customers are saying</h2>
                <div class="say-nav">
                    <button type="button" class="say-btn" data-review-prev aria-label="Previous review">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M10 3 5 8l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button type="button" class="say-btn" data-review-next aria-label="Next review">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </div>

            {{-- Sample reviews and portraits for the layout. Replace with real, approved customers before launch. --}}
            @php
                $reviews = [
                    ['photo' => 'review-amira.jpg', 'name' => 'Amira Shah', 'role' => 'Northline', 'quote' => 'Contextual links went onto checked publisher sites, and the live URL was in our report the same week.'],
                    ['photo' => 'review-leo.jpg', 'name' => 'Leo Grant', 'role' => 'Harbor Digital', 'quote' => 'Filtering by niche and DR made the shortlist simple. Every site we ordered was a real publisher.'],
                    ['photo' => 'review-priya.jpg', 'name' => 'Priya Nair', 'role' => 'Field & Co', 'quote' => 'Guest posts went live inside the delivery window, and the verified report was ready for our client.'],
                    ['photo' => 'review-jonah.jpg', 'name' => 'Jonah Ellis', 'role' => 'Brightpath', 'quote' => 'Niche edits on older articles gave us a faster start than waiting for brand-new posts to index.'],
                    ['photo' => 'review-sofia.jpg', 'name' => 'Sofia Marquez', 'role' => 'Lumen SEO', 'quote' => 'Traffic, spam score and price are on every listing, so we stopped guessing which sites were worth buying.'],
                    ['photo' => 'review-kenji.jpg', 'name' => 'Kenji Sato', 'role' => 'Atlas Links', 'quote' => 'The white-label report was clean enough to send to our clients without rebuilding it.'],
                ];
            @endphp
            <div class="say-stage" data-review-stage>
                <div class="say-orb" aria-hidden="true"></div>
                <div class="say-track" data-review-track>
                    @foreach ($reviews as $review)
                        <figure class="say-card" data-review-card>
                            <blockquote class="say-bubble">{{ $review['quote'] }}</blockquote>
                            <figcaption class="say-person">
                                <img src="{{ asset('images/reviews/'.$review['photo']) }}" alt="" width="46" height="46">
                                <div>
                                    <p class="say-name">{{ $review['name'] }}</p>
                                    <p class="say-role">{{ $review['role'] }}</p>
                                </div>
                            </figcaption>
                        </figure>
                @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#F2F2F2]" aria-labelledby="home-niches-heading">
        <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-[88px]">
            <x-ui.section-heading
                eyebrow="Niches"
                title="Browse Publisher Websites by Niche"
                subtitle="Open a niche to see the publisher websites that accept guest posts, contextual links, niche edits and text links."
                heading-id="home-niches-heading"
            />
            <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($nicheTiles as $niche)
                    <a href="{{ route('niches.show', $niche['slug']) }}" class="flex min-h-11 items-center justify-between gap-3 rounded-[20px] border border-[#E3E5E8] bg-white px-5 py-4 font-bold text-navy hover:border-[#D63A10] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $niche['name'] }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <x-ui.country-browser :counts="$counts" :counts-available="$countsAvailable" />

    <section class="home-cta" aria-labelledby="home-cta-heading">
        <style>
            .home-cta { background: #F2F2F2; }
            .home-cta-wrap { max-width: 1240px; margin: 0 auto; padding: 28px 24px 72px; }
            .home-cta-card {
                display: grid;
                grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr);
                overflow: hidden;
                border-radius: 28px;
                background: #fff;
                box-shadow: 0 18px 40px rgba(21, 32, 41, .08);
            }
            .home-cta-copy { position: relative; padding: 48px 40px 44px; }
            .home-cta-copy::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 6px;
                background: linear-gradient(90deg, #D63A10 0%, #FF4917 55%, #FFB199 100%);
            }
            .home-cta-kicker { margin: 0; color: #C0340D; font-size: 13px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
            .home-cta h2 { margin: 12px 0 0; max-width: 560px; color: #152029; font-size: 36px; line-height: 1.12; font-weight: 800; }
            .home-cta-text { max-width: 520px; margin: 14px 0 0; color: #4A5560; font-size: 17px; line-height: 1.65; }
            .home-cta-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 28px; }
            .home-cta-primary, .home-cta-secondary, .home-cta-quiet {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 48px;
                padding: 12px 22px;
                border-radius: 14px;
                font-size: 15px;
                font-weight: 800;
                text-decoration: none;
            }
            .home-cta-primary { background: #D63A10; color: #fff; }
            .home-cta-primary:hover { background: #FF4917; }
            .home-cta-secondary { border: 1.5px solid #152029; background: #fff; color: #152029; }
            .home-cta-secondary:hover { background: #152029; color: #fff; }
            .home-cta-quiet { color: #C0340D; padding-left: 8px; padding-right: 8px; }
            .home-cta-quiet:hover { color: #D63A10; }
            .home-cta-primary:focus-visible, .home-cta-secondary:focus-visible, .home-cta-quiet:focus-visible { outline: 2px solid #D63A10; outline-offset: 3px; }
            .home-cta-side { background: #152029; padding: 40px 32px; color: #fff; }
            .home-cta-side h3 { margin: 0; color: #fff; font-size: 18px; font-weight: 800; }
            .home-cta-steps { margin: 22px 0 0; padding: 0; list-style: none; }
            .home-cta-steps li { display: flex; gap: 14px; align-items: flex-start; }
            .home-cta-steps li + li { margin-top: 18px; }
            .home-cta-steps .home-cta-num {
                display: inline-flex;
                width: 44px;
                height: 44px;
                flex: none;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                background: #D63A10;
                color: #fff;
                font-size: 18px;
                font-weight: 800;
                line-height: 1;
            }
            .home-cta-steps p { margin: 0; min-width: 0; padding-top: 2px; }
            .home-cta-steps strong { display: block; color: #fff; font-size: 15px; line-height: 1.3; }
            .home-cta-steps p span { display: block; margin-top: 4px; color: #D5DCE3; font-size: 14px; line-height: 1.5; }
            @media (min-width: 900px) { .home-cta h2 { font-size: 44px; } }
            @media (max-width: 860px) {
                .home-cta-card { grid-template-columns: 1fr; }
                .home-cta-copy, .home-cta-side { padding: 32px 22px; }
                .home-cta h2 { font-size: 30px; }
            }
        </style>
        <div class="home-cta-wrap">
            <div class="home-cta-card">
                <div class="home-cta-copy">
                    <p class="home-cta-kicker">Get started</p>
                    <h2 id="home-cta-heading" class="font-display">Ready to Order Your First Backlink?</h2>
                    <p class="home-cta-text">Choose a checked publisher website, set your target URL and anchor, and we send the live URL once the link is verified.</p>
                    <div class="home-cta-actions">
                        <a class="home-cta-primary" href="{{ $startHref }}">Start Your Order</a>
                        <a class="home-cta-secondary" href="{{ $publisherListHref }}">See the publisher list</a>
                        <a class="home-cta-quiet" href="{{ url('/services') }}">See Services</a>
                    </div>
                </div>
                <div class="home-cta-side">
                    <h3 class="font-display">How an order starts</h3>
                    <ol class="home-cta-steps">
                        <li>
                            <span class="home-cta-num">1</span>
                            <p><strong>Choose a website</strong><span>Open the publisher list and compare the metrics stored on each listing.</span></p>
                        </li>
                        <li>
                            <span class="home-cta-num">2</span>
                            <p><strong>Set the placement</strong><span>Add your target URL and anchor, or send your own article for review.</span></p>
                        </li>
                        <li>
                            <span class="home-cta-num">3</span>
                            <p><strong>Get the live URL</strong><span>We send the verified page address once the link is published.</span></p>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var reviewTrack = document.querySelector('[data-review-track]');
            var reviewStage = document.querySelector('[data-review-stage]');
            var reviewCards = reviewTrack ? Array.prototype.slice.call(reviewTrack.querySelectorAll('[data-review-card]')) : [];
            var reviewIndex = 0;

            function reviewStep() {
                if (!reviewCards.length) return 0;
                var card = reviewCards[0].getBoundingClientRect().width;
                var gap = 18;
                return card + gap;
            }

            function reviewMax() {
                if (!reviewStage || !reviewCards.length) return 0;
                var visible = Math.max(1, Math.floor(reviewStage.clientWidth / reviewStep()));
                return Math.max(0, reviewCards.length - visible);
            }

            function showReview(next) {
                if (!reviewTrack || !reviewCards.length) return;
                var max = reviewMax();
                reviewIndex = next > max ? 0 : next < 0 ? max : next;
                reviewTrack.style.transform = 'translateX(' + (-reviewIndex * reviewStep()) + 'px)';
            }

            var prevReview = document.querySelector('[data-review-prev]');
            var nextReview = document.querySelector('[data-review-next]');
            if (prevReview) prevReview.addEventListener('click', function () { showReview(reviewIndex - 1); });
            if (nextReview) nextReview.addEventListener('click', function () { showReview(reviewIndex + 1); });
            window.addEventListener('resize', function () { showReview(reviewIndex); });
            showReview(0);
        });
    </script>
@endpush
