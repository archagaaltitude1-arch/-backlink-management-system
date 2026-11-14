@extends('layouts.public')

@php
    $story = $package['story'] ?? null;
    $title = $package['label'];
    $unit = is_numeric($package['price'] ?? null) ? (float) $package['price'] : null;
    $prefix = trim((string) ($package['price_prefix'] ?? ''));
    $money = $unit === null ? 'Price on request' : ($prefix === '' ? '' : $prefix.' ').'$'.number_format($unit, 2);
    $cartPost = \Illuminate\Support\Facades\Route::has('shopping.store') ? route('shopping.store') : null;
@endphp

@section('document_title', $title.' | BacklinkPro')
@section('meta_description', $story['lead'] ?? $package['summary'])

@section('content')
    <style>
        .pk-wrap { max-width: 1240px; margin: 0 auto; padding: 28px 20px 72px; color: #152029; }
        .pk-crumb { margin: 0 0 16px; color: #4A5560; font-size: 14px; font-weight: 700; }
        .pk-crumb a { color: inherit; text-decoration: none; }
        .pk-crumb a:hover { color: #C0340D; }
        .pk-grid { display: grid; gap: 20px; }
        .pk-hero { position: relative; overflow: hidden; min-height: 420px; border-radius: 16px; background: #152029; color: #fff; padding: 36px 28px; }
        .pk-hero h2 { margin: 48px 0 0; max-width: 460px; font-family: Montserrat, sans-serif; font-size: 42px; font-weight: 800; line-height: 1.05; text-transform: uppercase; }
        .pk-badge { position: absolute; top: 0; right: 0; width: 150px; height: 150px; background: #FF4917; clip-path: polygon(100% 0, 0 0, 100% 100%); }
        .pk-badge span { position: absolute; top: 28px; right: 14px; font-family: Montserrat, sans-serif; font-size: 28px; font-weight: 800; }
        .pk-hero ul { margin: 28px 0 0; padding: 0; list-style: none; max-width: 420px; }
        .pk-hero li { position: relative; margin-top: 10px; padding-left: 16px; font-size: 16px; line-height: 1.4; }
        .pk-hero li::before { content: ""; position: absolute; left: 0; top: 0.55em; width: 6px; height: 6px; border-radius: 50%; background: #fff; }
        .pk-panel { background: #fff; border: 1px solid #E3E5E8; border-radius: 16px; padding: 22px; }
        .pk-panel h1 { margin: 0; font-family: Montserrat, sans-serif; font-size: 32px; font-weight: 800; line-height: 1.15; }
        .pk-price { margin: 16px 0 0; font-family: Montserrat, sans-serif; font-size: 34px; font-weight: 800; }
        .pk-lead, .pk-copy p { color: #152029; font-size: 15px; line-height: 1.65; }
        .pk-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .pk-chip { display: inline-flex; align-items: center; min-height: 40px; border: 1px solid #E3E5E8; border-radius: 8px; padding: 6px 10px; font-size: 14px; font-weight: 700; }
        .pk-sample { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; margin-top: 16px; padding: 8px 18px; border: 1px solid #FF4917; border-radius: 8px; background: #fff; color: #FF4917; font-size: 14px; font-weight: 800; letter-spacing: 0.02em; text-decoration: none; }
        .pk-sample:hover { background: #FFF1EC; color: #C0340D; }
        .pk-copy { margin-top: 22px; }
        .pk-copy h2 { margin: 22px 0 8px; font-family: Montserrat, sans-serif; font-size: 20px; font-weight: 800; }
        .pk-copy ul, .pk-copy ol { margin: 0; padding-left: 18px; }
        .pk-copy li + li { margin-top: 6px; }
        .pk-rules { margin-top: 18px; border-radius: 12px; background: #EEF5FC; padding: 16px; }
        .pk-rules h2 { display: flex; align-items: center; gap: 8px; margin: 0 0 8px; font-size: 16px; }
        .pk-choice { display: flex; gap: 10px; align-items: flex-start; margin-top: 8px; min-height: 44px; }
        .pk-summary { margin-top: 16px; padding-top: 12px; border-top: 1px solid #E3E5E8; }
        .pk-row { display: flex; justify-content: space-between; gap: 12px; margin-top: 8px; }
        .pk-sub { margin-top: 12px; padding-top: 12px; border-top: 1px solid #E3E5E8; font-family: Montserrat, sans-serif; font-weight: 800; }
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
            <nav class="pk-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('packages.index') }}">Packages</a>
                <span aria-hidden="true"> / </span>
                <span>{{ $package['name'] }}</span>
            </nav>

            <div class="pk-grid">
                <aside class="pk-hero" aria-label="{{ $title }}">
                    <div class="pk-badge"><span>{{ $story['count'] ?? $package['size'] }}</span></div>
                    <h2>{{ $package['name'] }} Backlink Package</h2>
                    <ul>
                        @foreach (($story['gets'] ?? []) as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                </aside>

                <article class="pk-panel">
                    <h1>{{ $title }}</h1>
                    <p class="pk-price" data-pk-price="{{ $unit === null ? '' : $unit }}" data-pk-prefix="{{ $package['price_prefix'] ?? '' }}">{{ $money }}</p>
                    @if ($story)
                        <p class="pk-lead">{{ $story['lead'] }}</p>
                        <p class="pk-lead">{{ $story['support'] }}</p>
                    @else
                        <p class="pk-lead">{{ $package['summary'] }}</p>
                    @endif
                    <div class="pk-chips">
                        <span class="pk-chip">{{ $story['count'] ?? $package['size'] }} backlinks</span>
                        <span class="pk-chip">{{ $story['delivery'] ?? 'Delivery confirmed with the order' }}</span>
                        <span class="pk-chip">Report included</span>
                        <span class="pk-chip">Order tracking</span>
                    </div>
                    @php
                        $sampleReports = [
                            'display-starter' => 'reports/starter-backlink-sample-report.xlsx',
                            'display-growth' => 'reports/growth-backlink-sample-report.xlsx',
                            'display-professional' => 'reports/professional-backlink-sample-report.xlsx',
                            'display-enterprise' => 'reports/enterprise-backlink-sample-report.xlsx',
                        ];
                    @endphp
                    @if (isset($sampleReports[$package['slug']]))
                        <a class="pk-sample" href="{{ asset($sampleReports[$package['slug']]) }}" target="_blank" rel="noopener">View Sample Report</a>
                    @endif

                    @if ($story)
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
                        <div class="pk-rules">
                            <h2>Publishing rules</h2>
                            <ul>
                                <li>Placements stay relevant to the niche and pages you submit.</li>
                                <li>Anchor text is placed so it reads naturally on the page.</li>
                                <li>Adult content, hate speech and misleading claims are not accepted.</li>
                                <li>{{ $story['delivery_note'] }}</li>
                            </ul>
                        </div>
                    @endif

                    @if ($cartPost)
                        <form method="POST" action="{{ $cartPost }}" data-pk-order>
                            @csrf
                            <input type="hidden" name="package_slug" value="{{ $package['slug'] }}">
                            <input type="hidden" name="package_id" value="{{ $package['package_id'] }}">
                            <input type="hidden" name="quantity" value="1" data-pk-qty>
                            <fieldset style="margin:18px 0 0;padding:0;border:0">
                                <legend style="font-weight:800">Order type *</legend>
                                <label class="pk-choice">
                                    <input type="radio" name="order_type" value="package" checked>
                                    <span>{{ $package['slug'] === 'display-enterprise' ? 'Custom campaign (We review your goals and confirm the quotation.)' : 'Backlink campaign (We place the links and send the report.)' }}</span>
                                </label>
                            </fieldset>
                            @if (($package['price_prefix'] ?? '') === '')
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
                            <p class="pk-note">After checkout you'll add your website URL, anchor text and target pages on the requirements page.</p>
                            <button class="pk-cart" type="submit">Add to cart</button>
                        </form>
                    @endif

                    <p class="pk-tat"><b>Estimated delivery</b> <span>{{ $story['delivery'] ?? 'Confirmed with the order' }}{{ isset($story['delivery_note']) ? '. '.$story['delivery_note'] : '' }}</span></p>
                    <p class="pk-help"><strong>Questions?</strong> Our team replies within [HOURS] hours. <a href="{{ route('contact') }}">Contact us</a></p>
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
