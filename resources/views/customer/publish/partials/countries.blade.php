<section class="pb-card pb-countries" aria-labelledby="countries-title">
    <h2 id="countries-title">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M3 12h18M12 3c2.5 2.8 3.8 5.8 3.8 9S14.5 18.2 12 21c-2.5-2.8-3.8-5.8-3.8-9S9.5 5.8 12 3Z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
        Top 5 visitor countries
    </h2>
    @if ($offer['top_countries'] === [])
        <p>—</p>
    @endif
    <ol>
        @foreach ($offer['top_countries'] as $country)
            <li>
                <div class="pb-country-row">
                    <span class="pb-country-name"><span class="pb-code">{{ $country['code'] }}</span> {{ $country['name'] }}</span>
                    <span>{{ $country['label'] }}</span>
                </div>
                <div class="pb-track" aria-hidden="true"><span class="{{ $loop->first ? 'is-first' : '' }}" style="width: {{ min(100, $country['percent']) }}%"></span></div>
            </li>
        @endforeach
        <li>
            <div class="pb-country-row">
                <span class="pb-country-name">Others</span>
                <span>{{ number_format($offer['others_percent'], 2) }}%</span>
            </div>
            <div class="pb-track" aria-hidden="true"><span style="width: {{ min(100, $offer['others_percent']) }}%"></span></div>
        </li>
    </ol>
    <p class="pb-country-foot">Share of estimated organic visitors · Last updated {{ $offer['updated_at']->format('j F Y') }}</p>
</section>
