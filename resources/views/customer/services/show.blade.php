@extends('layouts.public')

@section('document_title', $page['name'].' Service – BacklinkPro')

@section('meta_description', $page['meta'])

@push('head')
    <script type="application/ld+json">{!! json_encode($page['faq_schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
    @php
        $highlight = $page['h1_highlight'];
        $h1Before = e(\Illuminate\Support\Str::before($page['h1'], $highlight));
        $h1After = e(\Illuminate\Support\Str::after($page['h1'], $highlight));
        $contact = route('contact');
        $offerHref = $page['slug'] === 'niche-edit' ? '#publishers' : '#packages';
    @endphp
    <style>
        .sp-wrap { padding-left: clamp(24px, 4vw, 56px); padding-right: clamp(24px, 4vw, 56px); }
        .sp-kicker { margin: 0 0 12px; color: #C0340D; font-size: 12px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; }
        .sp-h1 { margin: 18px 0 0; color: #152029; font-size: clamp(34px, 4.2vw, 48px); line-height: 1.08; font-weight: 800; letter-spacing: -0.02em; }
        .sp-h1 span { color: #FF4917; }
        .sp-h2 { margin: 0; color: #152029; font-size: clamp(28px, 3vw, 40px); line-height: 1.12; font-weight: 800; letter-spacing: -0.02em; }
        .sp-lead { margin: 16px 0 0; max-width: 46ch; color: #4A5560; font-size: 17px; line-height: 1.65; }
        .sp-hero { background: #fff; border-bottom: 1px solid #E3E5E8; }
        .sp-hero-grid { display: grid; gap: 32px; align-items: center; padding-top: 36px; padding-bottom: 48px; }
        .sp-crumb { margin: 0; color: #4A5560; font-size: 14px; font-weight: 700; }
        .sp-crumb a { color: #4A5560; text-decoration: none; }
        .sp-crumb a:hover { color: #C0340D; }
        .sp-crumb a:focus-visible, .sp-btn:focus-visible, .sp-chip:focus-visible, .sp-faq summary:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .sp-pills { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0 0; }
        .sp-pill { display: inline-flex; align-items: center; min-height: 36px; padding: 0 14px; border: 1px solid #E3E5E8; border-radius: 999px; background: #F6F7F9; color: #152029; font-size: 13px; font-weight: 800; }
        .sp-pill-hot { border-color: transparent; background: #FFF1EC; color: #C0340D; }
        .sp-tag { margin: 14px 0 0; color: #152029; font-size: 16px; font-weight: 800; }
        .sp-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
        .sp-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 18px; border-radius: 999px; font-size: 15px; font-weight: 800; text-decoration: none; }
        .sp-btn-fill { background: #D63A10; color: #fff; }
        .sp-btn-fill:hover { background: #FF4917; }
        .sp-btn-line { border: 1px solid #152029; background: #fff; color: #152029; }
        .sp-btn-line:hover { background: #152029; color: #fff; }
        .sp-trust { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 22px 0 0; padding: 0; list-style: none; }
        .sp-trust li { display: flex; align-items: center; gap: 8px; color: #152029; font-size: 14px; font-weight: 700; }
        .sp-tick { display: grid; width: 22px; height: 22px; flex: none; place-items: center; border-radius: 999px; background: #FFF1EC; color: #D63A10; }
        .sp-preview { border: 1px solid #E3E5E8; border-radius: 20px; background: #F6F7F9; padding: 18px; box-shadow: 0 10px 28px rgba(21, 32, 41, 0.05); }
        .sp-preview-label { margin: 0 4px 12px; color: #4A5560; font-size: 12px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; }
        .sp-snippet { margin: 0; border-radius: 16px; background: #fff; padding: 18px; }
        .sp-snippet p { margin: 0; color: #4A5560; font-size: 15px; line-height: 1.65; }
        .sp-snippet strong { color: #C0340D; }
        .sp-chips { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; margin-top: 10px; }
        .sp-chip-static { display: flex; min-height: 40px; align-items: center; justify-content: center; border-radius: 12px; background: #fff; color: #152029; font-size: 13px; font-weight: 800; text-align: center; }
        .sp-band { background: #F6F7F9; }
        .sp-section { padding-top: 56px; padding-bottom: 64px; }
        .sp-split { display: grid; gap: 16px; }
        .why-intro { margin: 0; color: #4A5560; font-size: 16px; line-height: 1.7; }
        .why-intro strong { color: #152029; font-weight: 700; }
        .sp-benefit-grid, .sp-area-grid, .sp-steps, .sp-plans { display: grid; gap: 16px; margin-top: 28px; }
        .sp-benefit, .sp-area, .sp-step, .sp-plan {
            display: flex;
            flex-direction: column;
            border: 1px solid #E3E5E8;
            border-radius: 20px;
            background: #fff;
            padding: 22px;
            box-shadow: 0 10px 28px rgba(21, 32, 41, 0.05);
        }
        .sp-num { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 999px; background: #FFF1EC; color: #C0340D; font-size: 14px; font-weight: 800; }
        .sp-benefit h3, .sp-area h3, .sp-step h3, .sp-plan h3 { margin: 14px 0 0; color: #152029; font-size: 18px; line-height: 1.3; font-weight: 800; }
        .sp-benefit p, .sp-area p, .sp-step p { margin: 8px 0 0; color: #4A5560; font-size: 15px; line-height: 1.6; }
        .sp-icon { display: grid; width: 44px; height: 44px; place-items: center; border-radius: 14px; background: #FFF1EC; color: #D63A10; }
        .sp-points { display: grid; gap: 8px; margin: 18px 0 0; padding: 0; list-style: none; }
        .sp-points li { display: flex; align-items: center; gap: 8px; color: #152029; font-size: 14px; font-weight: 700; }
        .sp-niches { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
        .sp-niche { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 14px; border: 1px solid #E3E5E8; border-radius: 999px; background: #fff; color: #152029; font-size: 14px; font-weight: 700; text-decoration: none; }
        .sp-niche:hover { border-color: #152029; }
        .sp-niche span { border-radius: 999px; background: #F6F7F9; padding: 2px 8px; color: #4A5560; font-size: 12px; }
        .sp-plan.is-popular { border-color: #FF4917; background: #FFF8F6; }
        .sp-popular { margin: 0 0 8px; color: #C0340D; font-size: 12px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; }
        .sp-price { margin: 8px 0 0; color: #152029; font-size: 36px; line-height: 1; font-weight: 800; }
        .sp-summary { margin: 8px 0 0; color: #4A5560; font-size: 14px; }
        .sp-plan ul { margin: 16px 0 0; padding: 0; list-style: none; }
        .sp-plan li { display: flex; gap: 8px; margin-top: 10px; color: #152029; font-size: 14px; line-height: 1.45; }
        .sp-plan-actions { display: grid; gap: 8px; margin-top: auto; padding-top: 20px; }
        .sp-foot { margin: 22px 0 0; color: #4A5560; font-size: 15px; line-height: 1.6; }
        .sp-foot a { color: #152029; font-weight: 800; }
        .sp-foot a:hover { color: #C0340D; }
        .sp-faq { text-align: center; }
        .sp-faq .sp-lead { margin-left: auto; margin-right: auto; }
        .sp-faq-list { margin: 28px auto 0; max-width: 820px; text-align: left; }
        .sp-faq details { border: 1px solid #E3E5E8; border-radius: 16px; background: #fff; padding: 4px 18px; }
        .sp-faq details + details { margin-top: 10px; }
        .sp-faq summary { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 56px; cursor: pointer; list-style: none; color: #152029; font-size: 16px; font-weight: 800; }
        .sp-faq summary::-webkit-details-marker { display: none; }
        .sp-plus { color: #C0340D; font-size: 22px; font-weight: 400; line-height: 1; }
        .sp-faq details[open] .sp-plus { transform: rotate(45deg); }
        .sp-faq details p { margin: 0 28px 16px 0; color: #4A5560; font-size: 15px; line-height: 1.65; }
        .sp-faq-more { margin: 28px auto 0; max-width: 820px; }
        .sp-faq-more h3 { margin: 0; color: #152029; font-size: 24px; font-weight: 800; }
        .sp-faq-more p { margin: 8px auto 0; max-width: 46ch; color: #4A5560; }
        .sp-faq-more .sp-actions { justify-content: center; }
        .sp-cta { background: #152029; color: #fff; border-top: 3px solid #FF4917; }
        .sp-cta-inner { padding-top: 56px; padding-bottom: 64px; }
        .sp-cta .sp-kicker { color: #FF8A55; }
        .sp-cta h2 { margin: 0; max-width: 22ch; color: #fff; font-size: clamp(30px, 4vw, 44px); line-height: 1.1; font-weight: 800; }
        .sp-cta p { max-width: 46ch; margin: 12px 0 0; color: #D5DCE3; font-size: 16px; line-height: 1.65; }
        .sp-cta .sp-btn-line { border-color: #fff; background: transparent; color: #fff; }
        .sp-cta .sp-btn-line:hover { background: #fff; color: #152029; }
        @media (min-width: 720px) {
            .sp-area-grid, .sp-plans { grid-template-columns: 1fr 1fr; }
            .sp-points { grid-template-columns: 1fr 1fr; }
        }
        @media (min-width: 980px) {
            .sp-hero-grid { grid-template-columns: minmax(0, 1fr) minmax(300px, 420px); padding-top: 48px; padding-bottom: 64px; }
            .sp-split { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); align-items: end; gap: 40px; }
            .sp-benefit-grid, .sp-area-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .sp-steps { grid-template-columns: repeat(var(--steps, 5), minmax(0, 1fr)); }
            .sp-plans { grid-template-columns: repeat(var(--plans, 4), minmax(0, 1fr)); }
            .sp-section { padding-top: 72px; padding-bottom: 80px; }
        }
        @media (max-width: 979px) {
            .sp-steps { grid-template-columns: 1fr; }
        }
        @media (min-width: 700px) and (max-width: 979px) {
            .sp-steps { grid-template-columns: 1fr 1fr; }
            .sp-benefit-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>

    <section class="sp-hero" aria-labelledby="service-heading">
        <div class="sp-wrap sp-hero-grid">
            <div>
                <nav class="sp-crumb" aria-label="Breadcrumb">
                    <a href="{{ url('/') }}">Home</a>
                    <span aria-hidden="true"> / </span>
                    <a href="{{ url('/services') }}">Services</a>
                    <span aria-hidden="true"> / </span>
                    <span class="text-navy">{{ $page['name'] }}</span>
                </nav>
                <p class="sp-pills">
                    <span class="sp-pill sp-pill-hot">{{ $page['pill'] }}</span>
                    <span class="sp-pill">{{ $page['turnaround'] }}</span>
                </p>
                <h1 id="service-heading" class="sp-h1 font-display">{!! $h1Before !!}<span>{{ $highlight }}</span>{!! $h1After !!}</h1>
                <p class="sp-lead">{{ $page['intro'] }}</p>
                <p class="sp-tag">{{ $page['tagline'] }}</p>
                <div class="sp-actions">
                    <a class="sp-btn sp-btn-fill" href="{{ url('/marketplace?service='.$page['slug']) }}">{{ $page['buttons']['primary'] }}</a>
                    <a class="sp-btn sp-btn-line" href="{{ $offerHref }}">{{ $page['buttons']['secondary'] }}</a>
                </div>
                <ul class="sp-trust">
                    @foreach ($page['trust'] as $trust)
                        <li>
                            <span class="sp-tick"><x-ui.icon name="check" class="h-3.5 w-3.5" /></span>
                            <span>{{ $trust }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <aside class="sp-preview" aria-hidden="true">
                <p class="sp-preview-label">Placement preview</p>
                <div class="sp-snippet">
                    <p>Teams that want lasting authority publish on real websites. A useful guide on this topic is <strong>your brand</strong>, written for readers who already care about the subject.</p>
                </div>
                <div class="sp-chips">
                    @foreach (['DR 60+', 'Dofollow', 'Verified'] as $chip)
                        <p class="sp-chip-static">{{ $chip }}</p>
                        @endforeach
                </div>
            </aside>
        </div>
    </section>

    <section class="sp-band" aria-labelledby="why-service-heading">
        <div class="sp-wrap sp-section">
            <div class="sp-split">
                <div>
                    <p class="sp-kicker">Benefits</p>
                    <h2 id="why-service-heading" class="sp-h2 font-display">{{ $page['why_title'] }}</h2>
                </div>
                @if (! empty($page['why_intro']))
                    <p class="why-intro">{!! $page['why_intro'] !!}</p>
                @endif
            </div>
            <div class="sp-benefit-grid">
                        @foreach ($page['why'] as $index => $reason)
                    <article class="sp-benefit">
                        <span class="sp-num">{{ $index + 1 }}</span>
                                <h3>{{ $reason[0] }}</h3>
                                <p>{{ $reason[1] }}</p>
                            </article>
                        @endforeach
            </div>
        </div>
    </section>

    <section aria-labelledby="areas-heading">
        <div class="sp-wrap sp-section">
            <p class="sp-kicker">Our key areas</p>
            <h2 id="areas-heading" class="sp-h2 font-display">{{ $page['areas_title'] }}</h2>
            <p class="sp-lead">{{ $page['areas_intro'] }}</p>
            @if (! empty($page['areas_points']))
                <ul class="sp-points">
                    @foreach ($page['areas_points'] as $point)
                        <li>
                            <span class="sp-tick"><x-ui.icon name="check" class="h-3.5 w-3.5" /></span>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="sp-area-grid">
                @foreach ($page['areas'] as $area)
                    <article class="sp-area">
                        <span class="sp-icon"><x-ui.icon :name="$area['icon']" /></span>
                        <h3>{{ $area['title'] }}</h3>
                        <p>{{ $area['text'] }}</p>
                    </article>
                @endforeach
            </div>
            <div class="sp-niches">
                @foreach ($page['niches'] as $niche)
                    <a class="sp-niche" href="{{ route('niches.show', \Illuminate\Support\Str::slug($niche['name'])) }}">
                        {{ $niche['name'] }}
                        @if ($niche['count'] !== null)
                            <span>{{ $niche['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="sp-band" aria-labelledby="steps-heading">
        <div class="sp-wrap sp-section">
            <p class="sp-kicker">How it works</p>
            <h2 id="steps-heading" class="sp-h2 font-display">{{ $page['steps_title'] }}</h2>
            <ol class="sp-steps" style="--steps: {{ count($page['steps']) }}">
                @foreach ($page['steps'] as $step)
                    <li class="sp-step">
                        <span class="sp-num">{{ $loop->iteration }}</span>
                        <h3 class="font-display">{{ $step[0] }}</h3>
                        <p>{{ $step[1] }}</p>
                    </li>
                @endforeach
            </ol>
            <div class="sp-actions">
                <a class="sp-btn sp-btn-fill" href="{{ $page['start_href'] }}">{{ $page['buttons']['start'] }}</a>
            </div>
        </div>
    </section>

    @if ($page['slug'] === 'niche-edit')
        @include('customer.publishers.listing', [
            'rows' => $publishers['rows'] ?? [],
            'demo' => $publishers['demo'] ?? false,
            'categories' => $publishers['categories'] ?? [],
            'skipIntro' => true,
            'showRecent' => false,
            'sectionId' => 'publishers',
            'embeddedTitle' => 'Buy Niche Edits Backlinks',
            'embeddedLead' => 'Websites that allow link insertions. Choose a relevant publisher and we place your link in an existing article on that site.',
            'insertionPrice' => true,
        ])
    @else
    <section id="packages" class="scroll-mt-24" aria-labelledby="packages-heading">
        <div class="sp-wrap sp-section">
            <p class="sp-kicker">Packages</p>
            <h2 id="packages-heading" class="sp-h2 font-display">{{ $page['packages_title'] }}</h2>
            <p class="sp-lead">{{ $page['packages_intro'] ?? 'Choose a package that fits your requirements.' }}</p>
            @if (! empty($page['package_tabs']))
                @include('customer.packages.tier-tabs', ['tabs' => $page['package_tabs'], 'service' => $page['slug']])
            @else
            <div class="sp-plans" style="--plans: {{ count($page['tiers']) }}">
                    @foreach ($page['tiers'] as $tier)
                    <article id="tier-{{ \Illuminate\Support\Str::slug($tier['name']) }}" @class(['sp-plan scroll-mt-24', 'is-popular' => $tier['popular']])>
                            @if ($tier['popular'])
                            <p class="sp-popular">Popular</p>
                            @endif
                        <span class="sp-icon" aria-hidden="true"><x-ui.icon :name="$tier['icon'] ?? 'layers'" /></span>
                        <h3>{{ $tier['name'] }}</h3>
                        <p class="sp-price font-display">{{ $tier['price'] }}</p>
                        <p class="sp-summary">{{ $tier['summary'] ?? (($tier['links'] === 'Custom') ? 'Custom links' : $tier['links'].' links') }}</p>
                            <ul>
                                @foreach ($tier['features'] as $feature)
                                    <li>
                                    <span class="sp-tick"><x-ui.icon name="check" class="h-3.5 w-3.5" /></span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        <div class="sp-plan-actions">
                            @php($offerSlug = \App\Support\PublicPackages::slugForTier($page['slug'], $tier['name']))
                            <a class="sp-btn sp-btn-fill" href="{{ $offerSlug ? route('packages.show', $offerSlug) : url('/publishers?service='.$page['slug'].'&tier='.urlencode($tier['name'])) }}">Buy now</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
            <p class="sp-foot"><a href="{{ route('publishers.index') }}">Check out the publisher sites and individual pricing</a></p>
            <p class="sp-foot">Need a custom or bulk order? <a href="{{ $contact }}">Contact us</a></p>
        </div>
    </section>
    @endif

    <section class="sp-band" aria-labelledby="faq-heading">
        <div class="sp-wrap sp-section sp-faq">
            <p class="sp-kicker">FAQs</p>
            <h2 id="faq-heading" class="sp-h2 font-display">Questions about {{ $page['name'] }}</h2>
            <p class="sp-lead">What is included, how long it takes, and how we confirm the link is live.</p>
            <div class="sp-faq-list">
                @foreach ($page['faqs'] as $faq)
                    <details>
                        <summary>
                            {{ $faq[0] }}
                            <span class="sp-plus" aria-hidden="true">+</span>
                        </summary>
                        <p>{{ $faq[1] }}</p>
                    </details>
                @endforeach
            </div>
            <div class="sp-faq-more">
                <h3 class="font-display">Still have a question?</h3>
                <p>Tell us the niche, website, or package you want and we will help you choose.</p>
                <div class="sp-actions">
                    <a class="sp-btn sp-btn-line" href="{{ $contact }}">Contact</a>
                </div>
            </div>
        </div>
    </section>

    <section class="sp-cta" aria-labelledby="cta-heading">
        <div class="sp-wrap sp-cta-inner">
            <p class="sp-kicker">{{ $page['name'] }}</p>
            <h2 id="cta-heading" class="font-display">{{ $page['cta_title'] }}</h2>
            <p>{{ $page['cta_text'] }}</p>
            <div class="sp-actions">
                <a class="sp-btn sp-btn-fill" href="{{ $page['start_href'] }}">{{ $page['buttons']['start'] }}</a>
                <a class="sp-btn sp-btn-line" href="{{ $offerHref }}">{{ $page['buttons']['secondary'] }}</a>
                <a class="sp-btn sp-btn-line" href="{{ $contact }}">{{ $page['buttons']['contact'] }}</a>
            </div>
        </div>
    </section>
@endsection
