@extends('layouts.public')

@section('document_title', 'BacklinkPro – High-Quality Backlinks from Real Websites')

@section('meta_description', 'Order text links, contextual links, guest posts and niche edits on checked publisher websites, with verified reports.')

@section('content')
    @php
        $publishers = $publisherCount === null ? '[N]+' : number_format($publisherCount).'+';
        $startHref = auth()->check()
            ? url('/marketplace')
            : (Route::has('auth.register') ? route('auth.register') : url('/register'));
        $contactHref = route('contact');
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
            ['icon' => 'sliders', 'title' => 'You Stay in Control', 'text' => 'Set your target URL and anchor text. Our team writes and publishes the placement.'],
            ['icon' => 'clock', 'title' => 'Delivery in 3–14 Days', 'text' => 'Most placements are live within two weeks, with a verified report when done.'],
        ];

        $sampleRows = class_exists(\App\Support\DemoNicheData::class)
            ? array_slice(\App\Support\DemoNicheData::rowsFor('Technology'), 0, 4)
            : [];
    @endphp

    <section class="home-hero relative flex min-h-[calc(100dvh-4.5rem)] items-center overflow-hidden bg-[#152029]" aria-labelledby="home-heading">
        <style>
            .home-hero-copy { width: 100%; max-width: 56rem; }
        </style>
        <video
            class="pointer-events-none absolute inset-0 h-full w-full object-cover"
            src="{{ asset('videos/hero-dark.mp4') }}"
            autoplay
            muted
            loop
            playsinline
            aria-hidden="true"
            onloadedmetadata="if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) this.pause()"
        ></video>
        <div class="pointer-events-none absolute inset-0 bg-[#152029]/55"></div>
        <div class="home-hero-inner relative z-10 mx-auto flex w-full flex-col items-center px-6 py-14 text-center">
            <div class="home-hero-copy mx-auto flex flex-col items-center">
                <p class="eyebrow inline-flex rounded-full border border-[#E3E5E8] bg-white/90 px-4 py-2 text-[#C0340D] backdrop-blur-sm">Link Building Agency · White-Hat Only</p>
                <h1 id="home-heading" class="mt-5 w-[min(1120px,calc(100vw-2.5rem))] font-display text-[42px] leading-[1.08] text-white lg:text-[68px]">Earn Higher Rankings<br class="hidden lg:block"> with Backlinks from <span class="text-[#FF4917]">Real, Traffic-Driven</span> <br class="hidden lg:block"><span class="text-[#FF4917]">Websites</span></h1>
                <p class="mt-5 max-w-3xl text-[17px] leading-8 text-white/80 sm:text-[19px]">Pick from {{ $publishers }} checked publisher websites and grow your search visibility, visitors and sales with text links, contextual links, guest posts and niche edits. Every one is verified before you get your report.</p>
                <div class="mt-6 flex flex-col items-center gap-3 sm:flex-row sm:flex-wrap sm:justify-center">
                    <x-ui.button variant="primary" :href="route('customer.dashboard')">Start Building Links</x-ui.button>
                    <x-ui.button variant="outline" :href="$publisherListHref">See the publisher list</x-ui.button>
                </div>
                <ul class="mt-6 flex flex-wrap justify-center gap-x-6 gap-y-2">
                    @foreach ([$publishers.' Checked Publishers', 'White-Hat Placements Only', 'Delivered in 3–14 Days', 'Every Link Verified'] as $trust)
                        <li class="flex min-h-11 items-center gap-2 text-[15px] font-bold text-white">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[#FFF1EC] text-[#D63A10]">
                                <x-ui.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span>{{ $trust }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="why-pro px-5 py-16 lg:py-20" aria-labelledby="why-heading">
        <style>
            .why-pro {
                background-color: #f7f8fa;
                background-image:
                    radial-gradient(ellipse 70% 55% at 0% 0%, rgba(255, 73, 23, 0.08), transparent 58%),
                    linear-gradient(rgba(21, 32, 41, 0.045) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(21, 32, 41, 0.045) 1px, transparent 1px);
                background-size: auto, 56px 56px, 56px 56px;
            }
            .service-pick { border: 1px solid #E3E5E8; background: #fff; color: #152029; }
            .service-pick[aria-selected="true"] { border-color: #ff4917; background: #ff4917; color: #fff; box-shadow: 0 8px 24px rgba(255, 73, 23, .28); }
            .why-dots span {
                background: #ff4917;
                animation: why-dot-run 0.9s steps(1, end) infinite;
            }
            .why-dots span:last-child { animation-delay: -0.45s; }
            .service-dots > span:nth-child(-n + 3) { background: #ff4917; animation: service-dot-run 1.2s steps(1, end) infinite; }
            .service-dots > span:nth-child(2) { animation-delay: 0.4s; }
            .service-dots > span:nth-child(3) { animation-delay: 0.8s; }
            @keyframes service-dot-run {
                0%, 33% { opacity: 1; }
                34%, 100% { opacity: 0.28; }
            }
            @keyframes why-dot-run {
                0%, 49% { opacity: 1; }
                50%, 100% { opacity: 0.28; }
            }
            @media (prefers-reduced-motion: reduce) {
                .why-dots span, .service-dots > span { animation: none; }
                .why-dots span:last-child { opacity: 0.35; }
            }
        </style>
        <div class="mx-auto w-full max-w-7xl">
            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(16rem,0.7fr)] lg:gap-16">
                <div>
                    <p class="eyebrow text-[#8B939C]">[[ Why BacklinkPro ]]</p>
                    <h2 id="why-heading" class="mt-4 max-w-xl text-4xl text-[#152029] lg:text-[44px]">One Partner for Text Links, Contextual Links, Guest Posts &amp; Niche Edits</h2>
                </div>
                <p class="max-w-sm text-[15px] leading-7 text-[#4A5560] lg:pt-12">Why brands and agencies rely on BacklinkPro for safe, lasting link building.</p>
            </div>
            <dl class="mt-10 grid grid-cols-2 gap-x-6 gap-y-4 sm:grid-cols-4">
                @foreach ([
                    [$publishers, 'Checked Publishers'],
                    ['[N]+', 'Campaigns Completed'],
                    ['3–14 Days', 'Delivery Window'],
                    ['[YEAR]', 'Serving Clients Since'],
                ] as [$figure, $label])
                    <div>
                        <dt class="text-2xl font-extrabold text-[#152029]">{{ $figure }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-[#4A5560]">{{ $label }}</dd>
                    </div>
                @endforeach
            </dl>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach (array_slice($reasons, 0, 4) as $reason)
                    <article class="flex h-full flex-col rounded-xl border border-[#E6E8EB] bg-white p-5 shadow-[0_10px_28px_rgba(21,32,41,0.04)]">
                        <div class="flex items-start justify-between gap-3">
                            <span class="grid h-11 w-11 place-items-center rounded-full bg-[#152029] text-white">
                                <x-ui.icon :name="$reason['icon']" class="h-5 w-5" />
                            </span>
                            <span class="text-sm font-semibold tracking-wide text-[#C5CBD1]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h3 class="mt-6 text-[17px] text-[#152029]">{{ $reason['title'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-[#4A5560]">{{ $reason['text'] }}</p>
                    </article>
                @endforeach
            </div>
            <div class="why-dots mt-6 flex justify-center gap-1.5" aria-hidden="true">
                <span class="h-1.5 w-1.5 rounded-full"></span>
                <span class="h-1.5 w-1.5 rounded-full"></span>
            </div>
        </div>
    </section>

    <section class="bg-[#0c0c0e] px-6 py-16 lg:px-12 lg:py-24" aria-labelledby="services-heading" data-services>
        @php
            $orderedCards = collect($serviceCards)->sortByDesc(fn ($card) => ! empty($card['featured']))->values();
            $activeCard = $orderedCards->first();
            $servicePhotos = [
                'contextual-link' => ['file' => 'service-contextual.jpg', 'alt' => 'Open book with one glowing line under a warm lamp'],
                'text-link' => ['file' => 'service-text-link.jpg', 'alt' => 'Two laptops joined by a thread of orange light'],
                'guest-post' => ['file' => 'service-guest-post.jpg', 'alt' => 'Fountain pen on blank paper under a desk lamp'],
                'niche-edit' => ['file' => 'service-niche-edit.jpg', 'alt' => 'Laptop page with an orange block inserted into the article'],
            ];
        @endphp
        <div class="mx-auto grid w-full items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="eyebrow text-[#FF4917]">Our services</p>
                <h2 id="services-heading" class="mt-3 max-w-xl text-4xl text-white lg:text-5xl">Link building services built around real results</h2>
                <p class="mt-5 max-w-md text-[17px] leading-7 text-[#A7ADB4]">Order text links, contextual links, guest posts and niche edits on checked publisher websites—with clear metrics, fast delivery, and reports made for agencies and in-house teams.</p>
                @unless ($servicesPublished)
                    <p class="mt-4 max-w-md text-sm leading-6 text-[#A7ADB4]" role="status">Service prices are not published yet. A starting price appears once that service is active.</p>
                @endunless
                <div class="mt-8 grid grid-cols-2 justify-items-start gap-3 xl:flex xl:flex-wrap" role="tablist" aria-label="Services">
                    @foreach ($orderedCards as $card)
                        <button type="button" class="service-pick min-h-11 shrink-0 whitespace-nowrap rounded-full px-5 text-[15px] font-bold" role="tab" data-service-pick="{{ $card['slug'] }}" aria-selected="{{ $activeCard && $card['slug'] === $activeCard['slug'] ? 'true' : 'false' }}">{{ $card['title'] }}</button>
                    @endforeach
                </div>
            </div>
            <div class="overflow-hidden rounded-[28px] border border-[#E6E8EB] bg-white shadow-[0_18px_40px_rgba(21,32,41,0.08)]">
                <div class="service-dots flex items-center gap-2 bg-[#f3f4f6] px-4 py-3">
                    <span class="h-2.5 w-2.5 rounded-full"></span>
                    <span class="h-2.5 w-2.5 rounded-full"></span>
                    <span class="h-2.5 w-2.5 rounded-full"></span>
                    <p class="min-w-0 flex-1 truncate text-center">
                        <span class="inline-block max-w-full truncate rounded-full border border-[#e5e7eb] bg-white px-4 py-1 text-xs font-semibold text-[#6b7280]" data-service-path>services / {{ $activeCard['slug'] ?? 'service' }}</span>
                    </p>
                </div>

                @foreach ($orderedCards as $card)
                    <div class="grid items-center gap-6 p-6 sm:p-8 md:grid-cols-[minmax(0,1.1fr)_minmax(160px,0.9fr)]" data-service-panel="{{ $card['slug'] }}" @if (! $activeCard || $card['slug'] !== $activeCard['slug']) hidden @endif>
                        <div>
                            <p class="text-xs font-extrabold tracking-[0.16em] text-[#FF4917] uppercase">{{ ! empty($card['featured']) ? 'Most popular' : 'Service' }}</p>
                            <h3 class="mt-3 text-3xl text-[#152029] lg:text-4xl">{{ $card['title'] }}</h3>
                            @if (! empty($card['price']))
                                <p class="mt-3 text-lg font-extrabold text-[#C0340D]">{{ $card['price'] }}</p>
                            @endif
                            <p class="mt-3 text-[15px] leading-7 text-[#4A5560]">{{ $card['description'] }}</p>
                            <p class="mt-2 text-sm text-[#152029]"><span class="font-bold">Best for:</span> {{ $card['best_for'] }}</p>
                            <ul class="mt-5 flex flex-wrap gap-2">
                                @foreach (array_slice($card['options'] ?? [], 0, 3) as $option)
                                    <li><a href="{{ $option['href'] }}" class="inline-flex min-h-9 items-center rounded-full border border-[#E5E7EB] bg-[#F7F8FA] px-3 text-sm font-semibold text-[#152029] hover:border-[#FF4917] hover:text-[#FF4917]">{{ $option['label'] }}</a></li>
                                @endforeach
                            </ul>
                            <x-ui.button variant="primary" :href="$card['cta_href'] ?? url('/services/'.$card['slug'])" class="mt-6">{{ $card['button'] }}</x-ui.button>
                        </div>
                        <img src="{{ asset('images/services/'.($servicePhotos[$card['slug']]['file'] ?? 'service-contextual.jpg')) }}" alt="{{ $servicePhotos[$card['slug']]['alt'] ?? $card['title'] }}" class="h-56 w-full rounded-2xl object-cover md:h-full md:min-h-64">
                    </div>
                @endforeach
            </div>
        </div>
        <script>
            document.querySelectorAll('[data-service-pick]').forEach((button) => {
                button.addEventListener('click', () => {
                    const root = button.closest('[data-services]');
                    const slug = button.getAttribute('data-service-pick');
                    root.querySelectorAll('[data-service-pick]').forEach((item) => {
                        item.setAttribute('aria-selected', item === button ? 'true' : 'false');
                    });
                    root.querySelectorAll('[data-service-panel]').forEach((panel) => {
                        panel.hidden = panel.getAttribute('data-service-panel') !== slug;
                    });
                    const path = root.querySelector('[data-service-path]');
                    if (path) {
                        path.textContent = 'services / ' + slug;
                    }
                });
            });
        </script>
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
                text-align: center;
            }
            .control-item { display: flex; flex-direction: column; gap: 10px; align-items: center; border: 1px solid #FFD5C8; border-radius: 16px; background: #FFF7F4; padding: 24px 20px; text-align: center; }
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
            .diy-table { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
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
                padding: 12px 6px;
                border-right: 1px solid rgba(255, 255, 255, .28);
                border-bottom: 1px solid #B7C0C8;
                background: #152029;
                color: #fff;
                font-size: 12px;
                font-weight: 800;
                line-height: 1.25;
                text-align: center;
                vertical-align: middle;
            }
            .diy-table td {
                padding: 10px 6px;
                border-right: 1px solid #B7C0C8;
                border-bottom: 1px solid #B7C0C8;
                background: #fff;
                color: #152029;
                font-size: 13px;
                vertical-align: top;
            }
            .diy-table th:last-child, .diy-table td:last-child { border-right: 0; }
            .diy-table th:first-child, .diy-table td:first-child { padding-left: 14px; }
            .diy-table th:last-child, .diy-table td:last-child { padding-right: 14px; }
            .diy-table .c-num, .diy-table .c-keys { text-align: center; }
            .diy-table td { text-align: center; }
            .diy-url { display: flex; align-items: center; justify-content: center; gap: 6px; min-width: 0; color: #152029; font-weight: 800; }
            .diy-url span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .diy-star { flex: none; color: #FF4917; }
            .diy-metric { font-size: 14px; font-weight: 800; line-height: 1.2; white-space: nowrap; }
            .diy-chart { display: flex; flex-direction: column; align-items: center; gap: 4px; }
            .diy-chart .nd-spark { width: 72px; height: 22px; }
            .diy-country { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px; font-weight: 700; white-space: nowrap; }
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
                text-decoration: none;
                text-transform: uppercase;
            }
            .diy-publish:hover { background: #FF4917; color: #fff; }
            @media (max-width: 1099px) {
                .diy-table { width: 1200px; }
            }
            @media (max-width: 900px) {
                .control-title { font-size: 30px; }
                .control-lead { font-size: 16px; }
                .control-layout { grid-template-columns: 1fr; }
            }
        </style>
        <div class="control-wrap mx-auto px-6 py-14 lg:py-[88px]">
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
                                                <x-ui.sparkline :seed="$sample['domain'].'-ahrefs'" color="#E98989" :value="$sample['traffic']" />
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
                                                <x-ui.sparkline :seed="$sample['domain'].'-semrush'" color="#7DCEA0" :value="$sample['semrush_traffic']" />
                                            </div>
                                        </td>
                                        <td><span class="diy-metric">${{ number_format((float) $sample['price'], 2) }}</span></td>
                                        <td><a class="diy-publish" href="{{ route('publish.show', $sample['domain']) }}">Publish</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
            </div>
        </div>

                <div class="control-foot">
                    <ul class="flex flex-wrap justify-center gap-2">
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
                        <x-ui.button variant="primary" :href="$publisherListHref.'#all-publishers'">See the publisher list</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="bg-[#152029] px-6 py-16 lg:px-12 lg:py-24" aria-labelledby="home-niches-heading">
        <div class="overflow-hidden rounded-3xl border border-[#E3E5E8] bg-white">
            <div class="grid items-end gap-4 border-b border-[#E6E8EB] px-6 py-8 lg:grid-cols-[minmax(0,1fr)_minmax(16rem,0.55fr)] lg:px-10">
                <div>
                    <p class="eyebrow text-[#C0340D]">Niches</p>
                    <h2 id="home-niches-heading" class="mt-3 max-w-xl text-4xl text-[#152029] lg:text-[44px]">Browse Publisher Websites by Niche</h2>
                </div>
                <p class="max-w-sm text-[15px] leading-7 text-[#4A5560] lg:justify-self-end">Open a niche to see the publisher websites that accept guest posts, contextual links, niche edits and text links.</p>
            </div>
            <div class="-mr-px -mb-px grid bg-white sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($nicheTiles as $niche)
                    <a href="{{ route('niches.show', $niche['slug']) }}" class="group flex min-h-16 items-center gap-3 border-r border-b border-[#E6E8EB] bg-white px-6 py-4 transition hover:bg-[#FFF7F4] focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#D63A10]">
                        <span class="w-6 shrink-0 font-sans text-[13px] font-bold text-[#8B939C] tabular-nums group-hover:text-[#FF4917]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="min-w-0 truncate font-sans text-[15px] font-bold text-[#152029]">{{ $niche['name'] }}</span>
                        <span aria-hidden="true" class="ml-auto text-[#C5CBD1] transition group-hover:translate-x-0.5 group-hover:text-[#FF4917]">→</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-cta" aria-labelledby="home-cta-heading">
        <style>
            .home-cta { background: #F2F2F2; }
            .home-cta-wrap { max-width: none; margin: 0 auto; padding: 28px 24px 72px; }
            .home-cta-card {
                position: relative;
                display: grid;
                grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr);
                overflow: hidden;
                border-radius: 28px;
                background: #152029;
                box-shadow: 0 18px 40px rgba(21, 32, 41, .08);
            }
            .home-cta-card::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                z-index: 1;
                width: 100%;
                height: 6px;
                background: linear-gradient(90deg, #D63A10 0%, #FF4917 55%, #FFB199 100%);
            }
            .home-cta-copy { position: relative; display: flex; flex-direction: column; justify-content: center; border-radius: 28px 0 0 28px; background: #fff; padding: 48px 40px 44px; text-align: center; }
            .home-cta-kicker { margin: 0; color: #C0340D; font-size: 13px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
            .home-cta h2 { margin: 12px auto 0; max-width: 16em; color: #152029; font-size: 36px; line-height: 1.12; font-weight: 800; }
            .home-cta-text { max-width: 46ch; margin: 14px auto 0; color: #4A5560; font-size: 17px; line-height: 1.65; }
            .home-cta-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 28px; }
            .home-cta-primary, .home-cta-secondary {
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
            .home-cta-primary:focus-visible, .home-cta-secondary:focus-visible { outline: 2px solid #D63A10; outline-offset: 3px; }
            .home-cta-side { display: flex; flex-direction: column; justify-content: center; border-radius: 0 28px 28px 0; background: #152029; padding: 48px 32px; color: #fff; text-align: center; }
            .home-cta-side h3 { margin: 0; color: #fff; font-size: 18px; font-weight: 800; }
            .home-cta-steps { margin: 22px 0 0; padding: 0; list-style: none; }
            .home-cta-steps li { display: flex; flex-direction: column; gap: 10px; align-items: center; text-align: center; }
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
                .home-cta-copy { border-radius: 28px 28px 0 0; padding: 32px 22px; }
                .home-cta-side { border-radius: 0 0 28px 28px; padding: 32px 22px; }
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
                            <p><strong>Set the placement</strong><span>Add your target URL, anchor, and topic. Our team publishes the placement.</span></p>
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

