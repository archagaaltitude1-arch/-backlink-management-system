@extends('layouts.public')

@php
    $domain = $profile['domain'];
    $compact = fn ($value) => \App\Support\DemoNicheData::compact(is_numeric($value) ? (int) $value : null);
    $show = fn ($value) => $value === null || $value === '' ? '' : $value;
    $loginHref = \Illuminate\Support\Facades\Route::has('auth.login') ? route('auth.login') : url('/login');
    $cartReady = ! $profile['demo'] && $profile['website_id'] && \Illuminate\Support\Facades\Route::has('orders.cart.items.store');
    $saveReady = ! $profile['demo'] && $profile['website_id'] && \Illuminate\Support\Facades\Route::has('customer.saved-websites.store');
    $articleHref = \Illuminate\Support\Facades\Route::has('articles.create') ? route('articles.create') : null;
    $services = [
        ['value' => 'guest_post', 'label' => 'Guest Post'],
        ['value' => 'contextual_link', 'label' => 'Contextual Link'],
        ['value' => 'niche_edit', 'label' => 'Niche Edit'],
        ['value' => 'text_link', 'label' => 'Text Link'],
    ];
    $metrics = [
        ['label' => 'DR', 'value' => $show($profile['dr']), 'note' => 'Domain Rating from 0 to 100.'],
        ['label' => 'DA', 'value' => $show($profile['da']), 'note' => 'Domain Authority from 0 to 100.'],
        ['label' => 'PA', 'value' => $show($profile['pa']), 'note' => 'Page Authority for a typical page.'],
        ['label' => 'Trust Flow', 'value' => $show($profile['tf']), 'note' => 'How trusted the site’s links look.'],
        ['label' => 'Citation Flow', 'value' => $show($profile['cf']), 'note' => 'How widely other sites mention it.'],
        ['label' => 'Monthly traffic', 'value' => $compact($profile['traffic']), 'note' => 'Estimated visits in a recent month.'],
        ['label' => 'Organic keywords', 'value' => $compact($profile['keywords']), 'note' => 'Keywords the site ranks for.'],
        ['label' => 'Backlinks', 'value' => $compact($profile['backlinks']), 'note' => 'Links pointing at this website.'],
        ['label' => 'Referring domains', 'value' => $compact($profile['referring_domains']), 'note' => 'Unique websites that link here.'],
        ['label' => 'Spam score', 'value' => $show($profile['spam']), 'note' => 'A lower number means fewer spam signals.'],
    ];
    $priceLabel = $profile['price'] === null ? '' : '$'.number_format((float) $profile['price'], 2);
    $change = $profile['traffic_change'];
@endphp

@section('document_title', $domain.' – Guest Post & Link Placement | BacklinkPro')

@section('meta_description', mb_substr($profile['about_p1'], 0, 155))

