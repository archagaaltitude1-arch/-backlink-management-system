@extends('layouts.public')

@php
    $story = $package['story'] ?? null;
    $title = $package['label'];
    $unit = is_numeric($package['price'] ?? null) ? (float) $package['price'] : null;
    $prefix = trim((string) ($package['price_prefix'] ?? ''));
    $money = $unit === null ? 'Price on request' : ($prefix === '' ? '' : $prefix.' ').'$'.number_format($unit, 2);
    $chooseHref = route('packages.websites', $package['slug']);
    $selectedBacklinks = $selectedBacklinks ?? null;
    $highlightTier = function (?string $text): string {
        $safe = e((string) $text);

        return preg_replace(
            '/\b(Professional|Enterprise|Starter|Growth|Pro)\b/',
            '<span class="pk-tier">$1</span>',
            $safe,
            1,
        ) ?? $safe;
    };
@endphp

@section('document_title', $title.' | BacklinkPro')
@section('meta_description', $story['lead'] ?? $package['summary'])

@section('content')
    <style>
        .pk-wrap { max-width: none; margin: 0 auto; padding: 28px 20px 72px; color: #152029; }
        .pk-crumb { margin: 0 0 16px; color: #4A5560; font-size: 14px; font-weight: 700; }
        .pk-crumb a { color: inherit; text-decoration: none; }
        .pk-crumb a:hover { color: #C0340D; }
        .pk-grid { display: grid; gap: 20px; }
        .pk-hero { position: relative; overflow: hidden; min-height: 420px; border: 1px solid rgba(198, 161, 91, 0.45); border-radius: 16px; background: linear-gradient(160deg, #152029 0%, #0c0c0e 62%, #2a2114 100%); color: #fff; padding: 36px 28px; }
        .pk-hero h2 { position: relative; z-index: 1; margin: 48px 0 0; max-width: 460px; font-family: var(--font-sans), sans-serif; font-size: clamp(34px, 4vw, 52px); font-weight: 800; line-height: 1.15; text-transform: uppercase; }
        .pk-tier { display: inline-block; border-radius: 4px; background: #000; color: #fff; padding: 0.04em 0.28em 0.08em; line-height: 1.05; }
        .pk-gold { position: absolute; top: 0; right: 0; z-index: 1; width: 180px; height: 180px; background: #C6A15B; clip-path: polygon(100% 0, 0 0, 100% 100%); }
        .pk-badge { position: absolute; top: 0; right: 0; z-index: 2; width: 164px; height: 164px; background: #FF4917; clip-path: polygon(100% 0, 0 0, 100% 100%); }
        .pk-badge span { position: absolute; top: 28px; right: 12px; max-width: 92px; font-family: var(--font-sans), sans-serif; font-size: 22px; font-weight: 800; line-height: 1.05; text-align: right; }
        .pk-marks { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: 8px; max-width: 440px; margin-top: 28px; }
        .pk-mark { display: inline-flex; align-items: center; min-height: 36px; border: 1px solid rgba(198, 161, 91, 0.55); border-radius: 8px; padding: 4px 10px; background: rgba(255,255,255,.08); font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .pk-price-row { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 16px; }
        .pk-price-row .pk-price { margin: 0; }
        .pk-cat { display: inline-flex; align-items: center; gap: 6px; min-height: 36px; border: 1px solid #E3E5E8; border-radius: 8px; padding: 4px 10px; color: #4A5560; font-size: 14px; font-weight: 700; }
        .pk-hero ul { position: relative; z-index: 1; margin: 28px 0 0; padding: 0; list-style: none; max-width: 420px; }
        .pk-hero li { position: relative; margin-top: 10px; padding-left: 16px; font-size: 16px; line-height: 1.4; }
        .pk-hero li::before { content: ""; position: absolute; left: 0; top: 0.55em; width: 6px; height: 6px; border-radius: 50%; background: #C6A15B; }
        .pk-panel { background: #fff; border: 1px solid #E3E5E8; border-radius: 16px; padding: 22px; }
        .pk-panel h1 { margin: 0; font-family: var(--font-sans), sans-serif; font-size: 32px; font-weight: 800; line-height: 1.25; }
        .pk-price { margin: 16px 0 0; font-family: var(--font-sans), sans-serif; font-size: 34px; font-weight: 800; }
        .pk-lead, .pk-copy p { color: #152029; font-size: 15px; line-height: 1.65; }
        .pk-details { margin-top: 16px; color: #4A5560; font-size: 14px; font-weight: 600; line-height: 1.6; }
        .pk-pipe { margin: 0 8px; color: #C5CAD1; font-weight: 400; }
        .pk-sample { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; margin-top: 16px; padding: 8px 18px; border: 1px solid #FF4917; border-radius: 8px; background: #fff; color: #FF4917; font-size: 14px; font-weight: 800; letter-spacing: 0.02em; text-decoration: none; }
        .pk-sample:hover { background: #FFF1EC; color: #C0340D; }
        .pk-copy { margin-top: 22px; }
        .pk-copy h2 { margin: 22px 0 8px; font-family: var(--font-sans), sans-serif; font-size: 20px; font-weight: 800; }
        .pk-copy ul, .pk-copy ol { margin: 0; padding-left: 18px; }
        .pk-copy li + li { margin-top: 6px; }
        .pk-rules { margin-top: 18px; border-radius: 12px; background: #EEF5FC; padding: 16px; }
        .pk-rules h2 { display: flex; align-items: center; gap: 8px; margin: 0 0 8px; font-size: 16px; }
        .pk-rules ul { margin: 0; padding-left: 18px; }
        .pk-rules li + li { margin-top: 6px; }
        .pk-choice { display: flex; gap: 10px; align-items: flex-start; margin-top: 8px; min-height: 44px; }
        .pk-summary { margin-top: 16px; padding-top: 12px; border-top: 1px solid #E3E5E8; }
        .pk-row { display: flex; justify-content: space-between; gap: 12px; margin-top: 8px; }
        .pk-sub { margin-top: 12px; padding-top: 12px; border-top: 1px solid #E3E5E8; font-family: var(--font-sans), sans-serif; font-weight: 800; }
        .pk-sub strong { font-size: 22px; }
        .pk-note { margin: 14px 0; color: #4A5560; font-size: 14px; line-height: 1.55; }
        .pk-cart { display: flex; align-items: center; justify-content: center; width: 100%; min-height: 52px; border: 0; border-radius: 8px; background: #D63A10; color: #fff; font-size: 16px; font-weight: 800; text-decoration: none; }
        .pk-cart:hover { background: #D63A10; color: #fff; }
        .pk-tat { display: flex; align-items: center; gap: 10px; margin-top: 12px; border-radius: 10px; background: #E7F5EC; padding: 12px; }
        .pk-tat b { border-radius: 999px; background: #17663A; color: #fff; padding: 4px 8px; font-size: 13px; }
        .pk-help { margin: 12px 0 0; }
        .pk-help a { color: #C0340D; font-weight: 700; }
        .pk-qty { display: flex; align-items: center; gap: 8px; margin-top: 14px; }
        .pk-qty button, .pk-qty input { min-width: 44px; min-height: 44px; border: 1px solid #E3E5E8; border-radius: 10px; background: #fff; text-align: center; font: inherit; }
        .pk-qty input { width: 64px; }
        .pk-wrap :focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        @media (min-width: 960px) {
            .pk-grid { grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr); align-items: start; }
            .pk-panel h1 { font-size: 36px; }
        }
    </style>

    <section class="bg-[#F7F8FA]">
        <div class="pk-wrap">
            @if (session('shopping_status'))
                <p class="pk-note" role="status">{{ session('shopping_status') }}</p>
            @endif
            <nav class="pk-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('packages.index') }}">Packages</a>
                <span aria-hidden="true"> / </span>
                <span>{{ $package['name'] }}</span>
            </nav>

            <div class="pk-grid">
                <aside class="pk-hero" aria-label="{{ $title }}">
                    <div class="pk-gold" aria-hidden="true"></div>
                    <div class="pk-badge"><span>{{ $story['badge'] ?? ($story['count'] ?? $package['size']) }}</span></div>
                    <h2>{!! $highlightTier($story['hero'] ?? ($package['name'].' Backlink Package')) !!}</h2>
                    <ul>
                        @foreach (($story['gets'] ?? []) as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                    @if (! empty($story['marks']))
                        <div class="pk-marks">
                            @foreach ($story['marks'] as $mark)
                                <span class="pk-mark">{{ $mark }}</span>
                            @endforeach
                        </div>
                    @endif
                </aside>

                <article class="pk-panel">
                    <h1>{!! $highlightTier($title) !!}</h1>
                    <div class="pk-price-row">
                        <p class="pk-price" data-pk-price="{{ $unit === null ? '' : $unit }}" data-pk-prefix="{{ $package['price_prefix'] ?? '' }}">{{ $money }}</p>
                        @if (! empty($story['category']))
                            <span class="pk-cat">{{ $story['category'] }}</span>
                        @endif
                    </div>
                    @if ($story)
                        <p class="pk-lead">{{ $story['lead'] }}</p>
                        <p class="pk-lead">{{ $story['support'] }}</p>
                    @else
                        <p class="pk-lead">{{ $package['summary'] }}</p>
                    @endif
                    @php
                        $detailChips = ! empty($story['chips'])
                            ? $story['chips']
                            : [
                                ($story['count'] ?? $package['size']).' backlinks',
                                $story['delivery'] ?? 'Delivery confirmed with the order',
                                'Report included',
                                'Order tracking',
                            ];
                    @endphp
                    <p class="pk-details">
                        @foreach ($detailChips as $chip)
                            @if (! $loop->first)
                                <span class="pk-pipe" aria-hidden="true">|</span>
                            @endif
                            <span>{{ $chip }}</span>
                        @endforeach
                    </p>
                    @php
                        $sampleReports = [
                            'display-starter' => 'reports/starter-backlink-sample-report.xlsx',
                            'display-growth' => 'reports/growth-backlink-sample-report.xlsx',
                            'display-professional' => 'reports/professional-backlink-sample-report.xlsx',
                            'display-enterprise' => 'reports/enterprise-backlink-sample-report.xlsx',
                        ];
                        $sampleReport = $story['sample'] ?? ($sampleReports[$package['slug']] ?? null);
                    @endphp
                    @if ($sampleReport)
                        <a class="pk-sample" href="{{ asset($sampleReport) }}" target="_blank" rel="noopener">View Sample Report</a>
                    @endif

                    @if ($story && ($story['layout'] ?? '') !== 'offer')
                        <div class="pk-copy">
                            <h2>{{ $story['headline'] }}</h2>
                            <h2>What you get</h2>
                            <ul>
                                @foreach ($story['gets'] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                            <h2>Who is this package for?</h2>
                            <ul>
                                @foreach ($story['audience'] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                            <h2>How it works</h2>
                            <ol>
                                @foreach ($story['steps'] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                    @if ($story)
                        <div class="pk-rules">
                            <h2>Publishing rules</h2>
                            <ul>
                                @forelse ($story['rules'] ?? [] as $rule)
                                    <li>{{ $rule }}</li>
                                @empty
                                    <li>Our team keeps each placement relevant to the niche in the order.</li>
                                    <li>Anchor text is placed so it reads naturally on the page.</li>
                                    <li>We do not publish adult content, hate speech, or misleading claims.</li>
                                    <li>{{ $story['delivery_note'] }}</li>
                                @endforelse
                            </ul>
                        </div>
                    @endif

                    <form method="GET" action="{{ $chooseHref }}" data-pk-order>
                            <input type="hidden" name="quantity" value="1" data-pk-qty>
                            <fieldset style="margin:18px 0 0;padding:0;border:0">
                                <legend style="font-weight:800">Order type *</legend>
                                @forelse (data_get($story, 'order_types', []) as $type)
                                    <label class="pk-choice">
                                        <input type="radio" name="order_type" value="{{ $type['value'] }}" @checked($loop->first)>
                                        <span>{{ $type['label'] }}</span>
                                    </label>
                                @empty
                                    <label class="pk-choice">
                                        <input type="radio" name="order_type" value="package" checked>
                                        <span>{{ $package['slug'] === 'display-enterprise' ? 'Custom campaign (We review your goals and confirm the quotation.)' : 'Backlink campaign (We place the links and send the report.)' }}</span>
                                    </label>
                                @endforelse
                            </fieldset>
                            @if (! empty($selectedBacklinks))
                                <input type="hidden" name="backlinks" value="{{ $selectedBacklinks }}">
                            @endif
                            @if (($package['price_prefix'] ?? '') === '' && empty($selectedBacklinks))
                                <div class="pk-qty">
                                    <button type="button" data-pk-step="-1" aria-label="Decrease quantity">−</button>
                                    <input type="number" min="1" max="10" value="1" data-pk-input aria-label="Quantity">
                                    <button type="button" data-pk-step="1" aria-label="Increase quantity">+</button>
                                </div>
                            @endif
                            <div class="pk-summary">
                                <div class="pk-row">
                                    <span data-pk-line>1× {{ $title }}</span>
                                    <span data-pk-line-price>{{ $money }}</span>
                                </div>
                                <div class="pk-row pk-sub">
                                    <span>Subtotal</span>
                                    <strong data-pk-sub>{{ $money }}</strong>
                                </div>
                            </div>
                            <p class="pk-note">@if (! empty($selectedBacklinks)) Next, choose {{ $selectedBacklinks }} websites for this package. @else {{ data_get($story, 'note', 'Next, choose the websites for this package. Anchor text and target pages are added at checkout.') }} @endif</p>
                            <button class="pk-cart" type="submit">Choose Backlinks</button>
                        </form>

                    <p class="pk-tat"><b>Estimated delivery</b> <span>{{ $story['delivery'] ?? 'Confirmed with the order' }}{{ isset($story['delivery_note']) ? '. '.$story['delivery_note'] : '' }}</span></p>
                    <p class="pk-help"><strong>Questions?</strong> <a href="{{ route('contact') }}">Contact us</a></p>
                    @if ($package['slug'] === 'display-enterprise')
                        <p class="pk-help">Contact our team to discuss your custom backlink campaign.</p>
                    @endif
                </article>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.querySelector('[data-pk-order]');
    var priceNode = document.querySelector('[data-pk-price]');
    if (!form || !priceNode) return;
    var unit = priceNode.getAttribute('data-pk-price') === '' ? null : Number(priceNode.getAttribute('data-pk-price'));
    var prefix = priceNode.getAttribute('data-pk-prefix') || '';
    var input = form.querySelector('[data-pk-input]');
    var hidden = form.querySelector('[data-pk-qty]');
    if (!input || !hidden) return;
    var money = function (amount) {
        return prefix + '$' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
    var paint = function () {
        var qty = Math.min(10, Math.max(1, Number(input.value) || 1));
        input.value = String(qty);
        hidden.value = String(qty);
        form.querySelector('[data-pk-line]').textContent = qty + '× ' + @json($title);
        var label = unit === null ? 'Price on request' : money(unit * qty);
        form.querySelector('[data-pk-line-price]').textContent = label;
        form.querySelector('[data-pk-sub]').textContent = label;
    };
    form.querySelectorAll('[data-pk-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            input.value = String(Number(input.value || 1) + Number(button.getAttribute('data-pk-step')));
            paint();
        });
    });
    input.addEventListener('input', paint);
    paint();
})();
</script>
@endpush
