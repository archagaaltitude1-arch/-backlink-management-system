@php
    $footerCategories = class_exists(\App\Support\DemoNicheData::class)
        ? \App\Support\DemoNicheData::names()
        : [];
    $classicBrand = $classicBrand ?? false;
    $brand = $classicBrand
        ? [
            'websiteName' => 'BacklinkPro',
            'companyName' => 'Prime One Global (PVT) LTD.',
            'address' => '#146B, Goodshed Road',
            'city' => 'Thonikkal',
            'state' => 'Vavuniya',
            'postalCode' => '',
            'country' => 'NP, Sri Lanka',
            'footerDescription' => 'Backlinks on real publisher websites, placed and verified by our SEO team.',
            'footerCopyright' => '© '.now()->year.' BacklinkPro',
            'footerLinks' => [],
            'footerHtml' => null,
            'logoUrl' => null,
            'companyEmail' => '',
            'supportEmail' => '',
            'phoneNumber' => '',
            'theme' => ['accent' => '#ff4917'],
        ]
        : ($siteSettings ?? []);
    $accent = $brand['theme']['accent'] ?? '#FF4917';
    $websiteName = $brand['websiteName'] ?? 'BacklinkPro';
    $contactChannels = array_values(array_filter([
        ['label' => 'Email', 'value' => $brand['companyEmail'] ?? '', 'href' => filled($brand['companyEmail'] ?? null) ? 'mailto:'.$brand['companyEmail'] : null],
        ['label' => 'Support', 'value' => $brand['supportEmail'] ?? '', 'href' => filled($brand['supportEmail'] ?? null) ? 'mailto:'.$brand['supportEmail'] : null],
        ['label' => 'Phone', 'value' => $brand['phoneNumber'] ?? '', 'href' => null],
    ], fn (array $channel): bool => $channel['value'] !== ''));
@endphp

