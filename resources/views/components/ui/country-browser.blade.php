@props([
    'counts' => [],
    'countsAvailable' => false,
    'headingId' => 'countries-heading',
    'centered' => false,
])


<section id="countries" class="bg-white" aria-labelledby="{{ $headingId }}" data-country-browser>
    <div class="mx-auto px-6 py-14 lg:py-[88px]">
        <x-ui.section-heading
            eyebrow="Browse by country"
            title="Find Publisher Websites by Country"
            subtitle="Target a specific market? Pick a country to see publisher websites with traffic from that audience."
            :heading-id="$headingId"
            :align="$centered ? 'center' : 'left'"
        />

        <style>
            .country-cloud { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px 12px; margin-top: 28px; }
            .country-pill {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                min-height: 40px;
                padding: 6px 14px 6px 8px;
                border: 1px solid #E4E7EB;
                border-radius: 12px;
                background: #fff;
                color: #1C2430;
                font-size: 14px;
                font-weight: 600;
                line-height: 1.2;
                text-decoration: none;
            }
            .country-pill:hover { border-color: #C5CBD1; }
            .country-pill:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
            .country-flag { display: inline-flex; width: 22px; height: 15px; flex: none; overflow: hidden; border-radius: 2px; box-shadow: 0 0 0 1px rgba(0, 0, 0, .08); }
            .country-flag svg { width: 22px; height: 15px; }
        </style>
        <div class="country-cloud">
            @foreach (\App\Support\BrowseCountries::all() as $country)
                <a class="country-pill" href="{{ route('publishers.index', ['country' => $country['code']]) }}#all-publishers">
                    <span class="country-flag"><x-ui.flag :code="$country['flag']" /></span>
                    <span>{{ $country['label'] }}</span>
                </a>
            @endforeach
        </div>

        <div @class(['mt-6', 'text-center' => $centered])>
            <a href="{{ route('publishers.index') }}#all-publishers" class="inline-flex min-h-11 items-center text-[15px] font-bold text-[#C0340D] underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">View All Websites →</a>
        </div>
    </div>
</section>
