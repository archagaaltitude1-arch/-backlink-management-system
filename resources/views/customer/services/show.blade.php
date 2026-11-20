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
        $heroPhotos = [
            'guest-post' => '/images/services/hero-guest-post.jpg',
            'contextual-link' => '/images/services/hero-contextual.jpg',
            'text-link' => '/images/services/hero-text-link.jpg',
            'niche-edit' => '/images/services/hero-niche-edit.jpg',
        ];
        $heroPhoto = $heroPhotos[$page['slug']] ?? null;
    @endphp
    <style>
        .sp-wrap { padding-left: clamp(24px, 4vw, 56px); padding-right: clamp(24px, 4vw, 56px); }
        .sp-kicker { margin: 0 0 12px; color: #C0340D; font-size: 12px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; }
        .sp-h1 { margin: 18px 0 0; color: #152029; font-size: clamp(34px, 4.2vw, 48px); line-height: 1.08; font-weight: 800; letter-spacing: -0.02em; }
        .sp-h1 span { color: #FF4917; }
        .sp-h2 { margin: 0; color: #152029; font-size: clamp(28px, 3vw, 40px); line-height: 1.12; font-weight: 800; letter-spacing: -0.02em; }
        .sp-lead { margin: 16px 0 0; max-width: 46ch; color: #4A5560; font-size: 17px; line-height: 1.65; }
        .sp-hero { background: #fff; border-bottom: 1px solid #E3E5E8; }
        .sp-hero-photo { position: relative; display: flex; align-items: center; min-height: calc(100dvh - 4.5rem); overflow: hidden; border-bottom-color: transparent; background-color: #0c1218; background-size: cover; background-position: 72% center; color: #fff; }
        .sp-hero-photo::before { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(12, 18, 24, 0.94) 0%, rgba(12, 18, 24, 0.72) 42%, rgba(12, 18, 24, 0.22) 100%); }
        .sp-hero-photo .sp-hero-grid { position: relative; z-index: 1; width: 100%; }
        .sp-hero-photo .sp-crumb, .sp-hero-photo .sp-crumb a { color: rgba(255, 255, 255, 0.75); }
        .sp-hero-photo .sp-crumb .text-navy, .sp-hero-photo .sp-h1, .sp-hero-photo .sp-tag, .sp-hero-photo .sp-trust li { color: #fff; }
        .sp-hero-photo .sp-h1 span { color: #FF4917; }
        .sp-hero-photo .sp-lead { color: rgba(255, 255, 255, 0.82); }
        .sp-hero-photo .sp-pill { border-color: rgba(255, 255, 255, 0.25); background: rgba(255, 255, 255, 0.1); color: #fff; }
        .sp-hero-photo .sp-pill-hot { border-color: transparent; background: #FFF1EC; color: #C0340D; }
        .sp-hero-photo .sp-btn-line { border-color: #fff; background: transparent; color: #fff; }
        .sp-hero-photo .sp-btn-line:hover { background: #fff; color: #152029; }
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
        .sp-band { background: #F6F7F9; }
        .sp-steps-band { background: #152029; color: #fff; }
        .sp-steps-band .sp-kicker { color: #FF8A55; }
        .sp-steps-band .sp-h2 { color: #fff; }
        .sp-section { padding-top: 56px; padding-bottom: 64px; }
        .sp-split { display: grid; gap: 16px; }
        .why-intro { margin: 0; border-left: 2px solid #FF4917; padding-left: 20px; color: #4A5560; font-size: 16px; line-height: 1.7; }
        .why-intro strong { color: #152029; font-weight: 600; }
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
        .sp-benefit { position: relative; overflow: hidden; padding: 24px; transition: transform 150ms ease, box-shadow 150ms ease, border-color 150ms ease; }
        .sp-benefit::before { content: ""; position: absolute; top: 0; right: 0; left: 0; height: 4px; background: #FF4917; transform: scaleX(0); transform-origin: left; transition: transform 150ms ease; }
        .sp-benefit:hover { border-color: #FFD5C8; box-shadow: 0 16px 36px rgba(21, 32, 41, 0.08); transform: translateY(-2px); }
        .sp-benefit:hover::before { transform: scaleX(1); }
        .sp-num { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 999px; background: #FFF1EC; color: #C0340D; font-size: 14px; font-weight: 800; }
        .sp-benefit .sp-num { width: 44px; height: 44px; flex: none; font-size: 15px; font-weight: 600; }
        .sp-benefit h3, .sp-area h3, .sp-step h3, .sp-plan h3 { margin: 14px 0 0; color: #152029; font-size: 18px; line-height: 1.3; font-weight: 800; }
        .sp-benefit p, .sp-area p, .sp-step p { margin: 8px 0 0; color: #4A5560; font-size: 15px; line-height: 1.6; }
        .sp-icon { display: grid; width: 44px; height: 44px; place-items: center; border-radius: 14px; background: #FFF1EC; color: #D63A10; }
        .sp-points { display: grid; gap: 8px; margin: 18px 0 0; padding: 0; list-style: none; }
        .sp-points li { display: flex; align-items: center; gap: 8px; color: #152029; font-size: 14px; font-weight: 700; }
        .sp-points .is-brand { background: #FFF1EC; color: #D63A10; }
        .sp-points .is-blue { background: #E7F0FA; color: #1D4E89; }
        .sp-points .is-green { background: #E7F6EA; color: #1B7A38; }
        .sp-points .is-amber { background: #FFF4DE; color: #B45309; }
        .sp-area.is-blue { background: #E7F0FA; border-color: #C9DCF2; }
        .sp-area.is-green { background: #E7F6EA; border-color: #C3E6CC; }
        .sp-area.is-rose { background: #FDECEF; border-color: #F6D0D8; }
        .sp-area.is-violet { background: #F1ECFA; border-color: #D9D2F2; }
        .sp-area.is-sky { background: #E6F4FB; border-color: #C5E6F5; }
        .sp-area.is-amber { background: #FFF4DE; border-color: #F3DFB4; }
        .sp-area.is-blue .sp-icon { background: #1D4E89; color: #fff; }
        .sp-area.is-green .sp-icon { background: #1B7A38; color: #fff; }
        .sp-area.is-rose .sp-icon { background: #BE123C; color: #fff; }
        .sp-area.is-violet .sp-icon { background: #6D28D9; color: #fff; }
        .sp-area.is-sky .sp-icon { background: #0369A1; color: #fff; }
        .sp-area.is-amber .sp-icon { background: #B45309; color: #fff; }
        .sp-niches { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
        .sp-niche { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 14px; border: 1px solid #E3E5E8; border-radius: 999px; background: #fff; color: #152029; font-size: 14px; font-weight: 700; text-decoration: none; }
        .sp-niche:hover { border-color: #152029; }
        .sp-niche span { border-radius: 999px; background: rgba(255, 255, 255, 0.8); padding: 2px 8px; color: inherit; font-size: 12px; }
        .sp-niche.is-blue { background: #E7F0FA; border-color: #C9DCF2; color: #1D4E89; }
        .sp-niche.is-violet { background: #F1ECFA; border-color: #D9D2F2; color: #6D28D9; }
        .sp-niche.is-navy { background: #EEF1F4; border-color: #D5DCE3; color: #152029; }
        .sp-niche.is-green { background: #E7F6EA; border-color: #C3E6CC; color: #1B7A38; }
        .sp-niche.is-rose { background: #FDECEF; border-color: #F6D0D8; color: #BE123C; }
        .sp-niche.is-amber { background: #FFF4DE; border-color: #F3DFB4; color: #B45309; }
        .sp-niche.is-sky { background: #E6F4FB; border-color: #C5E6F5; color: #0369A1; }
        .sp-niche.is-teal { background: #E5F6F2; border-color: #C9E8E3; color: #0F766E; }
        .sp-niche.is-slate { background: #F1F3F5; border-color: #D5DCE3; color: #3E4C59; }
        .sp-niche.is-brand { background: #FFF1EC; border-color: #FFD9CC; color: #D63A10; }
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
        .sp-faq-band { position: relative; overflow: hidden; }
        .sp-faq-mark { position: absolute; z-index: 0; color: rgba(255, 73, 23, 0.28); font-family: Manrope, sans-serif; font-weight: 800; line-height: 0.8; pointer-events: none; user-select: none; }
        .sp-faq-mark.is-left { left: -12px; top: 8px; font-size: clamp(180px, 22vw, 340px); }
        .sp-faq-mark.is-right { right: 0; bottom: -12px; font-size: clamp(140px, 16vw, 260px); }
        .sp-faq { position: relative; z-index: 1; text-align: center; }
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
        @media (prefers-reduced-motion: reduce) {
            .sp-benefit, .sp-benefit::before { transition: none; }
            .sp-benefit:hover { transform: none; }
        }
        @media (min-width: 720px) {
            .sp-area-grid, .sp-plans { grid-template-columns: 1fr 1fr; }
            .sp-points { grid-template-columns: 1fr 1fr; }
        }
        @media (min-width: 980px) {
            .sp-hero-grid { padding-top: 48px; padding-bottom: 64px; }
            .sp-split { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: center; gap: 64px; }
            .sp-area-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .sp-benefit-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
            .sp-benefit { grid-column: span 2; }
            .sp-benefit.is-half { grid-column: span 3; flex-direction: row; align-items: flex-start; gap: 20px; }
            .sp-benefit.is-full { grid-column: span 6; flex-direction: row; align-items: flex-start; gap: 20px; }
            .sp-benefit.is-half h3, .sp-benefit.is-full h3 { margin-top: 4px; }
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
            .sp-benefit, .sp-benefit.is-half, .sp-benefit.is-full { grid-column: auto; }
            .sp-benefit.is-alone { grid-column: 1 / -1; flex-direction: row; align-items: flex-start; gap: 20px; }
            .sp-benefit.is-alone h3 { margin-top: 4px; }
        }
    </style>

    <section class="sp-hero {{ $heroPhoto ? 'sp-hero-photo' : '' }}" @if ($heroPhoto) style="background-image: url('{{ $heroPhoto }}')" @endif aria-labelledby="service-heading">
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
                    @php
                        $benefitCount = count($page['why']);
                        $benefitRemainder = $benefitCount % 3;
                        $benefitLastRow = $benefitRemainder !== 0 && $loop->iteration > $benefitCount - $benefitRemainder;
                        $benefitClass = $benefitLastRow ? ($benefitRemainder === 1 ? 'is-full' : 'is-half') : '';
                        if ($benefitCount % 2 === 1 && $loop->last) {
                            $benefitClass .= ' is-alone';
                        }
                    @endphp
                    <article class="sp-benefit {{ $benefitClass }}">
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
            @php
                $pointTones = ['is-brand', 'is-blue', 'is-green', 'is-amber'];
                $areaTones = [
                    'Technology' => 'is-blue',
                    'Finance' => 'is-green',
                    'Health' => 'is-rose',
                    'Business' => 'is-violet',
                    'Travel & Lifestyle' => 'is-sky',
                    'Education' => 'is-amber',
                ];
                $nicheTones = [
                    'Technology' => 'is-blue',
                    'Software & IT' => 'is-violet',
                    'Software and IT' => 'is-violet',
                    'Business' => 'is-navy',
                    'Finance' => 'is-green',
                    'Health' => 'is-rose',
                    'Education' => 'is-amber',
                    'Travel' => 'is-sky',
                    'Fashion & Beauty' => 'is-rose',
                    'Fashion and Beauty' => 'is-rose',
                    'Lifestyle' => 'is-amber',
                    'Real Estate' => 'is-teal',
                    'E-commerce' => 'is-violet',
                    'Automotive' => 'is-slate',
                    'Sports' => 'is-green',
                    'Food' => 'is-brand',
                    'Agriculture & Farming' => 'is-green',
                    'Agriculture and Farming' => 'is-green',
                    'Legal' => 'is-navy',
                ];
            @endphp
            @if (! empty($page['areas_points']))
                <ul class="sp-points">
                    @foreach ($page['areas_points'] as $point)
                        <li>
                            <span class="sp-tick {{ $pointTones[$loop->index % count($pointTones)] }}"><x-ui.icon name="check" class="h-3.5 w-3.5" /></span>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="sp-area-grid">
                @foreach ($page['areas'] as $area)
                    <article class="sp-area {{ $areaTones[$area['title']] ?? 'is-blue' }}">
                        <span class="sp-icon"><x-ui.icon :name="$area['icon']" /></span>
                        <h3>{{ $area['title'] }}</h3>
                        <p>{{ $area['text'] }}</p>
                    </article>
                @endforeach
            </div>
            <div class="sp-niches">
                @foreach ($page['niches'] as $niche)
                    <a class="sp-niche {{ $nicheTones[$niche['name']] ?? '' }}" href="{{ route('niches.show', \Illuminate\Support\Str::slug($niche['name'])) }}">
                        {{ $niche['name'] }}
                        @if ($niche['count'] !== null)
                            <span>{{ $niche['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="sp-steps-band" aria-labelledby="steps-heading">
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

    <section class="sp-band sp-faq-band" aria-labelledby="faq-heading">
        <span class="sp-faq-mark is-left" aria-hidden="true">?</span>
        <span class="sp-faq-mark is-right" aria-hidden="true">?</span>
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
