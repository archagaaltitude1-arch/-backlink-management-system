@once
    <style>
        .nd-table-wrap { overflow-x: hidden; background: #fff; }
        .nd-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .nd-table col.c-url { width: 17%; }
        .nd-table col.c-num { width: 4.2%; }
        .nd-table col.c-niche { width: 10.5%; }
        .nd-table col.c-lang { width: 7.5%; }
        .nd-table col.c-chart { width: 9.5%; }
        .nd-table col.c-keys { width: 6.5%; }
        .nd-table col.c-countries { width: 8.4%; }
        .nd-table col.c-price { width: 7.5%; }
        .nd-table col.c-buy { width: 11%; }
        .nd-table th { padding: 10px 6px; border-bottom: 1px solid #E3E5E8; background: #F7F8F9; color: #152029; font-size: 12px; font-weight: 800; line-height: 1.25; text-align: left; vertical-align: bottom; }
        .nd-table td { padding: 10px 6px; border-bottom: 1px solid #F0F1F3; vertical-align: top; font-size: 13px; color: #152029; }
        .nd-table th:first-child, .nd-table td:first-child { padding-left: 12px; }
        .nd-table th:last-child, .nd-table td:last-child { padding-right: 12px; }
        .nd-table .nd-spark { width: 100%; height: 32px; }
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
        .nd-spark { display: block; width: 104px; height: 36px; }
        .nd-countries { display: flex; flex-direction: column; gap: 4px; }
        .nd-country { display: flex; align-items: center; gap: 6px; color: #152029; font-size: 13px; font-weight: 700; white-space: nowrap; }
        .nd-publish { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; border: 0; border-radius: 10px; background: #D63A10; padding: 0 16px; color: #fff; font-size: 13px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; text-decoration: none; }
        .nd-publish:hover { background: #FF4917; color: #fff; }
        .nd-publish:disabled,
        .nd-publish:disabled:hover { background: #D63A10; color: #fff; opacity: 1; cursor: not-allowed; }
        .nd-publish:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        @media (max-width: 1099px) {
            .nd-table-wrap { overflow-x: auto; }
            .nd-table { width: 1100px; }
        }
    </style>
@endonce
@php
    $loginHref = \Illuminate\Support\Facades\Route::has('auth.login') ? route('auth.login') : url('/login');
    $canSave = auth()->check() && \Illuminate\Support\Facades\Route::has('customer.saved-websites.store');
    $canCart = auth()->check() && \Illuminate\Support\Facades\Route::has('orders.cart.items.store');
    $ahrefsMax = 0;
    $semrushMax = 0;
    foreach ($rows as $publisher) {
        $ahrefsMax = max($ahrefsMax, (int) ($publisher['traffic'] ?? 0));
        $semrushMax = max($semrushMax, (int) ($publisher['semrush_traffic'] ?? 0));
    }
    $barWidth = function (int $value, int $max): int {
        if ($value <= 0 || $max <= 0) {
            return 0;
        }

        return max(4, (int) round($value / $max * 100));
    };
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
                <th scope="col">Price</th>
                <th scope="col">Buy now</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $publisher)
                @php
                    $rowSlug = \Illuminate\Support\Str::slug((string) ($publisher['domain'] ?? 'site'));
                    $ahrefs = is_numeric($publisher['traffic'] ?? null) ? (int) $publisher['traffic'] : 0;
                    $semrush = is_numeric($publisher['semrush_traffic'] ?? null) ? (int) $publisher['semrush_traffic'] : 0;
                @endphp
                <tr id="site-row-{{ $rowSlug !== '' ? $rowSlug : 'site' }}">
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
                        <div class="nd-chart" role="img" aria-label="Ahrefs traffic {{ $ahrefs > 0 ? number_format($ahrefs) : 'not stored' }}">
                            <span class="nd-bar is-ahrefs" title="{{ $ahrefs > 0 ? number_format($ahrefs) : 'Not stored' }}"><span style="width: {{ $barWidth($ahrefs, $ahrefsMax) }}%"></span></span>
                            <span class="nd-metric">{{ \App\Support\DemoNicheData::compact($ahrefs > 0 ? $ahrefs : null) }}</span>
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
                        <div class="nd-chart" role="img" aria-label="Semrush traffic {{ $semrush > 0 ? number_format($semrush) : 'not stored' }}">
                            <span class="nd-bar is-semrush" title="{{ $semrush > 0 ? number_format($semrush) : 'Not stored' }}"><span style="width: {{ $barWidth($semrush, $semrushMax) }}%"></span></span>
                            <span class="nd-metric">{{ \App\Support\DemoNicheData::compact($semrush > 0 ? $semrush : null) }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="nd-metric">{{ ($publisher['price'] ?? null) === null ? '' : '$'.number_format((float) $publisher['price'], 2) }}</span>
                        @if (($publisher['credits'] ?? null) !== null)
                            <span class="mt-1 block text-xs text-[#4A5560]">{{ $publisher['credits'] }} credits</span>
                        @endif
                    </td>
                    <td>
                        @include('customer.niches.actions', ['site' => $publisher, 'part' => 'buy', 'demo' => $demo ?? ! empty($publisher['demo']), 'canSave' => $canSave, 'canCart' => $canCart, 'loginHref' => $loginHref])
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
