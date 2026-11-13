<article class="nd-site-card">
    <div class="nd-site-top">
        <div class="nd-site-id">
            <div class="nd-url">
                <a href="{{ $site['demo'] || empty($site['id']) ? '#' : url('/marketplace/'.$site['id']) }}" class="font-bold text-navy underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $site['domain'] }}</a>
                <span class="text-[#FF4917]" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="m12 3.2 2.4 5.4 5.9.6-4.4 3.9 1.3 5.8L12 16.2 6.8 19l1.3-5.8L3.7 9.2l5.9-.6L12 3.2Z"/></svg>
                </span>
                @include('customer.niches.actions', ['site' => $site, 'part' => 'save', 'demo' => $demo, 'canSave' => $canSave, 'canCart' => $canCart, 'loginHref' => $loginHref])
            </div>
            <ul class="nd-niches nd-site-niches">
                @foreach ($site['niches'] as $pill)
                    <li>{{ $pill }}</li>
                @endforeach
            </ul>
            <p class="nd-site-lang">{{ $site['language'] }}</p>
        </div>
        <div class="nd-site-offer">
            <div>
                <span class="nd-metric">{{ $site['price'] === null ? '' : '$'.number_format($site['price'], 2) }}</span>
                @if ($site['credits'] !== null)
                    <span class="nd-site-credits">{{ $site['credits'] }} credits</span>
                @endif
            </div>
            @include('customer.niches.actions', ['site' => $site, 'part' => 'buy', 'demo' => $demo, 'canSave' => $canSave, 'canCart' => $canCart, 'loginHref' => $loginHref])
        </div>
    </div>
    <div class="nd-site-stats">
        <dl class="nd-site-metrics">
            <div><dt>AS</dt><dd>{{ $site['as'] ?? '' }}</dd></div>
            <div><dt>DA</dt><dd>{{ $site['da'] ?? '' }}</dd></div>
            <div><dt>DR</dt><dd>{{ $site['dr'] ?? '' }}</dd></div>
            <div><dt>Ahrefs keywords</dt><dd>{{ \App\Support\DemoNicheData::compact($site['keywords']) }}</dd></div>
        </dl>
        <div class="nd-chart">
            <span class="nd-site-label">Ahrefs traffic</span>
            <x-ui.sparkline :seed="$site['domain'].'-ahrefs'" color="#E24B4B" />
            <span class="nd-metric">{{ \App\Support\DemoNicheData::compact($site['traffic']) }}</span>
        </div>
        <div>
            <span class="nd-site-label">Countries</span>
            <div class="nd-countries">
                @forelse (array_slice($site['top_countries'], 0, 3) as $country)
                    @php
                        $flagCode = strtoupper((string) ($country[0] ?? ''));
                        $flagCode = $flagCode === 'UK' ? 'gb' : strtolower($flagCode);
                    @endphp
                    <span class="nd-country">
                        <span class="inline-flex shrink-0 overflow-hidden rounded-[2px] ring-1 ring-black/10" style="width:20px;height:14px">
                            <x-ui.flag :code="$flagCode" width="20" height="14" class="h-full w-full" />
                        </span>
                        {{ number_format((float) ($country[1] ?? 0), 1) }}%
                    </span>
                @empty
                    <span></span>
                @endforelse
            </div>
        </div>
        <div class="nd-chart">
            <span class="nd-site-label">Semrush traffic</span>
            <x-ui.sparkline :seed="$site['domain'].'-semrush'" color="#22A06B" />
            <span class="nd-metric">{{ \App\Support\DemoNicheData::compact($site['semrush_traffic'] ?? null) }}</span>
        </div>
    </div>
</article>
