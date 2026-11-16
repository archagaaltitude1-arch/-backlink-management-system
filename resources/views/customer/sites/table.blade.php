@once
    <style>
        .nd-table-wrap { overflow-x: auto; background: #fff; }
        .nd-table { width: 100%; min-width: 1330px; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
        .nd-table col.c-url { width: 180px; }
        .nd-table col.c-num { width: 52px; }
        .nd-table col.c-niche { width: 140px; }
        .nd-table col.c-lang { width: 96px; }
        .nd-table col.c-chart { width: 150px; }
        .nd-table col.c-keys { width: 84px; }
        .nd-table col.c-countries { width: 110px; }
        .nd-table col.c-price { width: 96px; }
        .nd-table col.c-buy { width: 120px; }
        .nd-table th { padding: 12px 6px; border-right: 1px solid rgba(255, 255, 255, .28); border-bottom: 1px solid #B7C0C8; background: #152029; color: #fff; font-size: 12px; font-weight: 800; line-height: 1.25; text-align: left; vertical-align: middle; }
        .nd-table td { padding: 10px 6px; border-right: 1px solid #B7C0C8; border-bottom: 1px solid #B7C0C8; background: #fff; vertical-align: top; font-size: 13px; color: #152029; }
        .nd-table th:last-child, .nd-table td:last-child { border-right: 0; }
        .nd-table th:first-child, .nd-table td:first-child { padding-left: 12px; }
        .nd-table th:last-child, .nd-table td:last-child { padding-right: 12px; }
        .nd-table .nd-spark { width: 72px; height: 22px; }
        .nd-table .nd-publish { min-height: 40px; gap: 4px; padding: 0 8px; font-size: 12px; letter-spacing: 0.02em; }
        .nd-table .nd-url { gap: 6px; min-width: 0; }
        .nd-table .nd-url a { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
        .nd-table .nd-niches { font-size: 12px; line-height: 1.35; }
        .nd-table th.c-num, .nd-table td.c-num, .nd-table th.c-keys, .nd-table td.c-keys { text-align: center; }
        .nd-table tbody tr:last-child td { border-bottom: 0; }
        .nd-url { display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; }
        .nd-url a { white-space: nowrap; }
        .nd-metric { font-size: 15px; font-weight: 800; line-height: 1.2; color: #152029; white-space: nowrap; }
        .nd-niches { margin: 0; padding: 0; list-style: none; color: #4A5560; font-size: 13px; line-height: 1.55; }
        .nd-niches li + li { margin-top: 2px; }
        .nd-chart { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; width: 100%; }
        .nd-bar { display: block; width: 100%; height: 8px; overflow: hidden; border-radius: 999px; background: #F2F2F2; }
        .nd-bar > span { display: block; height: 100%; border-radius: 999px; }
        .nd-bar.is-ahrefs > span { background: #E24B4B; }
        .nd-bar.is-semrush > span { background: #22A06B; }
        tr.is-chart-target td { background: #FFF1EC; }
        h2.is-chart-target { outline: 2px solid #D63A10; outline-offset: 4px; }
        .nd-spark { display: block; width: 72px; height: 22px; }
        .nd-countries { display: flex; flex-direction: column; gap: 4px; }
        .nd-country { display: flex; align-items: center; gap: 6px; color: #152029; font-size: 13px; font-weight: 700; white-space: nowrap; }
        .nd-publish { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; border: 0; border-radius: 10px; background: #D63A10; padding: 0 16px; color: #fff; font-size: 13px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; text-decoration: none; }
        .nd-publish:hover { background: #FF4917; color: #fff; }
        .nd-publish:disabled,
        .nd-publish:disabled:hover { background: #D63A10; color: #fff; opacity: 1; cursor: not-allowed; }
        .nd-publish:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .nd-pick { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; }
        .nd-pick input { width: 20px; height: 20px; accent-color: #D63A10; }
        @media (max-width: 1099px) {
            .nd-table-wrap { overflow-x: auto; }
        }
    </style>
@endonce
@php
    $loginHref = \Illuminate\Support\Facades\Route::has('auth.login') ? route('auth.login') : url('/login');
    $canSave = auth()->check() && \Illuminate\Support\Facades\Route::has('customer.saved-websites.store');
    $canCart = auth()->check() && \Illuminate\Support\Facades\Route::has('orders.cart.items.store');
@endphp
<div class="nd-table-wrap rounded-[20px] border border-[#E3E5E8]">
    <table class="nd-table">
        <colgroup>
            <col class="c-url">
            <col class="c-num">
            <col class="c-num">
            <col class="c-num">
            <col class="c-niche">
            <col class="c-lang">
            <col class="c-chart">
            <col class="c-keys">
            <col class="c-countries">
            <col class="c-chart">
            <col class="c-price">
            <col class="c-buy">
        </colgroup>
        <thead>
            <tr>
                <th scope="col">Website URL</th>
                <th class="c-num" scope="col">AS</th>
                <th class="c-num" scope="col">DA</th>
                <th class="c-num" scope="col">DR</th>
                <th scope="col">Niche</th>
                <th scope="col">Language</th>
                <th scope="col">Ahrefs<br>traffic</th>
                <th class="c-keys" scope="col">Ahrefs<br>keywords</th>
                <th scope="col">Countries</th>
                <th scope="col">Semrush<br>traffic</th>
                <th scope="col">@if (! empty($insertionPrice)) Link insertion<br>price @else Price @endif</th>
                <th scope="col">@if (! empty($selectable)) Select @else Buy now @endif</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $publisher)
                @php
                    $rowSlug = \Illuminate\Support\Str::slug((string) ($publisher['domain'] ?? 'site'));
                    $ahrefs = is_numeric($publisher['traffic'] ?? null) ? (int) $publisher['traffic'] : 0;
                    $semrush = is_numeric($publisher['semrush_traffic'] ?? null) ? (int) $publisher['semrush_traffic'] : 0;
                    $addedAt = $publisher['added_at'] ?? null;
                    $addedStamp = $addedAt instanceof \DateTimeInterface ? $addedAt->getTimestamp() : 0;
                    $countryCode = strtoupper((string) ($publisher['top_countries'][0][0] ?? ''));
                    $flag = static function (mixed $value): string {
                        if (! is_bool($value)) {
                            return '';
                        }

                        return $value ? 'yes' : 'no';
                    };
                @endphp
                <tr id="site-row-{{ $rowSlug !== '' ? $rowSlug : 'site' }}" @if (! empty($filterable)) data-publisher-row data-as="{{ (int) ($publisher['as'] ?? 0) }}" data-da="{{ (int) ($publisher['da'] ?? 0) }}" data-dr="{{ (int) ($publisher['dr'] ?? 0) }}" data-traffic="{{ (int) ($publisher['traffic'] ?? 0) }}" data-semrush="{{ $semrush }}" data-keywords="{{ (int) ($publisher['keywords'] ?? 0) }}" data-backlinks="{{ (int) ($publisher['backlinks'] ?? 0) }}" data-pa="{{ (int) ($publisher['pa'] ?? 0) }}" data-tf="{{ (int) ($publisher['tf'] ?? 0) }}" data-spam="{{ (int) ($publisher['spam'] ?? 0) }}" data-referring="{{ (int) ($publisher['referring_domains'] ?? 0) }}" data-price="{{ is_numeric($publisher['price'] ?? null) ? (float) $publisher['price'] : 0 }}" data-added="{{ $addedStamp }}" data-language="{{ $publisher['language'] ?? '' }}" data-country="{{ $countryCode }}" data-type="{{ $publisher['link_type'] ?? '' }}" data-validity="{{ $publisher['link_validity'] ?? '' }}" data-sports="{{ $flag($publisher['sports_allowed'] ?? null) }}" data-pharmacy="{{ $flag($publisher['pharmacy_allowed'] ?? null) }}" data-crypto="{{ $flag($publisher['crypto_allowed'] ?? null) }}" data-news="{{ $flag($publisher['google_news'] ?? null) }}" data-sponsored="{{ $flag($publisher['sponsored'] ?? null) }}" data-foreign="{{ $flag($publisher['foreign_language'] ?? null) }}" data-niche="{{ implode('|', $publisher['niches'] ?? []) }}" data-search="{{ mb_strtolower(trim(($publisher['domain'] ?? '').' '.implode(' ', $publisher['niches'] ?? []).' '.($publisher['language'] ?? ''))) }}" @endif>
                    <td>
                        <div class="nd-url">
                            <a href="{{ ! empty($publisher['demo']) || empty($publisher['id']) ? '#' : url('/marketplace/'.$publisher['id']) }}" class="font-bold text-navy underline-offset-2 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">{{ $publisher['domain'] }}</a>
                            <span class="text-[#FF4917]" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="m12 3.2 2.4 5.4 5.9.6-4.4 3.9 1.3 5.8L12 16.2 6.8 19l1.3-5.8L3.7 9.2l5.9-.6L12 3.2Z"/></svg>
                            </span>
                            @include('customer.niches.actions', ['site' => $publisher, 'part' => 'save', 'demo' => $demo ?? ! empty($publisher['demo']), 'canSave' => $canSave, 'canCart' => $canCart, 'loginHref' => $loginHref])
                        </div>
                    </td>
                    <td class="c-num nd-metric">{{ $publisher['as'] ?? '' }}</td>
                    <td class="c-num nd-metric">{{ $publisher['da'] ?? '' }}</td>
                    <td class="c-num nd-metric">{{ $publisher['dr'] ?? '' }}</td>
                    <td>
                        <ul class="nd-niches">
                            @foreach ($publisher['niches'] ?? [] as $pill)
                                <li>{{ $pill }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td>{{ $publisher['language'] ?? '' }}</td>
                    <td>
                        <div class="nd-chart">
                            <x-ui.sparkline :seed="$publisher['domain'].'-ahrefs'" color="#E98989" :value="$ahrefs > 0 ? $ahrefs : null" :label="'Ahrefs traffic trend for '.$publisher['domain']" />
                        </div>
                    </td>
                    <td class="c-keys nd-metric">{{ \App\Support\DemoNicheData::compact(is_numeric($publisher['keywords'] ?? null) ? (int) $publisher['keywords'] : null) }}</td>
                    <td>
                        <div class="nd-countries">
                            @forelse (array_slice($publisher['top_countries'] ?? [], 0, 3) as $country)
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
                    </td>
                    <td>
                        <div class="nd-chart">
                            <x-ui.sparkline :seed="$publisher['domain'].'-semrush'" color="#7DCEA0" :value="$semrush > 0 ? $semrush : null" :label="'Semrush traffic trend for '.$publisher['domain']" />
                        </div>
                    </td>
                    <td>
                        <span class="nd-metric">{{ ($publisher['price'] ?? null) === null ? '' : '$'.number_format((float) $publisher['price'], 2) }}</span>
                    </td>
                    <td>
                        @if (! empty($selectable))
                            <label class="nd-pick">
                                <input type="checkbox" name="websites[]" value="{{ $publisher['domain'] }}" data-site-choice @if (! empty($selectionForm)) form="{{ $selectionForm }}" @endif>
                                <span class="sr-only">Select {{ $publisher['domain'] }}</span>
                            </label>
                        @else
                            @include('customer.niches.actions', ['site' => $publisher, 'part' => 'buy', 'demo' => $demo ?? ! empty($publisher['demo']), 'canSave' => $canSave, 'canCart' => $canCart, 'loginHref' => $loginHref])
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