@section('content')
    <style>
        .wd-page { background: #F2F2F2; color: #152029; }
        .wd-wrap { max-width: 1240px; margin: 0 auto; padding: 40px 24px 72px; }
        .wd-crumb { color: #4A5560; font-size: 14px; font-weight: 700; }
        .wd-crumb a { color: #4A5560; }
        .wd-crumb a:hover { color: #C0340D; }
        .wd-note { margin: 12px 0 0; color: #4A5560; font-size: 13px; }
        .wd-top { display: grid; gap: 16px; margin-top: 20px; }
        .wd-card, .wd-order, .wd-block { background: #fff; border: 1px solid #E3E5E8; border-radius: 24px; }
        .wd-card, .wd-block { padding: 24px; }
        .wd-order { padding: 20px; }
        .wd-id { display: flex; gap: 14px; align-items: flex-start; }
        .wd-avatar { display: grid; place-items: center; width: 52px; height: 52px; flex: none; border-radius: 12px; background: #152029; color: #fff; font-family: Montserrat, sans-serif; font-size: 22px; font-weight: 800; }
        .wd-h1 { margin: 0; font-family: Montserrat, sans-serif; font-size: 32px; line-height: 1.15; font-weight: 800; word-break: break-word; }
        .wd-visit { color: #C0340D; font-weight: 700; }
        .wd-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .wd-badge { display: inline-flex; align-items: center; min-height: 32px; padding: 0 10px; border-radius: 999px; font-size: 13px; font-weight: 700; }
        .wd-badge-green { background: #E7F6EE; color: #1F7A45; }
        .wd-badge-grey { background: #F2F2F2; color: #4A5560; }
        .wd-kicker { margin: 20px 0 8px; color: #4A5560; font-size: 13px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .wd-pills { display: flex; flex-wrap: wrap; gap: 8px; }
        .wd-pill { display: inline-flex; align-items: center; min-height: 36px; padding: 0 12px; border: 1px solid #E3E5E8; border-radius: 999px; background: #fff; color: #152029; font-size: 14px; font-weight: 700; }
        .wd-pill-primary { border-color: transparent; background: #FFF1EC; color: #C0340D; }
        .wd-price { margin: 0; font-family: Montserrat, sans-serif; font-size: 36px; line-height: 1; font-weight: 800; }
        .wd-credits { margin: 8px 0 0; color: #4A5560; font-size: 15px; }
        .wd-services { display: grid; gap: 4px; margin: 16px 0 0; padding: 0; border: 0; }
        .wd-choice { display: flex; align-items: center; gap: 10px; min-height: 44px; font-weight: 700; }
        .wd-muted { margin: 8px 0 0; color: #4A5560; font-size: 13px; }
        .wd-qty { display: flex; align-items: center; gap: 10px; margin-top: 14px; font-weight: 700; }
        .wd-qty input { width: 84px; min-height: 44px; border: 1px solid #E3E5E8; border-radius: 12px; padding: 0 12px; font: inherit; }
        .wd-btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 44px; margin-top: 12px; border-radius: 12px; padding: 10px 18px; font-size: 15px; font-weight: 700; }
        .wd-btn-primary { border: 0; background: #D63A10; color: #fff; }
        .wd-btn-primary:hover { background: #FF4917; }
        .wd-btn-primary:disabled { cursor: not-allowed; opacity: .55; }
        .wd-btn-outline { border: 1.5px solid #152029; background: #fff; color: #152029; }
        .wd-btn:focus-visible, .wd-pill:focus-visible, .wd-visit:focus-visible, .wd-crumb a:focus-visible, .wd-inline:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .wd-fine { margin: 12px 0 0; color: #4A5560; font-size: 14px; line-height: 1.6; }
        .wd-h2 { margin: 0 0 12px; font-family: Montserrat, sans-serif; font-size: 28px; line-height: 1.2; font-weight: 800; }
        .wd-about p { margin: 0 0 14px; color: #4A5560; font-size: 17px; line-height: 1.7; }
        .wd-about p:last-child { margin-bottom: 0; }
        .wd-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
        .wd-metric { border: 1px solid #E3E5E8; border-radius: 16px; background: #fff; padding: 14px; }
        .wd-metric dt { margin: 0; color: #4A5560; font-size: 13px; font-weight: 700; }
        .wd-metric dd { margin: 6px 0 0; font-family: Montserrat, sans-serif; font-size: 28px; line-height: 1; font-weight: 800; }
        .wd-metric p { margin: 8px 0 0; color: #4A5560; font-size: 12px; line-height: 1.4; }
        .wd-split { display: grid; gap: 16px; }
        .wd-big { margin: 0; font-family: Montserrat, sans-serif; font-size: 40px; line-height: 1; font-weight: 800; }
        .wd-up { color: #1F7A45; font-weight: 700; }
        .wd-down { color: #B42318; font-weight: 700; }
        .wd-chart { position: relative; max-width: 100%; }
        .wd-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
        .wd-mini { display: flex; flex-direction: column; gap: 8px; border: 1px solid #E3E5E8; border-radius: 16px; background: #fff; padding: 16px; }
        .wd-mini strong { word-break: break-word; }
        .wd-row { display: flex; flex-wrap: wrap; gap: 6px; }
        .wd-chip { border-radius: 999px; background: #F2F2F2; padding: 2px 8px; color: #152029; font-size: 12px; font-weight: 700; }
        .wd-inline { color: #C0340D; font-weight: 700; }
        .wd-guide { margin-top: 16px; border-radius: 16px; background: #F2F2F2; padding: 16px 16px 16px 32px; color: #152029; }
        .wd-guide li { margin: 6px 0; }
        .wd-stack > * + * { margin-top: 16px; }
        @media (min-width: 1024px) {
            .wd-top { grid-template-columns: minmax(0, 1fr) 320px; align-items: start; }
            .wd-order { position: sticky; top: 88px; }
            .wd-split { grid-template-columns: 220px minmax(0, 1fr); align-items: center; }
        }
    </style>

    <section class="wd-page">
        <div class="wd-wrap">
            <nav class="wd-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ url('/marketplace') }}">Marketplace</a>
                <span aria-hidden="true"> / </span>
                @if ($profile['primary_slug'])
                    <a href="{{ route('niches.show', $profile['primary_slug']) }}">{{ $profile['primary'] }}</a>
                    <span aria-hidden="true"> / </span>
                @endif
                <span class="text-navy">{{ $domain }}</span>
            </nav>
            @if ($profile['demo'])
                <p class="wd-note">Sample data for preview</p>
            @endif

            <div class="wd-top">
                <article class="wd-card">
                    <div class="wd-id">
                        <span class="wd-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($domain, 0, 1)) }}</span>
                        <div>
                            <h1 class="wd-h1">{{ $domain }}</h1>
                            <a class="wd-visit" href="{{ $profile['url'] }}" rel="nofollow noopener" target="_blank">Visit site ↗</a>
                        </div>
                    </div>
                    <div class="wd-badges">
                        <span class="wd-badge wd-badge-green">{{ $profile['link_type'] }}</span>
                        @if ($profile['language'])
                            <span class="wd-badge wd-badge-grey">{{ $profile['language'] }}</span>
                        @endif
                        @if ($profile['updated_at'])
                            <span class="wd-badge wd-badge-grey">Updated {{ $profile['updated_at']->diffForHumans() }}</span>
                        @endif
                    </div>
                    <h2 class="wd-kicker">Categories</h2>
                    <div class="wd-pills">
                        @forelse ($profile['categories'] as $category)
                            <a class="wd-pill {{ $loop->first ? 'wd-pill-primary' : '' }}" href="{{ route('niches.show', \Illuminate\Support\Str::slug($category)) }}">{{ $category }}</a>
                        @empty
                            <span class="wd-pill"></span>
                        @endforelse
                    </div>
                </article>

                <aside class="wd-order" aria-label="Order">
                    <p class="wd-price">{{ $priceLabel }}</p>
                    @if ($profile['credits'] !== null)
                        <p class="wd-credits">or {{ $profile['credits'] }} credits</p>
                    @endif
                    @if ($profile['demo'])
                        <fieldset class="wd-services">
                            <legend class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)">Service</legend>
                            @foreach ($services as $service)
                                <label class="wd-choice">
                                    <input type="radio" name="product_key_preview" value="{{ $service['value'] }}" @checked($loop->first) disabled>
                                    {{ $service['label'] }}
                                </label>
                            @endforeach
                        </fieldset>
                        <p class="wd-muted">Final price may vary by service</p>
                        <label class="wd-qty" for="order-quantity">Quantity
                            <input id="order-quantity" type="number" min="1" max="10" value="1" disabled>
                        </label>
                        <button class="wd-btn wd-btn-primary" type="button" disabled title="Demo data">Add to cart</button>
                    @elseif (auth()->check() && $cartReady)
                        <form method="POST" action="{{ route('orders.cart.items.store') }}">
                            @csrf
                            <input type="hidden" name="website_id" value="{{ $profile['website_id'] }}">
                            <fieldset class="wd-services">
                                <legend class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)">Service</legend>
                                @foreach ($services as $service)
                                    <label class="wd-choice">
                                        <input type="radio" name="product_key" value="{{ $service['value'] }}" @checked($loop->first)>
                                        {{ $service['label'] }}
                                    </label>
                                @endforeach
                            </fieldset>
                            <p class="wd-muted">Final price may vary by service</p>
                            <label class="wd-qty" for="order-quantity">Quantity
                                <input id="order-quantity" name="quantity" type="number" min="1" max="10" value="1" required>
                            </label>
                            <button class="wd-btn wd-btn-primary" type="submit">Add to cart</button>
                        </form>
                    @else
                        <fieldset class="wd-services">
                            <legend class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)">Service</legend>
                            @foreach ($services as $service)
                                <label class="wd-choice">
                                    <input type="radio" name="product_key_preview" value="{{ $service['value'] }}" @checked($loop->first) disabled>
                                    {{ $service['label'] }}
                                </label>
                            @endforeach
                        </fieldset>
                        <p class="wd-muted">Final price may vary by service</p>
                        <a class="wd-btn wd-btn-primary" href="{{ $loginHref }}">Login to buy</a>
                    @endif

                    @if (auth()->check() && $saveReady)
                        <form method="POST" action="{{ route('customer.saved-websites.store') }}">
                            @csrf
                            <input type="hidden" name="website_id" value="{{ $profile['website_id'] }}">
                            <button class="wd-btn wd-btn-outline" type="submit">Save website</button>
                        </form>
                    @else
                        <a class="wd-btn wd-btn-outline" href="{{ $loginHref }}">Save website</a>
                    @endif
                    <p class="wd-fine">You'll add your target URL, anchor text and topic at checkout.</p>
                    @if ($articleHref)
                        <a class="wd-inline" href="{{ $articleHref }}">Have your own article? Submit it →</a>
                    @endif
                </aside>
            </div>

            <div class="wd-stack">
                <section class="wd-block wd-about" aria-labelledby="about-heading">
                    <h2 class="wd-h2" id="about-heading">About {{ $domain }}</h2>
                    <p>{{ $profile['about_p1'] }}</p>
                    <p>{{ $profile['about_p2'] }}</p>
                </section>

                <section class="wd-block" aria-labelledby="metrics-heading">
                    <h2 class="wd-h2" id="metrics-heading">SEO metrics</h2>
                    <dl class="wd-metrics">
                        @foreach ($metrics as $metric)
                            <div class="wd-metric">
                                <dt>{{ $metric['label'] }}</dt>
                                <dd>{{ $metric['value'] }}</dd>
                                <p>{{ $metric['note'] }}</p>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <section class="wd-block" aria-labelledby="traffic-heading">
                    <h2 class="wd-h2" id="traffic-heading">Traffic overview</h2>
                    <div class="wd-split">
                        <div>
                            <p class="wd-big">{{ $compact($profile['traffic']) }}</p>
                            <p class="wd-muted">Current monthly traffic</p>
                            @if ($change !== null)
                                <p class="{{ $change >= 0 ? 'wd-up' : 'wd-down' }}">{{ $change >= 0 ? '↑' : '↓' }} {{ ($change >= 0 ? '+' : '').$change }}%</p>
                            @endif
                        </div>
                        <div>
                            @if ($profile['has_traffic_history'])
                                <x-ui.line-chart :points="$profile['traffic_series']" />
                                <p class="wd-muted">Estimated organic visits per month.</p>
                            @else
                                <p class="wd-muted">Traffic history will appear after the next metrics update.</p>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="wd-block" aria-labelledby="countries-heading">
                    <h2 class="wd-h2" id="countries-heading">Where visitors come from</h2>
                    @if ($profile['countries_ready'])
                        <x-ui.bar-list :items="$profile['top_countries']" />
                    @else
                        <p class="wd-muted">Country data coming soon.</p>
                    @endif
                </section>

                <section class="wd-block" aria-labelledby="also-heading">
                    <h2 class="wd-h2" id="also-heading">Also check these {{ $profile['primary'] !== '' ? $profile['primary'].' ' : '' }}websites</h2>
                    <div class="wd-cards">
                        @foreach ($profile['similar'] as $site)
                            <article class="wd-mini">
                                <strong>{{ $site['domain'] }}</strong>
                                @if ($site['category'] !== '')
                                    <a class="wd-pill" href="{{ route('niches.show', \Illuminate\Support\Str::slug($site['category'])) }}">{{ $site['category'] }}</a>
                                @endif
                                <div class="wd-row">
                                    <span class="wd-chip">DR {{ $site['dr'] ?? '' }}</span>
                                    <span class="wd-chip">DA {{ $site['da'] ?? '' }}</span>
                                    <span class="wd-chip">{{ $compact($site['traffic']) }}</span>
                                </div>
                                <p style="margin:0;font-weight:800">{{ $site['price'] === null ? '' : '$'.number_format((float) $site['price'], 2) }}</p>
                                <a class="wd-inline" href="{{ route('marketplace.show', $site['key']) }}">View site →</a>
                            </article>
                        @endforeach
                    </div>
                    @if ($profile['popular_in_niche'] !== [])
                        <p class="wd-fine"><span style="font-weight:800;color:#152029">Popular in {{ $profile['primary'] }}:</span>
                            @foreach ($profile['popular_in_niche'] as $popular)
                                <a class="wd-inline" href="{{ route('marketplace.show', $popular['key']) }}">{{ $popular['domain'] }}</a>@if (! $loop->last) · @endif
                            @endforeach
                        </p>
                    @endif
                    @if ($profile['primary_slug'])
                        <p class="wd-fine"><a class="wd-inline" href="{{ route('niches.show', $profile['primary_slug']) }}">See all {{ $profile['primary'] }} websites →</a></p>
                    @endif
                    <div class="wd-guide">
                        <p style="margin:0 0 8px;font-family:Montserrat,sans-serif;font-weight:800">Publisher guidelines</p>
                        <ul>
                            <li>Original content, minimum [WORD COUNT] words</li>
                            <li>Topic must suit the site's categories</li>
                            <li>No adult, gambling or CBD content unless the publisher allows it</li>
                            <li>Up to [N] links per article</li>
                        </ul>
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection
