@props([
    'counts' => [],
    'countsAvailable' => false,
    'headingId' => 'countries-heading',
])

@php
    $regions = [
        'Africa' => ['South Africa' => 'za', 'Nigeria' => 'ng', 'Kenya' => 'ke', 'Egypt' => 'eg'],
        'Asia' => ['India' => 'in', 'Sri Lanka' => 'lk', 'Singapore' => 'sg', 'Malaysia' => 'my', 'Indonesia' => 'id', 'Philippines' => 'ph', 'Thailand' => 'th', 'Vietnam' => 'vn', 'Pakistan' => 'pk', 'Bangladesh' => 'bd', 'UAE' => 'ae', 'Japan' => 'jp'],
        'Europe' => ['United Kingdom' => 'gb', 'Germany' => 'de', 'France' => 'fr', 'Spain' => 'es', 'Italy' => 'it', 'Netherlands' => 'nl', 'Poland' => 'pl', 'Sweden' => 'se', 'Ireland' => 'ie', 'Portugal' => 'pt'],
        'North America' => ['United States' => 'us', 'Canada' => 'ca', 'Mexico' => 'mx'],
        'South America' => ['Brazil' => 'br', 'Argentina' => 'ar', 'Chile' => 'cl', 'Colombia' => 'co'],
        'Oceania' => ['Australia' => 'au', 'New Zealand' => 'nz'],
    ];
@endphp

<section id="countries" class="bg-white" aria-labelledby="{{ $headingId }}" data-country-browser>
    <div class="mx-auto max-w-[1240px] px-6 py-14 lg:py-[88px]">
        <x-ui.section-heading
            eyebrow="Browse by country"
            title="Find Publisher Websites by Country"
            subtitle="Target a specific market? Pick a region or country to see publisher websites with traffic from that audience."
            :heading-id="$headingId"
        />

        <div class="mt-8 flex flex-wrap gap-2" role="group" aria-label="Filter countries by region">
            @foreach (['all' => 'All', 'africa' => 'Africa', 'asia' => 'Asia', 'europe' => 'Europe', 'north-america' => 'North America', 'south-america' => 'South America', 'oceania' => 'Oceania'] as $key => $label)
                <button
                    type="button"
                    data-region-tab="{{ $key }}"
                    aria-pressed="{{ $key === 'all' ? 'true' : 'false' }}"
                    @class([
                        'inline-flex min-h-11 items-center rounded-xl border px-4 text-[15px] font-bold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]',
                        'border-navy bg-navy text-white' => $key === 'all',
                        'border-[#E3E5E8] bg-white text-navy' => $key !== 'all',
                    ])
                >{{ $label }}</button>
            @endforeach
        </div>

        <div class="mt-6 grid grid-cols-[repeat(auto-fit,minmax(160px,1fr))] gap-3">
            @foreach ($regions as $region => $countries)
                @foreach ($countries as $country => $code)
                    <a
                        href="{{ url('/marketplace?country='.rawurlencode($country)) }}"
                        data-country-region="{{ \Illuminate\Support\Str::slug($region) }}"
                        class="flex min-h-11 items-center gap-2 rounded-[20px] border border-[#E3E5E8] bg-white px-4 py-3 text-[15px] font-bold text-navy hover:border-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                    >
                        <span class="inline-flex shrink-0 overflow-hidden rounded-[3px] ring-1 ring-black/15" style="width:36px;height:24px">
                            <x-ui.flag :code="$code" class="h-full w-full" />
                        </span>
                        <span class="min-w-0 flex-1">{{ $country }}</span>
                        @if ($countsAvailable)
                            <span class="rounded-full bg-[#F2F2F2] px-2 py-0.5 text-xs font-bold text-[#4A5560]">{{ $counts[mb_strtolower($country)] ?? 0 }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </div>

        <a href="{{ url('/marketplace') }}" class="mt-6 inline-flex min-h-11 items-center text-[15px] font-bold text-[#C0340D] underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">View All Websites →</a>
    </div>
</section>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-country-browser]').forEach(function (root) {
                    var tabs = root.querySelectorAll('[data-region-tab]');
                    var items = root.querySelectorAll('[data-country-region]');

                    function apply(region) {
                        tabs.forEach(function (tab) {
                            var on = tab.getAttribute('data-region-tab') === region;
                            tab.setAttribute('aria-pressed', on ? 'true' : 'false');
                            tab.classList.toggle('border-navy', on);
                            tab.classList.toggle('bg-navy', on);
                            tab.classList.toggle('text-white', on);
                            tab.classList.toggle('border-[#E3E5E8]', !on);
                            tab.classList.toggle('bg-white', !on);
                            tab.classList.toggle('text-navy', !on);
                        });

                        items.forEach(function (item) {
                            item.hidden = region !== 'all' && item.getAttribute('data-country-region') !== region;
                        });
                    }

                    tabs.forEach(function (tab) {
                        tab.addEventListener('click', function () {
                            apply(tab.getAttribute('data-region-tab'));
                        });
                    });
                });
            });
        </script>
    @endpush
@endonce
