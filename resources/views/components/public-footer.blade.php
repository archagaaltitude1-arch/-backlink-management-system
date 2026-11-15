@php
    $footerCategories = class_exists(\App\Support\DemoNicheData::class)
        ? \App\Support\DemoNicheData::names()
        : [];

    $contactChannels = array_values(array_filter([
        ['label' => 'Email', 'value' => '[YOUR EMAIL]', 'href' => 'mailto:[YOUR EMAIL]'],
        ['label' => 'Phone', 'value' => '[YOUR PHONE]', 'href' => null],
        ['label' => 'Office', 'value' => '[YOUR ADDRESS]', 'href' => null],
    ], fn (array $channel): bool => ! str_starts_with($channel['value'], '[')));
@endphp

<footer class="site-footer">
    <style>
        .site-footer {
            background:
                radial-gradient(ellipse 42% 80% at 0% 0%, rgba(255, 73, 23, 0.16), transparent 58%),
                #152029;
            color: #fff;
            border-top: 4px solid #FF4917;
        }
        .site-footer-main {
            display: grid;
            grid-template-columns: minmax(240px, 1.35fr) repeat(3, minmax(150px, 1fr));
            gap: 40px 48px;
            margin: 0 auto;
            padding: 64px clamp(24px, 4vw, 56px) 36px;
        }
        .site-footer-brand { display: inline-flex; align-items: center; gap: 10px; border-radius: 12px; color: #fff; text-decoration: none; }
        .site-footer-mark { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 10px; background: #FF4917; }
        .site-footer-name { font-size: 20px; font-weight: 800; line-height: 1; }
        .site-footer-lead { max-width: 34ch; margin: 16px 0 0; color: #D5DCE3; font-size: 15px; line-height: 1.7; }
        .site-footer-trust { display: flex; flex-wrap: wrap; gap: 8px; margin: 20px 0 0; padding: 0; list-style: none; }
        .site-footer-trust li {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
            padding: 0 12px;
            color: #F2F4F6;
            font-size: 13px;
            font-weight: 700;
        }
        .site-footer h2 { margin: 0; color: #FF4917; font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .site-footer-links { margin: 14px 0 0; padding: 0; list-style: none; }
        .site-footer-links a, .site-footer-contact a {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            color: #D5DCE3;
            font-size: 15px;
            text-decoration: none;
        }
        .site-footer-links a:hover, .site-footer-contact a:hover { color: #fff; }
        .site-footer-links a:focus-visible, .site-footer-contact a:focus-visible, .site-footer-brand:focus-visible, .site-footer-cta:focus-visible, .site-footer-cats a:focus-visible, .site-footer-bar a:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
        .site-footer-contact { margin-top: 14px; }
        .site-footer-cta {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            border-radius: 12px;
            background: #FF4917;
            padding: 0 16px;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            text-decoration: none;
        }
        .site-footer-cta:hover { background: #D63A10; }
        .site-footer-note { max-width: 28ch; margin: 12px 0 0; color: #B8C2CC; font-size: 14px; line-height: 1.6; }
        .site-footer-channel { margin: 10px 0 0; color: #D5DCE3; font-size: 14px; line-height: 1.5; }
        .site-footer-channel span { display: block; color: #8B939C; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .site-footer-cats {
            margin: 0 auto;
            border-top: 1px solid rgba(255, 255, 255, .1);
            padding: 28px clamp(24px, 4vw, 56px) 32px;
        }
        .site-footer-cats-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 8px 16px; }
        .site-footer-cats-head p { margin: 0; color: #B8C2CC; font-size: 14px; }
        .site-footer-cats ul {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 2px 20px;
            margin: 14px 0 0;
            padding: 0;
            list-style: none;
        }
        .site-footer-cats a {
            display: inline-flex;
            min-height: 36px;
            align-items: center;
            color: #D5DCE3;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }
        .site-footer-cats a:hover { color: #fff; }
        .site-footer-bar { border-top: 1px solid rgba(255, 255, 255, .1); }
        .site-footer-bar-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px 20px;
            margin: 0 auto;
            padding: 18px clamp(24px, 4vw, 56px);
            color: #B8C2CC;
            font-size: 14px;
        }
        .site-footer-bar p { margin: 0; }
        .site-footer-legal { display: flex; flex-wrap: wrap; gap: 8px 18px; }
        .site-footer-bar a { color: #D5DCE3; text-decoration: none; }
        .site-footer-bar a:hover { color: #fff; }
        @media (max-width: 980px) {
            .site-footer-main { grid-template-columns: 1fr 1fr; }
            .site-footer-brand-col { grid-column: 1 / -1; }
        }
        @media (max-width: 640px) {
            .site-footer-main { grid-template-columns: 1fr; padding-top: 40px; }
            .site-footer-cats ul { grid-template-columns: 1fr 1fr; }
        }
    </style>

    <div class="site-footer-main">
        <div class="site-footer-brand-col">
            <a href="{{ route('home') }}" class="site-footer-brand">
                <span class="site-footer-mark" aria-hidden="true">
                    <x-ui.icon name="link" class="h-5 w-5" />
                </span>
                <span class="site-footer-name font-display">BacklinkPro</span>
            </a>
            <p class="site-footer-lead">Backlinks on real publisher websites, placed and verified by our SEO team.</p>
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
            <div class="site-footer-contact">
                <a class="site-footer-cta" href="{{ route('contact') }}">Contact the team</a>
                <p class="site-footer-note">Questions about a campaign, a package, or a publisher.</p>
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

    <div class="site-footer-bar">
        <div class="site-footer-bar-inner">
            <p>&copy; {{ date('Y') }} BacklinkPro. All rights reserved.</p>
            <nav class="site-footer-legal" aria-label="Legal">
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Service</a>
            </nav>
        </div>
    </div>
</footer>
