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
    @endphp
    <style>
        .svc-h1 { margin: 0; font-size: 34px; line-height: 1.1; font-weight: 800; }
        .svc-h2 { margin: 0; font-size: 30px; line-height: 1.15; font-weight: 800; }
        .svc-section { padding: 56px 0; }
        @media (min-width: 1024px) {
            .svc-h1 { font-size: 52px; }
            .svc-h2 { font-size: 40px; }
            .svc-section { padding: 88px 0; }
        }
        .why-intro strong { font-weight: 700; color: #152029; }
        .benefit-head { display: grid; gap: 20px 56px; align-items: start; }
        .benefit-kicker { display: flex; align-items: center; gap: 10px; margin: 0; color: #D63A10; font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .benefit-kicker::before { content: ""; width: 10px; height: 10px; background: #FF4917; }
        .benefit-slider { margin-top: 48px; }
        .benefit-viewport { overflow: hidden; }
        .benefit-track { display: flex; transition: transform 0.45s ease; }
        .benefit-card { position: relative; flex: 0 0 100%; box-sizing: border-box; background: #fff; padding: 36px 28px 44px; border-right: 14px solid #F4F4F4; box-shadow: inset 0 3px 0 #FF4917; transition: background 0.2s ease; }
        .benefit-card:hover { background: #FFF8F6; }
        .benefit-num { display: grid; width: 42px; height: 42px; place-items: center; border: 1.5px solid #FF4917; border-radius: 50%; background: #FFF1EC; color: #D63A10; font-size: 16px; font-weight: 700; line-height: 1; transition: background 0.2s ease, color 0.2s ease; }
        .benefit-card:hover .benefit-num { background: #FF4917; color: #fff; }
        .benefit-card h3 { margin: 22px 0 0; font-size: 20px; line-height: 1.35; font-weight: 700; color: #152029; }
        .benefit-card p { margin: 12px 0 0; font-size: 15px; line-height: 1.7; color: #4A5560; }
        .benefit-arrows { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
        .benefit-arrow { display: grid; width: 46px; height: 46px; place-items: center; border: 1px solid #E3E5E8; border-radius: 50%; background: #fff; color: #152029; cursor: pointer; box-shadow: 0 6px 16px rgba(21, 32, 41, 0.08); }
        .benefit-arrow[data-benefit-next]:not(:disabled) { background: #FF4917; border-color: #FF4917; color: #fff; }
        .benefit-arrow:hover:not(:disabled) { background: #152029; border-color: #152029; color: #fff; }
        .benefit-arrow:disabled { opacity: 0.35; cursor: default; box-shadow: none; }
        .benefit-arrow:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .area-grid { display: grid; grid-template-columns: 1fr; gap: 16px; margin-top: 32px; }
        .area-card { display: flex; height: 100%; flex-direction: column; border-radius: 20px; border: 1px solid #E3E5E8; background: #fff; padding: 24px; box-shadow: 0 10px 24px rgba(21, 32, 41, 0.05); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .area-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(255, 73, 23, 0.12); }
        .area-icon { display: grid; width: 52px; height: 52px; place-items: center; border-radius: 16px; }
        .area-card h3 { margin: 16px 0 0; font-size: 22px; line-height: 1.3; font-weight: 800; color: #152029; }
        .area-card p { margin: 8px 0 0; font-size: 16px; line-height: 1.7; color: #4A5560; }
        @media (min-width: 720px) { .area-grid { grid-template-columns: 1fr 1fr; } }
        @media (min-width: 1024px) { .area-grid { grid-template-columns: 1fr 1fr 1fr; } }
        .steps-section { background: #F3F5F8; }
        .steps-head { max-width: 760px; margin: 0 auto; text-align: center; }
        .steps-head .benefit-kicker { justify-content: center; }
        .steps-grid { display: grid; grid-template-columns: 1fr; gap: 16px; margin: 40px 0 0; padding: 0; list-style: none; }
        .step-card { height: 100%; border-radius: 28px; background: #fff; padding: 32px 24px 36px; box-shadow: 0 16px 40px rgba(21, 32, 41, 0.07); }
        .step-card h3 { margin: 0; font-size: 18px; line-height: 1.35; font-weight: 800; color: #152029; }
        .step-card p { margin: 12px 0 0; font-size: 14px; line-height: 1.7; color: #6B7280; }
        .steps-rail { display: none; position: relative; margin-top: 28px; height: 18px; align-items: center; }
        .steps-line { position: absolute; top: 50%; right: 8%; left: 8%; height: 2px; background: #E4E7EB; transform: translateY(-50%); }
        .steps-dot { position: relative; justify-self: center; width: 12px; height: 12px; border-radius: 50%; background: #D5DBE1; }
        .steps-dot.is-on { width: 14px; height: 14px; background: #FF4917; box-shadow: 0 0 0 5px rgba(255, 73, 23, 0.16); }
        .steps-start { display: flex; justify-content: center; margin-top: 36px; }
        @media (min-width: 900px) {
            .steps-grid { grid-template-columns: repeat(var(--steps, 5), minmax(0, 1fr)); gap: 22px; }
            .steps-rail { display: grid; grid-template-columns: repeat(var(--steps, 5), minmax(0, 1fr)); }
        }
        @media (min-width: 900px) {
            .benefit-head { grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr); }
            .benefit-card { flex-basis: 33.3333%; }
        }
        .price-section { background: #152029; }
        .price-sheet { border-radius: 28px; background: #fff; padding: 48px 20px 32px; }
        .price-head { max-width: 640px; margin: 0 auto; text-align: center; }
        .price-head p { margin: 12px 0 0; color: #4A5560; font-size: 16px; line-height: 1.6; }
        .price-grid { display: grid; grid-template-columns: 1fr; gap: 16px; margin-top: 36px; align-items: stretch; }
        .price-card { position: relative; display: flex; height: 100%; flex-direction: column; border: 1px solid #E7E9EC; border-top: 3px solid #FF4917; border-radius: 22px; background: #fff; padding: 28px 22px 22px; box-shadow: 0 14px 32px rgba(21, 32, 41, 0.08); transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
        .price-card:hover { transform: translateY(-4px); border-color: #FFB599; box-shadow: 0 18px 36px rgba(255, 73, 23, 0.16); }
        .price-card.is-popular { border: 2px solid #FF4917; background: #FFF6F2; box-shadow: 0 18px 40px rgba(255, 73, 23, 0.18); }
        .price-card.is-popular:hover { border-color: #D63A10; box-shadow: 0 22px 44px rgba(255, 73, 23, 0.24); }
        .price-badge { position: absolute; top: 18px; right: 18px; margin: 0; color: #D63A10; font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
        .price-icon { display: grid; width: 40px; height: 40px; place-items: center; border-radius: 12px; background: #FFF1EC; color: #D63A10; }
        .price-name { margin: 16px 0 0; color: #D63A10; font-size: 15px; font-weight: 700; }
        .price-amount { margin: 8px 0 0; color: #152029; font-size: 40px; line-height: 1; font-weight: 800; }
        .price-summary { margin: 10px 0 0; color: #4A5560; font-size: 14px; line-height: 1.5; }
        .price-card ul { margin: 22px 0 0; padding: 0; list-style: none; }
        .price-card li { display: flex; align-items: flex-start; gap: 10px; margin-top: 12px; color: #152029; font-size: 14px; line-height: 1.45; }
        .price-check { display: grid; width: 20px; height: 20px; flex: 0 0 20px; place-items: center; margin-top: 1px; border-radius: 50%; background: #FFF1EC; color: #D63A10; }
        .price-check svg { width: 12px; height: 12px; }
        .price-actions { display: flex; flex-direction: column; gap: 8px; margin-top: auto; padding-top: 28px; }
        .price-btn { display: flex; align-items: center; justify-content: center; min-height: 44px; border: 1.5px solid #D63A10; border-radius: 12px; background: #D63A10; color: #fff; font-size: 15px; font-weight: 700; text-decoration: none; }
        .price-btn:hover { border-color: #FF4917; background: #FF4917; color: #fff; }
        .price-btn-line { border-color: #152029; background: #fff; color: #152029; }
        .price-btn-line:hover { border-color: #152029; background: #F2F2F2; color: #152029; }
        .price-btn:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .price-foot { margin: 28px 0 0; text-align: center; color: #4A5560; font-size: 15px; line-height: 1.7; }
        .price-foot a { color: #152029; font-weight: 700; text-underline-offset: 3px; }
        .price-foot a:hover { color: #C0340D; }
        @media (min-width: 800px) {
            .price-sheet { padding: 56px 36px 40px; }
            .price-grid { grid-template-columns: repeat(var(--plans, 3), minmax(0, 1fr)); gap: 18px; }
        }
}
        .faq-section { position: relative; overflow: hidden; background: #F6F5F3; }
        .faq-curve { position: absolute; width: 220px; height: 460px; color: #F0B39A; pointer-events: none; }
        .faq-curve-left { top: 24px; left: -40px; }
        .faq-curve-right { right: -50px; bottom: 10px; }
        .faq-wrap { position: relative; z-index: 1; max-width: 760px; margin: 0 auto; padding: 72px 24px 80px; text-align: center; }
        .faq-title { margin: 0; color: #152029; font-size: 40px; line-height: 1.1; font-weight: 800; }
        .faq-intro { max-width: 520px; margin: 14px auto 0; color: #4A5560; font-size: 16px; line-height: 1.65; }
        .faq-list { display: grid; gap: 14px; margin: 36px 0 0; padding: 0; text-align: left; list-style: none; }
        .faq-item { border-radius: 18px; background: #fff; padding: 4px 22px 4px 26px; box-shadow: 0 10px 28px rgba(21, 32, 41, 0.06); }
        .faq-item[open] { box-shadow: 0 14px 32px rgba(255, 73, 23, 0.12); }
        .faq-item summary { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 64px; cursor: pointer; list-style: none; color: #152029; font-size: 16px; font-weight: 700; }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary:focus-visible { outline: 2px solid #D63A10; outline-offset: 4px; }
        .faq-plus { flex: none; color: #152029; font-size: 24px; font-weight: 400; line-height: 1; transition: transform 0.2s ease, color 0.2s ease; }
        .faq-item[open] .faq-plus { color: #D63A10; transform: rotate(45deg); }
        .faq-item p { margin: 0; padding: 0 36px 18px 0; color: #4A5560; font-size: 15px; line-height: 1.7; }
        .faq-more { margin-top: 56px; }
        .faq-more h3 { margin: 0; color: #152029; font-size: 28px; line-height: 1.2; font-weight: 800; }
        .faq-more p { max-width: 420px; margin: 10px auto 0; color: #4A5560; font-size: 15px; line-height: 1.6; }
        .faq-contact { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; margin-top: 18px; padding: 10px 26px; border: 1.5px solid #152029; border-radius: 999px; background: #fff; color: #152029; font-size: 15px; font-weight: 700; text-decoration: none; }
        .faq-contact:hover { border-color: #D63A10; background: #D63A10; color: #fff; }
        .faq-contact:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .svc-cta { position: relative; overflow: hidden; background: #152029; color: #fff; }
        .svc-cta-orb { position: absolute; top: 50%; right: -140px; width: 460px; height: 460px; border-radius: 50%; background: radial-gradient(circle at 40% 40%, #FF8A55 0%, #FF4917 46%, rgba(255, 73, 23, 0) 72%); transform: translateY(-50%); }
        .svc-cta-inner { position: relative; z-index: 1; max-width: 760px; margin: 0 auto; padding: 72px 24px 80px; text-align: center; }
        .svc-cta-kicker { margin: 0; color: #FF4917; font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .svc-cta h2 { margin: 12px 0 0; color: #fff; font-size: 34px; line-height: 1.15; font-weight: 800; }
        .svc-cta-text { max-width: 540px; margin: 14px auto 0; color: #D5DCE3; font-size: 17px; line-height: 1.65; }
        .svc-cta-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 28px; }
        .svc-cta-primary, .svc-cta-secondary { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; padding: 12px 24px; border-radius: 999px; font-size: 15px; font-weight: 700; text-decoration: none; }
        .svc-cta-primary { background: #D63A10; color: #fff; }
        .svc-cta-primary:hover { background: #FF4917; }
        .svc-cta-secondary { border: 1.5px solid #fff; background: transparent; color: #fff; }
        .svc-cta-secondary:hover { background: #fff; color: #152029; }
        .svc-cta-primary:focus-visible, .svc-cta-secondary:focus-visible { outline: 2px solid #fff; outline-offset: 3px; }
        @media (min-width: 900px) { .svc-cta h2 { font-size: 44px; } }
    </style>

    <section class="bg-[#F2F2F2]" aria-labelledby="service-heading">
        <div class="mx-auto flex max-w-[1240px] flex-col items-center gap-10 px-6 svc-section md:flex-row md:items-center md:justify-between">
            <div class="min-w-0 w-full md:max-w-[640px] md:flex-1">
                <nav aria-label="Breadcrumb" class="text-sm font-bold text-[#4A5560]">
                    <a href="{{ url('/') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Home</a>
                    <span aria-hidden="true"> / </span>
                    <a href="{{ url('/services') }}" class="hover:text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Services</a>
                    <span aria-hidden="true"> / </span>
                    <span class="text-navy">{{ $page['name'] }}</span>
                </nav>
                <p class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full border border-[#E3E5E8] bg-white px-4 py-2 text-sm font-bold text-[#C0340D]">{{ $page['pill'] }}</span>
                    <span class="inline-flex rounded-full bg-white px-4 py-2 text-sm font-bold text-navy">{{ $page['turnaround'] }}</span>
                </p>
                <h1 id="service-heading" class="svc-h1 mt-5 font-display text-navy">{!! $h1Before !!}<span class="text-[#FF4917]">{{ $highlight }}</span>{!! $h1After !!}</h1>
                <p class="mt-5 max-w-xl text-[17px] leading-8 text-[#4A5560] sm:text-[19px]">{{ $page['intro'] }}</p>
                <p class="mt-4 text-[17px] font-bold text-navy">{{ $page['tagline'] }}</p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ url('/marketplace?service='.$page['slug']) }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#D63A10] px-[22px] py-[14px] text-base font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $page['buttons']['primary'] }}</a>
                    <a href="#packages" class="inline-flex min-h-11 items-center justify-center rounded-xl border-[1.5px] border-navy bg-white px-[22px] py-[14px] text-base font-bold text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $page['buttons']['secondary'] }}</a>
                </div>
                <ul class="mt-6 grid gap-x-4 gap-y-2 sm:grid-cols-2">
                    @foreach ($page['trust'] as $trust)
                        <li class="flex min-h-11 items-center gap-2 text-[15px] font-bold text-navy">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[#FFF1EC] text-[#D63A10]">
                                <x-ui.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span>{{ $trust }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="mx-auto w-full max-w-[480px] shrink-0 md:mx-0 md:w-[46%]" aria-hidden="true">
                <div class="rounded-[28px] bg-navy p-4 sm:p-6">
                    <p class="mb-4 px-1 text-[12px] font-bold tracking-[0.08em] text-[#B8C2CC] uppercase">Placement preview</p>
                    <article class="rounded-[20px] bg-white p-5 text-navy">
                        <p class="text-xs font-bold tracking-wide text-[#4A5560] uppercase">Article snippet</p>
                        <p class="mt-3 text-[15px] leading-7 text-[#4A5560]">Teams that want lasting authority publish on real websites. A useful guide on this topic is <span class="font-bold text-[#FF4917] underline decoration-[#FF4917] underline-offset-2">your brand</span>, written for readers who already care about the subject.</p>
                    </article>
                    <div class="mt-3 grid grid-cols-3 gap-3">
                        @foreach (['DR 60+', 'Dofollow', 'Verified ✓'] as $chip)
                            <p class="flex min-h-11 items-center justify-center rounded-[16px] bg-white px-2 text-center text-sm font-bold text-navy">{{ $chip }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#F4F4F4]" aria-labelledby="why-service-heading">
        <div class="mx-auto max-w-[1240px] px-6 svc-section">
            <div class="benefit-head">
                <div>
                    <p class="benefit-kicker">Benefits</p>
                    <h2 id="why-service-heading" class="svc-h2 mt-4 max-w-md font-display text-navy">{{ $page['why_title'] }}</h2>
                </div>
                @if (! empty($page['why_intro']))
                    <p class="why-intro text-base leading-7 text-[#4A5560] lg:pt-8">{!! $page['why_intro'] !!}</p>
                @endif
            </div>
            <div class="benefit-slider" data-benefit-slider>
                <div class="benefit-viewport">
                    <div class="benefit-track" data-benefit-track>
                        @foreach ($page['why'] as $index => $reason)
                            <article class="benefit-card">
                                <span class="benefit-num">{{ $index + 1 }}</span>
                                <h3>{{ $reason[0] }}</h3>
                                <p>{{ $reason[1] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
                <div class="benefit-arrows">
                    <button type="button" class="benefit-arrow" data-benefit-prev aria-label="Previous benefits">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m15 6-6 6 6 6" /></svg>
                    </button>
                    <button type="button" class="benefit-arrow" data-benefit-next aria-label="Next benefits">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m9 6 6 6-6 6" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-[#F2F2F2]" aria-labelledby="areas-heading">
        <div class="mx-auto max-w-[1240px] px-6 svc-section">
            <p class="text-sm font-bold tracking-[0.08em] text-[#C0340D] uppercase">Our key areas</p>
            <h2 id="areas-heading" class="svc-h2 mt-3 font-display text-navy">{{ $page['areas_title'] }}</h2>
            <p class="mt-4 max-w-2xl text-base leading-7 text-[#4A5560]">{{ $page['areas_intro'] }}</p>
            @php
                $areaTones = [
                    'layers' => ['#EAF2FF', '#2563EB'],
                    'chart' => ['#E7F8F2', '#0F766E'],
                    'shield' => ['#E8F1FF', '#3B82F6'],
                    'users' => ['#F3E8FF', '#7C3AED'],
                    'globe' => ['#FFF1E8', '#EA580C'],
                    'pen' => ['#FDE8F4', '#DB2777'],
                ];
            @endphp
            <div class="area-grid">
                @foreach ($page['areas'] as $area)
                    @php $tone = $areaTones[$area['icon']] ?? ['#FFF1EC', '#D63A10']; @endphp
                    <article class="area-card">
                        <span class="area-icon" style="background-color: {{ $tone[0] }}; color: {{ $tone[1] }};">
                            <x-ui.icon :name="$area['icon']" />
                        </span>
                        <h3>{{ $area['title'] }}</h3>
                        <p>{{ $area['text'] }}</p>
                    </article>
                @endforeach
            </div>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($page['niches'] as $niche)
                    <a href="{{ route('niches.show', \Illuminate\Support\Str::slug($niche['name'])) }}" class="inline-flex min-h-11 items-center gap-2 rounded-full border border-[#E3E5E8] bg-white px-4 text-[14px] font-bold text-navy hover:border-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                        {{ $niche['name'] }}
                        @if ($niche['count'] !== null)
                            <span class="rounded-full bg-[#F2F2F2] px-2 py-0.5 text-xs font-bold text-[#4A5560]">{{ $niche['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="steps-section" aria-labelledby="steps-heading">
        <div class="mx-auto max-w-[1240px] px-6 svc-section" style="--steps: {{ count($page['steps']) }}">
            <div class="steps-head">
                <p class="benefit-kicker">How it works</p>
                <h2 id="steps-heading" class="svc-h2 mt-3 font-display text-navy">{{ $page['steps_title'] }}</h2>
            </div>
            <ol class="steps-grid">
                @foreach ($page['steps'] as $step)
                    <li class="step-card">
                        <h3 class="font-display">{{ $step[0] }}</h3>
                        <p>{{ $step[1] }}</p>
                    </li>
                @endforeach
            </ol>
            <div class="steps-rail" aria-hidden="true">
                <span class="steps-line"></span>
                @foreach ($page['steps'] as $index => $step)
                    <span @class(['steps-dot', 'is-on' => $index === 0])></span>
                @endforeach
            </div>
            <div class="steps-start">
                <a href="{{ $page['start_href'] }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#D63A10] px-[22px] py-[14px] text-base font-bold text-white hover:bg-[#FF4917] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $page['buttons']['start'] }}</a>
            </div>
        </div>
    </section>

    <section id="packages" class="price-section scroll-mt-24" aria-labelledby="packages-heading">
        <div class="mx-auto max-w-[1100px] px-6 svc-section">
            <div class="price-sheet" style="--plans: {{ count($page['tiers']) }}">
                <div class="price-head">
                    <h2 id="packages-heading" class="svc-h2 font-display text-navy">{{ $page['packages_title'] }}</h2>
                    <p>{{ $page['packages_intro'] ?? 'Choose a package that fits your requirements.' }}</p>
                </div>
                <div class="price-grid">
                    @foreach ($page['tiers'] as $tier)
                        <article @class(['price-card', 'is-popular' => $tier['popular']])>
                            @if ($tier['popular'])
                                <p class="price-badge">Popular</p>
                            @endif
                            <span class="price-icon" aria-hidden="true">
                                <x-ui.icon :name="$tier['icon'] ?? 'layers'" />
                            </span>
                            <h3 class="price-name">{{ $tier['name'] }}</h3>
                            <p class="price-amount font-display">{{ $tier['price'] }}</p>
                            <p class="price-summary">{{ $tier['summary'] ?? (($tier['links'] === 'Custom') ? 'Custom links' : $tier['links'].' links') }}</p>
                            <ul>
                                @foreach ($tier['features'] as $feature)
                                    <li>
                                        <span class="price-check" aria-hidden="true"><x-ui.icon name="check" /></span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="price-actions">
                                <a class="price-btn" href="{{ route('publishers.index') }}">Browse publishers</a>
                                <a class="price-btn price-btn-line" href="{{ route('packages.index') }}">All packages</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <p class="price-foot">
                    <a href="{{ route('publishers.index') }}">Check out the publisher sites and individual pricing</a>
                </p>
                <p class="price-foot">Need a custom or bulk order? <a href="{{ $contact }}">Contact us</a></p>
            </div>
        </div>
    </section>

<section class="faq-section" aria-labelledby="faq-heading">
        <svg class="faq-curve faq-curve-left" viewBox="0 0 180 420" fill="none" aria-hidden="true">
            <path d="M150 10C40 80 20 180 70 250C120 320 30 370 20 410" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <svg class="faq-curve faq-curve-right" viewBox="0 0 180 420" fill="none" aria-hidden="true">
            <path d="M30 20C140 90 160 190 110 270C60 350 150 390 160 420" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <div class="faq-wrap">
            <h2 id="faq-heading" class="faq-title font-display">FAQs</h2>
            <p class="faq-intro">Quick answers about {{ $page['name'] }}: what is included, how long it takes, and how we confirm the link is live.</p>
            <div class="faq-list">
                @foreach ($page['faqs'] as $faq)
                    <details class="faq-item">
                        <summary>
                            {{ $faq[0] }}
                            <span class="faq-plus" aria-hidden="true">+</span>
                        </summary>
                        <p>{{ $faq[1] }}</p>
                    </details>
                @endforeach
            </div>
            <div class="faq-more">
                <h3 class="font-display">Still have a question?</h3>
                <p>Tell us the niche, website, or package you want and we will help you choose.</p>
                <a class="faq-contact" href="{{ $contact }}">Contact</a>
            </div>
        </div>
    </section>

    <section class="svc-cta" aria-labelledby="cta-heading">
        <div class="svc-cta-orb" aria-hidden="true"></div>
        <div class="svc-cta-inner">
            <p class="svc-cta-kicker">{{ $page['name'] }}</p>
            <h2 id="cta-heading" class="font-display">{{ $page['cta_title'] }}</h2>
            <p class="svc-cta-text">{{ $page['cta_text'] }}</p>
            <div class="svc-cta-actions">
                <a class="svc-cta-primary" href="{{ $page['start_href'] }}">{{ $page['buttons']['start'] }}</a>
                <a class="svc-cta-secondary" href="#packages">{{ $page['buttons']['secondary'] }}</a>
                <a class="svc-cta-secondary" href="{{ $contact }}">{{ $page['buttons']['contact'] }}</a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-benefit-slider]').forEach(function (root) {
                var track = root.querySelector('[data-benefit-track]');
                var cards = track.children;
                var prev = root.querySelector('[data-benefit-prev]');
                var next = root.querySelector('[data-benefit-next]');
                var index = 0;

                function perView() {
                    return window.matchMedia('(min-width: 900px)').matches ? 3 : 1;
                }

                function maxIndex() {
                    return Math.max(0, cards.length - perView());
                }

                function update() {
                    if (index > maxIndex()) {
                        index = maxIndex();
                    }

                    var width = cards[0].getBoundingClientRect().width;
                    track.style.transform = 'translateX(' + (-index * width) + 'px)';
                    prev.disabled = index <= 0;
                    next.disabled = index >= maxIndex();
                }

                next.addEventListener('click', function () {
                    index = Math.min(maxIndex(), index + perView());
                    update();
                });

                prev.addEventListener('click', function () {
                    index = Math.max(0, index - perView());
                    update();
                });

                window.addEventListener('resize', update);
                update();
            });
});
            if (reviewPrev) reviewPrev.addEventListener('click', function () { reviewIndex -= 1; showReview(); });
            window.addEventListener('resize', showReview);
            showReview();
        });
    </script>
@endpush
