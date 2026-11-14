@extends('layouts.public')

@section('document_title', 'Packages – BacklinkPro')

@section('meta_description', 'Display backlink packages and the guest post package prices already set on BacklinkPro.')

@section('content')
    <style>
        .pkg-wrap { max-width: 1240px; margin: 0 auto; padding: 56px 24px 88px; }
        .pkg-crumb { color: #4A5560; font-size: 14px; font-weight: 700; }
        .pkg-crumb a { color: inherit; text-decoration: none; }
        .pkg-crumb a:hover { color: #C0340D; }
        .pkg-crumb a:focus-visible, .pkg-card:focus-visible, .pkg-price-card:focus-visible, .pkg-btn:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .pkg-title { margin: 16px 0 0; color: #152029; font-size: 36px; line-height: 1.1; font-weight: 800; }
        .pkg-lead { max-width: 720px; margin: 16px 0 0; color: #4A5560; font-size: 17px; line-height: 1.7; }
        .pkg-block { margin-top: 36px; }
        .pkg-block h2 { margin: 0; color: #152029; font-size: 26px; line-height: 1.2; font-weight: 800; }
        .pkg-block > p { max-width: 720px; margin: 10px 0 0; color: #4A5560; font-size: 16px; line-height: 1.6; }
        .pkg-prices, .pkg-grid { display: grid; gap: 16px; margin: 20px 0 0; padding: 0; list-style: none; }
        .pkg-prices { grid-template-columns: 1fr; }
        .pkg-grid { grid-template-columns: 1fr; }
        .pkg-price-card, .pkg-card { display: flex; min-height: 100%; flex-direction: column; border: 1px solid #E3E5E8; border-top: 3px solid #FF4917; border-radius: 20px; background: #fff; padding: 22px; }
        .pkg-price-card.is-popular, .pkg-card.is-popular { border: 2px solid #FF4917; background: #FFF6F2; }
        .pkg-kicker { margin: 0; color: #C0340D; font-size: 13px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; }
        .pkg-amount { margin: 12px 0 0; color: #152029; font-size: 40px; line-height: 1; font-weight: 800; }
        .pkg-each { margin: 8px 0 0; color: #4A5560; font-size: 14px; font-weight: 700; }
        .pkg-name { margin: 14px 0 0; color: #152029; font-size: 22px; line-height: 1.2; font-weight: 800; }
        .pkg-meta { margin: 8px 0 0; color: #4A5560; font-size: 15px; line-height: 1.5; }
        .pkg-actions { display: flex; flex-direction: column; gap: 8px; margin-top: auto; padding-top: 18px; }
        .pkg-btn { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; border-radius: 12px; padding: 10px 16px; font-size: 15px; font-weight: 800; text-decoration: none; }
        .pkg-btn-primary { border: 1.5px solid #D63A10; background: #D63A10; color: #fff; }
        .pkg-btn-primary:hover { border-color: #FF4917; background: #FF4917; }
        .pkg-btn-line { border: 1.5px solid #152029; background: #fff; color: #152029; }
        .pkg-btn-line:hover { background: #F2F2F2; }
        .pkg-list { margin: 16px 0 0; padding: 0; list-style: none; }
        .pkg-list li { display: flex; gap: 8px; margin-top: 8px; color: #152029; font-size: 14px; line-height: 1.45; }
        .pkg-list li::before { content: ""; width: 8px; height: 8px; flex: 0 0 8px; margin-top: 6px; border-radius: 50%; background: #FF4917; }
        @media (min-width: 760px) {
            .pkg-prices { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .pkg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1100px) {
            .pkg-title { font-size: 44px; }
            .pkg-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
    </style>
    <section class="bg-[#F2F2F2]">
        <div class="pkg-wrap">
            <nav class="pkg-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <span class="text-navy">Packages</span>
            </nav>
            <h1 class="pkg-title font-display">Packages</h1>
            <p class="pkg-lead">Each package has its own price. A larger package costs less per backlink. Guest posts cost more because a new article is included, and a higher Domain Rating costs more again.</p>

            @if ($guestPostPrices !== [])
                <section class="pkg-block" aria-labelledby="guest-post-prices">
                    <h2 id="guest-post-prices" class="font-display">Guest post package prices</h2>
                    <p>These prices are already set for the guest post service.</p>
                    <ul class="pkg-prices">
                        @foreach ($guestPostPrices as $tier)
                            <li>
                                <article @class(['pkg-price-card', 'is-popular' => $tier['popular']])>
                                    <p class="pkg-kicker">Guest post</p>
                                    <p class="pkg-amount font-display">{{ $tier['price'] }}</p>
                                    <h3 class="pkg-name font-display">{{ $tier['name'] }}</h3>
                                    @if ($tier['summary'] !== '')
                                        <p class="pkg-meta">{{ $tier['summary'] }}</p>
                                    @endif
                                    <div class="pkg-actions">
                                        <a class="pkg-btn pkg-btn-primary" href="{{ route('services.show', 'guest-post') }}#packages">Package details</a>
                                        <a class="pkg-btn pkg-btn-line" href="{{ route('publishers.index') }}">Browse publishers</a>
                                    </div>
                                </article>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="pkg-block" aria-labelledby="display-packages">
                <h2 id="display-packages" class="font-display">Display backlink packages</h2>
                <p>The price follows the number of backlinks. Growth and Professional cost less per link than Starter.</p>
                <ul class="pkg-grid">
                    @foreach ($packages as $package)
                        <li id="{{ $package['slug'] }}">
                            <article @class(['pkg-card', 'is-popular' => $package['popular']])>
                                <p class="pkg-kicker">{{ $package['name'] }}</p>
                                <p class="pkg-amount font-display">{{ $package['price_label'] }}</p>
                                @if ($package['each_label'])
                                    <p class="pkg-each">{{ $package['each_label'] }}</p>
                                @endif
                                <h3 class="pkg-name font-display">{{ $package['size'] }}</h3>
                                <p class="pkg-meta">{{ $package['summary'] }}</p>
                                <div class="pkg-actions">
                                    <a class="pkg-btn pkg-btn-primary" href="{{ route('packages.show', $package['slug']) }}">Package details</a>
                                    <a class="pkg-btn pkg-btn-line" href="{{ route('publishers.index') }}">Browse publishers</a>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </section>
@endsection