<footer class="site-footer">
    <style>
        .site-footer {
            border-top: 4px solid {{ $accent }};
            background: #152029;
            color: #fff;
        }
        .site-footer-main {
            background: radial-gradient(ellipse at 0% 0%, color-mix(in srgb, {{ $accent }} 16%, transparent), transparent 34%);
            padding: 64px 24px 48px;
        }
        .site-footer-grid {
            display: grid;
            grid-template-columns: minmax(260px, 340px) repeat(3, minmax(0, 1fr));
            gap: 48px 56px;
        }
        .site-footer-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border-radius: 12px;
            color: #fff;
            text-decoration: none;
        }
        .site-footer-mark {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border-radius: 8px;
            background: {{ $accent }};
            font-size: 14px;
        }
        .site-footer-name { font-size: 20px; font-weight: 800; letter-spacing: -0.02em; line-height: 1; }
        .site-footer-lead { max-width: 32ch; margin: 20px 0 0; color: #D5DCE3; font-size: 15px; line-height: 1.7; }
        .site-footer-trust { margin: 24px 0 0; padding: 0; list-style: none; }
        .site-footer-trust li { display: flex; align-items: center; gap: 10px; margin-top: 10px; color: #F2F4F6; font-size: 14px; }
        .site-footer-trust li::before {
            content: "✓";
            display: grid;
            width: 20px;
            height: 20px;
            flex: 0 0 20px;
            place-items: center;
            border-radius: 999px;
            background: {{ $accent }};
            color: #fff;
            font-size: 11px;
            font-weight: 700;
        }
        .site-footer h2 { margin: 0; color: {{ $accent }}; }
        .site-footer-links { margin: 16px 0 0; padding: 0; list-style: none; }
        .site-footer-links a, .site-footer-cats a {
            display: inline-flex;
            min-height: 40px;
            align-items: center;
            gap: 8px;
            color: #D5DCE3;
            font-size: 15px;
            text-decoration: none;
        }
        .site-footer-links a::before {
            content: "";
            width: 12px;
            height: 1px;
            background: color-mix(in srgb, {{ $accent }} 70%, transparent);
            transition: width 160ms ease;
        }
        .site-footer-links a:hover, .site-footer-cats a:hover, .site-footer-brand:hover { color: #fff; }
        .site-footer-links a:hover::before { width: 20px; }
        .site-footer a:focus-visible, .site-footer-cta:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
        .site-footer-cta {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            margin-top: 20px;
            border-radius: 999px;
            background: {{ $accent }};
            padding: 0 16px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }
        .site-footer-cta:hover { filter: brightness(0.92); }
        .site-footer-note { max-width: 28ch; margin: 16px 0 0; color: #B8C2CC; font-size: 14px; line-height: 1.6; }
        .site-footer-address { margin: 20px 0 0; color: #D5DCE3; font-size: 14px; font-style: normal; line-height: 1.6; }
        .site-footer-address strong { display: block; color: #fff; font-weight: 600; }
        .site-footer-channel { margin: 10px 0 0; color: #D5DCE3; font-size: 14px; line-height: 1.5; }
        .site-footer-channel span { display: block; color: #8B939C; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .site-footer-channel a { color: #D5DCE3; text-decoration: none; }
        .site-footer-cats { margin-top: 56px; border-top: 1px solid rgba(255, 255, 255, .1); padding-top: 40px; }
        .site-footer-cats-head { display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 16px; }
        .site-footer-cats-head p { margin: 0; color: #B8C2CC; font-size: 14px; }
        .site-footer-cats ul {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin: 16px 0 0;
            padding: 0;
            list-style: none;
        }
        .site-footer-bar { border-top: 1px solid rgba(255, 255, 255, .1); }
        .site-footer-bar-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px 20px;
            padding: 20px 24px;
            color: #B8C2CC;
            font-size: 14px;
        }
        .site-footer-bar p { margin: 0; }
        .site-footer-legal { display: flex; flex-wrap: wrap; gap: 8px 16px; margin: 0; padding: 0; list-style: none; }
        .site-footer-legal a { color: #D5DCE3; text-decoration: none; }
        .site-footer-legal a:hover { color: #fff; }
        .site-footer-html { max-width: 62ch; margin-top: 28px; color: #D5DCE3; font-size: 14px; line-height: 1.7; }
        .site-footer-html a { color: #fff; }
        .site-footer-logo { display: block; max-width: 250px; max-height: 80px; width: auto; height: auto; object-fit: contain; }
        @media (max-width: 1024px) {
            .site-footer-grid, .site-footer-cats ul { grid-template-columns: 1fr 1fr; }
            .site-footer-brand-col { grid-column: 1 / -1; }
        }
        @media (max-width: 640px) {
            .site-footer-grid, .site-footer-cats ul { grid-template-columns: 1fr; }
            .site-footer-main { padding-top: 40px; }
        }
        @media (min-width: 1024px) {
            .site-footer-main, .site-footer-bar-inner { padding-left: 24px; padding-right: 24px; }
        }
        @media (min-width: 1280px) {
            .site-footer-cats ul { grid-template-columns: repeat(8, minmax(0, 1fr)); }
        }
    </style>

    <div class="site-footer-main">
        <div class="site-footer-grid">
            <div class="site-footer-brand-col">
                <a href="{{ route('home') }}" class="site-footer-brand">
                    @if (! empty($brand['logoUrl']))
                        <img src="{{ $brand['logoUrl'] }}" alt="" class="site-footer-logo">
                    @else
                        <span class="site-footer-mark" aria-hidden="true">↗</span>
                    @endif
                    <span class="site-footer-name font-sans">{{ $websiteName }}</span>
                </a>
                <p class="site-footer-lead">{{ $brand['footerDescription'] ?? 'Backlinks on real publisher websites, placed and verified by our SEO team.' }}</p>
                <ul class="site-footer-trust">
                    <li>White-hat only</li>
                    <li>Every link verified</li>
                    <li>Delivery in 3–14 days</li>
                </ul>
            </div>

            <nav aria-label="Services">
                <h2 class="font-display">Services</h2>
                <ul class="site-footer-links">
                    <li><a href="{{ url('/services/guest-post') }}">Guest Post</a></li>
                    <li><a href="{{ route('sites.show', 'link-insertions-niche-edits') }}">Niche Edit</a></li>
                    <li><a href="{{ url('/services/contextual-link') }}">Contextual Link</a></li>
                    <li><a href="{{ url('/services/text-link') }}">Text Link</a></li>
                </ul>
            </nav>

            <nav aria-label="Explore">
                <h2 class="font-display">Explore</h2>
                <ul class="site-footer-links">
                    <li><a href="{{ route('publishers.index') }}">Publisher list</a></li>
                    <li><a href="{{ url('/niches') }}">Niches</a></li>
                    <li><a href="{{ route('sites.index') }}">Sites</a></li>
                    <li><a href="{{ url('/packages') }}">Packages</a></li>
                    <li><a href="{{ route('tools.index') }}">SEO Tools</a></li>
                    <li><a href="{{ url('/#countries') }}">Browse by Country</a></li>
                </ul>
            </nav>

            <div>
                <h2 class="font-display">Contact</h2>
                <a class="site-footer-cta" href="{{ route('contact') }}">Contact the team</a>
                <p class="site-footer-note">Questions about a campaign, a package, or a publisher.</p>
                <address class="site-footer-address">
                    <strong>{{ $brand['companyName'] ?? $websiteName }}</strong>
                    {{ $brand['address'] ?? '' }}<br>
                    {{ trim(($brand['city'] ?? '').', '.($brand['state'] ?? '').' '.($brand['postalCode'] ?? '')) }}<br>
                    {{ $brand['country'] ?? '' }}
                </address>
                @foreach ($contactChannels as $channel)
                    <p class="site-footer-channel">
                        <span>{{ $channel['label'] }}</span>
                        @if ($channel['href'])
                            <a href="{{ $channel['href'] }}">{{ $channel['value'] }}</a>
                        @else
                            {{ $channel['value'] }}
                        @endif
                    </p>
                @endforeach
            </div>
        </div>

        @if ($footerCategories !== [])
            <div class="site-footer-cats">
                <div class="site-footer-cats-head">
                    <h2 class="font-display">Categories</h2>
                    <p>Publisher websites by niche</p>
                </div>
                <ul>
                    @foreach ($footerCategories as $category)
                        <li><a href="{{ route('niches.show', \Illuminate\Support\Str::slug($category)) }}">{{ $category }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($brand['footerHtml']))
            <div class="site-footer-html">{!! $brand['footerHtml'] !!}</div>
        @endif
    </div>

    <div class="site-footer-bar">
        <div class="site-footer-bar-inner">
            <p>{{ $brand['footerCopyright'] ?? $brand['copyright'] ?? '' }}</p>
            @if (! empty($brand['footerLinks']))
                <ul class="site-footer-legal">
                    @foreach ($brand['footerLinks'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" @if (($link['target'] ?? '_self') === '_blank') target="_blank" rel="noopener noreferrer" @endif>{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            @elseif ($classicBrand)
                <p>White-hat placements, verified before delivery</p>
            @endif
        </div>
    </div>
</footer>
