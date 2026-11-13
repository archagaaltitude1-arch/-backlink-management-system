@php
    $footerCategories = class_exists(\App\Support\DemoNicheData::class)
        ? \App\Support\DemoNicheData::names()
        : [];
@endphp

<footer class="site-footer">
    <style>
        .site-footer { background: #152029; color: #fff; border-top: 4px solid #FF4917; }
        .site-footer-main {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr 1fr;
            gap: 36px 28px;
            max-width: 1240px;
            margin: 0 auto;
            padding: 56px 24px 28px;
        }
        .site-footer-brand { display: inline-flex; align-items: center; gap: 10px; border-radius: 12px; color: #fff; text-decoration: none; }
        .site-footer-mark { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 10px; background: #FF4917; }
        .site-footer-name { font-size: 20px; font-weight: 800; line-height: 1; }
        .site-footer-lead { max-width: 280px; margin: 16px 0 0; color: #D5DCE3; font-size: 15px; line-height: 1.7; }
        .site-footer h2 { margin: 0; color: #FF4917; font-size: 13px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .site-footer-links { margin: 12px 0 0; padding: 0; list-style: none; }
        .site-footer-links a, .site-footer-contact a {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            color: #D5DCE3;
            font-size: 15px;
            text-decoration: none;
        }
        .site-footer-links a:hover, .site-footer-contact a:hover { color: #fff; }
        .site-footer-links a:focus-visible, .site-footer-contact a:focus-visible, .site-footer-brand:focus-visible, .site-footer-cats a:focus-visible, .site-footer-bar a:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
        .site-footer-contact { margin-top: 12px; color: #D5DCE3; font-size: 15px; line-height: 1.7; }
        .site-footer-contact p { margin: 0; }
        .site-footer-cats { max-width: 1240px; margin: 0 auto; padding: 8px 24px 36px; }
        .site-footer-cats ul { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0 0; padding: 0; list-style: none; }
        .site-footer-cats a {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 999px;
            padding: 0 16px;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }
        .site-footer-cats a:hover { border-color: #D63A10; background: #D63A10; }
        .site-footer-bar { border-top: 1px solid rgba(255, 255, 255, .1); }
        .site-footer-bar-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px 16px;
            max-width: 1240px;
            margin: 0 auto;
            padding: 16px 24px;
            color: #B8C2CC;
            font-size: 14px;
        }
        .site-footer-bar p { margin: 0; }
        .site-footer-bar a { color: #D5DCE3; text-decoration: none; }
        .site-footer-bar a:hover { color: #fff; }
        @media (max-width: 980px) {
            .site-footer-main { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .site-footer-main { grid-template-columns: 1fr; padding-top: 40px; }
        }
    </style>

    <div class="site-footer-main">
        <div>
            <a href="{{ url('/') }}" class="site-footer-brand">
                <span class="site-footer-mark" aria-hidden="true">
                    <x-ui.icon name="link" class="h-5 w-5" />
                </span>
                <span class="site-footer-name font-display">BacklinkPro</span>
            </a>
            <p class="site-footer-lead">Backlinks on real publisher websites, placed and verified by our SEO team.</p>
        </div>

        <nav aria-label="Services">
            <h2 class="font-display">Services</h2>
            <ul class="site-footer-links">
                <li><a href="{{ url('/services/guest-post') }}">Guest Post</a></li>
                <li><a href="{{ url('/services/niche-edit') }}">Niche Edit</a></li>
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
                <p><a href="mailto:[YOUR EMAIL]">[YOUR EMAIL]</a></p>
                <p>[YOUR PHONE]</p>
                <p>[YOUR ADDRESS]</p>
            </div>
        </div>
    </div>

    @if ($footerCategories !== [])
        <div class="site-footer-cats">
            <h2 class="font-display">Categories</h2>
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
            <p>
                <a href="#privacy">Privacy Policy</a>
                <span aria-hidden="true"> · </span>
                <a href="#terms">Terms of Service</a>
            </p>
        </div>
    </div>
</footer>
