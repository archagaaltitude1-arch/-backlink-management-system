@extends('layouts.public')

@section('document_title', 'Cart – BacklinkPro')

@section('meta_description', 'Publishers added for an order, with the price stored on each listing.')

@section('content')
    @php
        $loginHref = \Illuminate\Support\Facades\Route::has('auth.login') ? route('auth.login') : null;
        $features = [
            ['Publisher list', 'Every stored guest-post publisher.', route('publishers.index')],
            ['Wishlist', 'Publishers you want to come back to.', route('wishlist.index')],
            ['Niches', 'Publishers grouped by category.', route('niches.index')],
            ['Services', 'Guest posts, niche edits, and links.', url('/services')],
            ['Packages', 'Backlink packages by size.', route('packages.index')],
            ['SEO tools', 'Check a stored domain.', route('tools.index')],
        ];
    @endphp

    <style>
        .bag { background: #F2F2F2; }
        .bag-wrap { max-width: 1240px; margin: 0 auto; padding: 28px 24px 72px; }
        .bag-crumb { margin: 0; color: #4A5560; font-size: 14px; font-weight: 700; }
        .bag-crumb a { color: inherit; }
        .bag-crumb a:hover { color: #C0340D; }
        .bag-hero { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(260px, .65fr); gap: 20px; margin-top: 22px; }
        .bag-intro { position: relative; overflow: hidden; border: 1px solid #E3E5E8; border-radius: 24px; background: #fff; padding: 36px 36px 32px; }
        .bag-intro::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: linear-gradient(90deg, #D63A10 0%, #FF4917 55%, #FFB199 100%); }
        .bag-kicker { margin: 0; color: #C0340D; font-size: 13px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .bag-intro h1 { margin: 10px 0 0; color: #152029; font-size: 40px; line-height: 1.1; font-weight: 800; }
        .bag-lead { max-width: 560px; margin: 12px 0 0; color: #4A5560; font-size: 17px; line-height: 1.65; }
        .bag-count { display: inline-flex; min-height: 36px; align-items: center; margin-top: 18px; border-radius: 999px; background: #FFF1EC; padding: 0 14px; color: #C0340D; font-size: 14px; font-weight: 800; }
        .bag-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .bag-btn { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; border-radius: 12px; padding: 0 18px; font-size: 15px; font-weight: 800; }
        .bag-btn-primary { border: 0; background: #D63A10; color: #fff; }
        .bag-btn-primary:hover { background: #FF4917; }
        .bag-btn-line { border: 1.5px solid #152029; background: #fff; color: #152029; }
        .bag-btn-line:hover { background: #F2F2F2; }
        .bag-steps { border-radius: 24px; background: #152029; color: #fff; padding: 28px 24px; }
        .bag-steps h2 { margin: 0; font-size: 20px; font-weight: 800; }
        .bag-steps ol { margin: 16px 0 0; padding: 0; list-style: none; }
        .bag-steps li { display: flex; gap: 12px; margin-top: 14px; color: #D5DCE3; font-size: 15px; line-height: 1.5; }
        .bag-num { display: inline-flex; width: 36px; height: 36px; flex: none; align-items: center; justify-content: center; border-radius: 999px; background: #D63A10; color: #fff; font-size: 15px; font-weight: 800; }
        .bag-note { margin: 16px 0 0; border-radius: 14px; padding: 12px 14px; font-size: 15px; font-weight: 700; }
        .bag-note-ok { background: #E8F7EE; color: #146C43; }
        .bag-note-bad { background: #FFF1EC; color: #C0340D; }
        .bag-layout { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(260px, .6fr); gap: 16px; margin-top: 22px; align-items: start; }
        .bag-block h2 { margin: 0; color: #152029; font-size: 26px; font-weight: 800; }
        .bag-block > p { max-width: 640px; margin: 6px 0 0; color: #4A5560; font-size: 16px; line-height: 1.6; }
        .bag-lines { display: grid; gap: 12px; margin: 16px 0 0; padding: 0; list-style: none; }
        .bag-line { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; gap: 16px; align-items: center; border: 1px solid #E3E5E8; border-radius: 20px; background: #fff; padding: 18px 20px; }
        .bag-line h3 { margin: 0; overflow: hidden; color: #152029; font-size: 18px; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
        .bag-meta { margin: 4px 0 0; color: #4A5560; font-size: 14px; font-weight: 700; }
        .bag-price { margin: 6px 0 0; color: #C0340D; font-size: 16px; font-weight: 800; }
        .bag-qty { display: flex; align-items: center; gap: 8px; }
        .bag-qty label { color: #4A5560; font-size: 13px; font-weight: 800; }
        .bag-qty input { width: 72px; min-height: 44px; border: 1.5px solid #E3E5E8; border-radius: 12px; padding: 0 10px; color: #152029; font-size: 16px; font-weight: 800; }
        .bag-summary { border-radius: 24px; background: #152029; color: #fff; padding: 24px; }
        .bag-summary h2 { margin: 0; font-size: 20px; font-weight: 800; }
        .bag-summary dl { margin: 16px 0 0; }
        .bag-summary div { display: flex; justify-content: space-between; gap: 12px; margin-top: 10px; color: #D5DCE3; font-size: 15px; }
        .bag-summary dd { margin: 0; font-weight: 800; color: #fff; }
        .bag-total { margin-top: 16px; border-top: 1px solid rgba(255,255,255,.16); padding-top: 16px; }
        .bag-total strong { display: block; color: #fff; font-size: 28px; }
        .bag-summary p { margin: 14px 0 0; color: #D5DCE3; font-size: 14px; line-height: 1.55; }
        .bag-summary .bag-btn { width: 100%; margin-top: 16px; }
        .bag-empty { margin-top: 16px; border: 1px dashed #E3E5E8; border-radius: 24px; background: #fff; padding: 28px; color: #4A5560; font-size: 16px; line-height: 1.6; }
        .bag-picks { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin: 16px 0 0; padding: 0; list-style: none; }
        .bag-pick { display: flex; min-height: 180px; flex-direction: column; border: 1px solid #E3E5E8; border-radius: 20px; background: #fff; padding: 16px; }
        .bag-pick h3 { margin: 0; overflow: hidden; color: #152029; font-size: 16px; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
        .bag-pick p { margin: 6px 0 0; color: #4A5560; font-size: 13px; line-height: 1.45; }
        .bag-pick form { margin-top: auto; padding-top: 14px; }
        .bag-pick .bag-btn { width: 100%; }
        .bag-features { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin: 16px 0 0; padding: 0; list-style: none; }
        .bag-feature { display: flex; min-height: 92px; flex-direction: column; justify-content: center; border: 1px solid #E3E5E8; border-radius: 18px; background: #fff; padding: 16px 18px; }
        .bag-feature:hover { border-color: #D63A10; }
        .bag-feature strong { color: #152029; font-size: 16px; }
        .bag-feature span { margin-top: 4px; color: #4A5560; font-size: 14px; line-height: 1.45; }
        .bag-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .bag-pill { display: inline-flex; min-height: 44px; align-items: center; border: 1px solid #E3E5E8; border-radius: 999px; background: #fff; padding: 0 14px; color: #152029; font-size: 14px; font-weight: 800; }
        .bag-pill:hover { background: #FFF1EC; color: #C0340D; }
        .co-grid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(280px, .7fr); gap: 18px; margin-top: 22px; align-items: start; }
        .co-card, .co-pay { border: 1px solid #E3E5E8; border-radius: 16px; background: #fff; }
        .co-card { padding: 22px 22px 18px; }
        .co-card + .co-card { margin-top: 14px; }
        .co-card h2 { margin: 0; color: #152029; font-family: Montserrat, sans-serif; font-size: 22px; font-weight: 800; line-height: 1.25; word-break: break-word; }
        .co-top { display: grid; grid-template-columns: 168px minmax(0, 1fr); gap: 16px; margin-top: 16px; }
        .co-preview { position: relative; overflow: hidden; border: 1px solid #E3E5E8; border-radius: 8px; background: #F4F7FB; aspect-ratio: 16 / 10; }
        .co-preview img { width: 100%; height: 100%; object-fit: cover; }
        .co-preview span { position: absolute; left: 8px; right: 8px; top: 38%; color: #152029; font-size: 11px; font-weight: 800; word-break: break-word; }
        .co-meta { margin: 0; color: #152029; font-size: 14px; line-height: 1.7; }
        .co-meta p { margin: 0; }
        .co-k { color: #152029; font-weight: 800; }
        .co-project { display: flex; align-items: center; gap: 6px; }
        .co-project input { width: 140px; min-height: 36px; border: 0; border-bottom: 1px solid #E3E5E8; background: transparent; color: #152029; font: inherit; font-weight: 700; }
        .co-project svg { flex: none; color: #4A5560; }
        .co-stats { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 16px; }
        .co-tile { flex: 1 1 120px; border-radius: 12px; background: #EEF5FC; padding: 12px 8px; text-align: center; }
        .co-tile strong { display: block; color: #152029; font-size: 16px; word-break: break-word; }
        .co-tile strong.is-follow { color: #17663A; }
        .co-tile span { color: #4A5560; font-size: 12px; }
        .co-price { margin: 0 4px 0 auto; color: #152029; font-family: Montserrat, sans-serif; font-size: 20px; font-weight: 800; }
        .co-trash { display: inline-flex; width: 44px; height: 44px; align-items: center; justify-content: center; border: 0; background: transparent; color: #4A5560; }
        .co-rules { margin-top: 14px; border-radius: 12px; background: #EEF5FC; padding: 14px 16px; }
        .co-rules h3 { display: flex; align-items: center; gap: 8px; margin: 0 0 8px; color: #152029; font-size: 15px; font-weight: 800; }
        .co-rules ul { margin: 0; padding-left: 18px; color: #152029; font-size: 14px; line-height: 1.55; }
        .co-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 16px; }
        .co-tools input[type="text"] { min-height: 44px; min-width: 180px; flex: 1 1 180px; border: 1px solid #E3E5E8; border-radius: 8px; padding: 0 12px; font: inherit; }
        .co-apply { min-height: 44px; border: 0; border-radius: 8px; background: #D63A10; padding: 0 16px; color: #fff; font-weight: 800; letter-spacing: .04em; }
        .co-text { min-height: 44px; border: 0; background: transparent; color: #152029; font-weight: 800; letter-spacing: .04em; text-decoration: underline; }
        .co-pay { padding: 18px; }
        .co-row { display: flex; justify-content: space-between; gap: 12px; color: #152029; font-size: 15px; }
        .co-row + .co-row { margin-top: 12px; }
        .co-row strong { font-weight: 700; }
        .co-total { margin-top: 12px; border-top: 1px solid #E3E5E8; padding-top: 12px; font-weight: 800; }
        .co-total strong { font-family: Montserrat, sans-serif; font-size: 20px; }
        .co-go { display: flex; align-items: center; justify-content: center; width: 100%; min-height: 52px; margin-top: 16px; border: 0; border-radius: 8px; background: #FF7A45; color: #fff; font-size: 16px; font-weight: 800; text-decoration: none; }
        .co-go:hover { background: #D63A10; color: #fff; }
        .co-pay p { margin: 12px 0 0; color: #4A5560; font-size: 13px; line-height: 1.5; }
        @media (max-width: 1100px) {
            .bag-picks { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .bag-line { grid-template-columns: 1fr; }
        }
        @media (max-width: 860px) {
            .bag-hero, .bag-layout, .bag-features, .co-grid, .co-top { grid-template-columns: 1fr; }
            .bag-intro h1 { font-size: 32px; }
            .bag-picks { grid-template-columns: 1fr; }
        }
    </style>

    <section class="bag">
        <div class="bag-wrap">
            <nav class="bag-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <span>Cart</span>
            </nav>

            @if ($items->isEmpty())
            <div class="bag-hero">
                <div class="bag-intro">
                    <p class="bag-kicker">Shopping</p>
                    <h1>Your cart</h1>
                    <p class="bag-lead">Add a publisher from the cards below. The price on each line is the price already stored for that website, multiplied by the quantity you choose.</p>
                    <p class="bag-count">{{ $quantity }} {{ $quantity === 1 ? 'placement' : 'placements' }}</p>
                    <div class="bag-actions">
                        <a class="bag-btn bag-btn-primary" href="#add-a-publisher">Add a publisher</a>
                        <a class="bag-btn bag-btn-line" href="{{ route('publishers.index') }}">Open the full list</a>
                    </div>
                </div>
                <aside class="bag-steps" aria-labelledby="bag-steps-title">
                    <h2 id="bag-steps-title">How an order starts</h2>
                    <ol>
                        <li><span class="bag-num">1</span><span>Add the publisher you want to order from.</span></li>
                        <li><span class="bag-num">2</span><span>Set the quantity. The line total uses the stored price.</span></li>
                        <li><span class="bag-num">3</span><span>Come back from the cart icon in the navbar.</span></li>
                    </ol>
                </aside>
            </div>
            @endif

            @if (session('shopping_status'))
                <p class="bag-note bag-note-ok" role="status">{{ session('shopping_status') }}</p>
            @endif
            @if (session('shopping_error'))
                <p class="bag-note bag-note-bad" role="alert">{{ session('shopping_error') }}</p>
            @endif

            @if ($items->isEmpty())
                <p class="bag-empty">The cart is empty. Add a publisher below. Only websites already listed on the site can be added.</p>
            @else
                <div class="co-grid">
                    <div>
                        @foreach ($items as $item)
                            @php
                                $lineTotal = is_numeric($item['price'] ?? null) ? (float) $item['price'] * (int) $item['quantity'] : null;
                                $follow = strcasecmp((string) ($item['link_type'] ?? ''), 'dofollow') === 0;
                                $rules = is_array($item['rules'] ?? null) ? $item['rules'] : [];
                                $statLabels = $item['stat_labels'] ?? ['Backlinks', 'Link validity', 'Link type', 'Article length'];
                                $line = (string) ($item['line'] ?? md5((string) $item['domain']));
                                $linePrefix = trim((string) ($item['price_prefix'] ?? ''));
                            @endphp
                            <article class="co-card">
                                <h2>{{ ($item['kind'] ?? '') === 'package' ? ($item['display_name'] ?? 'Package') : 'Publish Guest Post on '.($item['display_name'] ?? $item['domain']) }}</h2>
                                <div class="co-top">
                                    <div class="co-preview">
                                        @if (! empty($item['screenshot']))
                                            <img src="{{ $item['screenshot'] }}" alt="">
                                        @else
                                            <img src="{{ asset('images/publish/preview-placeholder.svg') }}" alt="">
                                            <span>{{ ($item['kind'] ?? '') === 'package' ? ($item['display_name'] ?? 'Package') : $item['domain'] }}</span>
                                        @endif
                                    </div>
                                    <div class="co-meta">
                                        <p class="co-project">
                                            <span class="co-k">Project:</span>
                                            <input form="cart-update" name="lines[{{ $line }}][project]" value="{{ $item['project'] ?? 'Default' }}" aria-label="Project name for {{ $item['domain'] }}" maxlength="80">
                                            <input form="cart-update" type="hidden" name="lines[{{ $line }}][quantity]" value="{{ (int) $item['quantity'] }}">
                                            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4l10-10-4-4L4 16v4Z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m13 7 4 4" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                                        </p>
                                        <p><span class="co-k">Order type:</span> {{ $item['order_label'] ?? 'Publish my article' }}@if (! empty($item['order_help'])) ({{ $item['order_help'] }})@endif</p>
                                        @if (($item['kind'] ?? '') !== 'package')
                                            <p><span class="co-k">Your article topic:</span> {{ $item['content_topic'] ?? 'General' }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="co-stats">
                                    <div class="co-tile"><strong>{{ $item['backlinks'] ?? '—' }}</strong><span>{{ $statLabels[0] }}</span></div>
                                    <div class="co-tile"><strong>{{ $item['link_validity'] ?? 'Permanent' }}</strong><span>{{ $statLabels[1] }}</span></div>
                                    <div class="co-tile"><strong class="{{ $follow ? 'is-follow' : '' }}">{{ $item['link_type'] ?? 'Dofollow' }}</strong><span>{{ $statLabels[2] }}</span></div>
                                    <div class="co-tile"><strong>{{ $item['article_length'] ?? '—' }}</strong><span>{{ $statLabels[3] }}</span></div>
                                    <p class="co-price">{{ $lineTotal === null ? 'Price is not stored' : ($linePrefix === '' ? '' : $linePrefix.' ').'$'.number_format($lineTotal, 2) }}</p>
                                    <form method="POST" action="{{ route('shopping.destroy') }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="line" value="{{ $line }}">
                                        <input type="hidden" name="domain" value="{{ $item['domain'] }}">
                                        <button class="co-trash" type="submit" aria-label="Remove {{ $item['domain'] }}">
                                            <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V5h6v2M8 7l1 13h6l1-13" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                                        </button>
                                    </form>
                                </div>
                                @if ($rules !== [])
                                    <div class="co-rules">
                                        <h3>
                                            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="4" width="12" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M9 4.5h6v3H9zM9 11h6M9 14h6" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                                            {{ ($item['kind'] ?? '') === 'package' ? 'What you get' : 'Publishing rules' }}
                                        </h3>
                                        <ul>
                                            @foreach ($rules as $rule)
                                                <li>{{ $rule }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </article>
                        @endforeach

                        <div class="co-tools">
                            <form method="POST" action="{{ route('shopping.coupon') }}" style="display:flex;flex-wrap:wrap;gap:10px;flex:1">
                                @csrf
                                <input type="text" name="coupon_code" value="{{ $coupon }}" placeholder="Coupon code" aria-label="Coupon code" maxlength="40">
                                <button class="co-apply" type="submit">Apply coupon</button>
                            </form>
                            <form method="POST" action="{{ route('shopping.clear') }}">
                                @csrf
                                <button class="co-text" type="submit">Clear cart</button>
                            </form>
                            <form id="cart-update" method="POST" action="{{ route('shopping.update') }}">
                                @csrf
                                @method('PATCH')
                                <button class="co-text" type="submit">Update cart</button>
                            </form>
                        </div>
                    </div>

                    <aside class="co-pay" aria-label="Order total">
                        <div class="co-row"><span>Subtotal</span><strong>${{ number_format($subtotal, 2) }}</strong></div>
                        @if ($discount > 0)
                            <div class="co-row"><span>Coupon {{ $coupon }}</span><strong>- ${{ number_format($discount, 2) }}</strong></div>
                        @endif
                        <div class="co-row co-total"><span>Total</span><strong>${{ number_format($total, 2) }}</strong></div>
                        @if (collect($items)->contains(fn ($row) => filled($row['price_prefix'] ?? null)))
                            <p>A line marked From is a starting amount. The quotation is confirmed after we review the campaign.</p>
                        @endif
                        <a class="co-go" href="{{ $checkoutHref }}">Proceed to checkout</a>
                        <p>Your project, order type, article topic, and publisher rules stay with this cart. Sign in at checkout to pay.</p>
                    </aside>
                </div>
            @endif

            <div class="bag-block" id="add-a-publisher" style="margin-top:28px">
                <h2>Add a publisher</h2>
                <p>Highest stored Domain Rating first. Adding one puts it in the cart with a quantity of 1.</p>
                @if ($picks->isEmpty())
                    <p class="bag-empty">Every publisher currently offered here is already in the cart.</p>
                @else
                    <ul class="bag-picks">
                        @foreach ($picks as $row)
                            <li class="bag-pick">
                                <h3>{{ $row['domain'] }}</h3>
                                <p>
                                    {{ $row['niches'][0] ?? 'Publisher' }}
                                    @if ($row['dr'] !== null)
                                        · DR {{ $row['dr'] }}
                                    @endif
                                    @if (is_numeric($row['price'] ?? null))
                                        · ${{ number_format((float) $row['price'], 2) }}
                                    @endif
                                </p>
                                <form method="POST" action="{{ route('shopping.store') }}">
                                    @csrf
                                    <input type="hidden" name="domain" value="{{ $row['domain'] }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button class="bag-btn bag-btn-primary" type="submit">Add to cart</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bag-block" style="margin-top:28px">
                <h2>Other features</h2>
                <ul class="bag-features">
                    @foreach ($features as [$title, $text, $href])
                        <li>
                            <a class="bag-feature" href="{{ $href }}">
                                <strong>{{ $title }}</strong>
                                <span>{{ $text }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="bag-pills" aria-label="Site lists">
                    @foreach ($sites as $site)
                        <a class="bag-pill" href="{{ route('sites.show', $site['slug']) }}">{{ $site['label'] }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
