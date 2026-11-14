@extends('layouts.public')

@section('document_title', 'Checkout – BacklinkPro')
@section('meta_description', 'Add your target URL, anchor text and requirements, apply a coupon, then place your BacklinkPro order.')

@section('content')
    <style>
        .ck { background: #F7F8FA; color: #152029; }
        .ck-wrap { max-width: 1120px; margin: 0 auto; padding: 28px 20px 72px; }
        .ck-crumb { margin: 0 0 18px; color: #4A5560; font-size: 14px; font-weight: 700; }
        .ck-crumb a { color: inherit; text-decoration: none; }
        .ck-crumb a:hover { color: #C0340D; }
        .ck-title { margin: 0; font-family: Montserrat, sans-serif; font-size: clamp(28px, 4vw, 40px); font-weight: 800; line-height: 1.15; }
        .ck-lead { margin: 10px 0 0; max-width: 620px; color: #4A5560; font-size: 16px; line-height: 1.6; }
        .ck-note { margin: 16px 0 0; border-radius: 12px; padding: 12px 14px; font-size: 14px; font-weight: 700; }
        .ck-note-ok { background: #E8F7EE; color: #146C43; }
        .ck-note-bad { background: #FFF1EC; color: #C0340D; }
        .ck-grid { display: grid; gap: 18px; margin-top: 24px; align-items: start; }
        .ck-card, .ck-side {
            border: 1px solid #E3E5E8;
            border-radius: 20px;
            background: #fff;
            padding: 22px;
        }
        .ck-card + .ck-card { margin-top: 14px; }
        .ck-card h2, .ck-side h2 {
            margin: 0;
            font-family: Montserrat, sans-serif;
            font-size: 18px;
            font-weight: 800;
        }
        .ck-meta { margin: 6px 0 0; color: #4A5560; font-size: 14px; line-height: 1.5; }
        .ck-price { margin: 10px 0 0; color: #C0340D; font-size: 18px; font-weight: 800; }
        .ck-fields { display: grid; gap: 12px; margin-top: 16px; }
        .ck-fields label { display: grid; gap: 6px; font-size: 13px; font-weight: 800; }
        .ck-fields input, .ck-fields textarea {
            min-height: 46px;
            border: 1px solid #E3E5E8;
            border-radius: 12px;
            background: #F7F8FA;
            padding: 10px 12px;
            color: #152029;
            font: inherit;
            font-size: 15px;
            font-weight: 500;
        }
        .ck-fields textarea { min-height: 90px; resize: vertical; }
        .ck-fields input:focus, .ck-fields textarea:focus {
            outline: none;
            border-color: #FF4917;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(255, 73, 23, 0.12);
        }
        .ck-error { margin: 0; color: #C0340D; font-size: 13px; font-weight: 700; }
        .ck-coupon-box {
            margin-top: 16px;
            border: 1px dashed #E3E5E8;
            border-radius: 14px;
            background: #FFF8F5;
            padding: 14px;
        }
        .ck-coupon-box p {
            margin: 0 0 10px;
            font-size: 14px;
            font-weight: 800;
        }
        .ck-coupon-row { display: flex; flex-wrap: wrap; gap: 8px; }
        .ck-coupon-row input {
            flex: 1 1 160px;
            min-height: 44px;
            border: 1px solid #E3E5E8;
            border-radius: 10px;
            padding: 0 12px;
            font: inherit;
            font-weight: 700;
            text-transform: uppercase;
        }
        .ck-apply, .ck-remove, .ck-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            border: 0;
            border-radius: 10px;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
        }
        .ck-apply { background: #152029; color: #fff; }
        .ck-apply:hover { background: #243844; color: #fff; }
        .ck-remove { background: #fff; border: 1px solid #E3E5E8; color: #152029; }
        .ck-remove:hover { background: #F2F2F2; }
        .ck-submit {
            width: 100%;
            min-height: 52px;
            margin-top: 16px;
            background: #D63A10;
            color: #fff;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .ck-submit:hover { background: #FF4917; color: #fff; }
        .ck-row { display: flex; justify-content: space-between; gap: 12px; margin-top: 10px; color: #4A5560; font-size: 15px; }
        .ck-row strong { color: #152029; }
        .ck-total { margin-top: 14px; padding-top: 14px; border-top: 1px solid #E3E5E8; font-family: Montserrat, sans-serif; font-size: 18px; font-weight: 800; }
        .ck-side > p { margin: 14px 0 0; color: #4A5560; font-size: 13px; line-height: 1.55; }
        .ck-back { display: inline-flex; margin-top: 14px; color: #C0340D; font-size: 14px; font-weight: 700; text-decoration: none; }
        .ck-back:hover { text-decoration: underline; }
        .ck-wrap :focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        @media (min-width: 900px) {
            .ck-grid { grid-template-columns: minmax(0, 1.35fr) minmax(280px, 0.65fr); }
            .ck-fields { grid-template-columns: 1fr 1fr; }
            .ck-fields .ck-span { grid-column: 1 / -1; }
        }
    </style>

    <section class="ck">
        <div class="ck-wrap">
            <nav class="ck-crumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <span aria-hidden="true"> / </span>
                <a href="{{ route('shopping.index') }}">Cart</a>
                <span aria-hidden="true"> / </span>
                <span>Checkout</span>
            </nav>

            <h1 class="ck-title">Checkout</h1>
            <p class="ck-lead">Add your website URL, anchor text and target details for each item. Have a coupon? Enter your code before you place the order.</p>

            @if (session('shopping_status'))
                <p class="ck-note ck-note-ok" role="status">{{ session('shopping_status') }}</p>
            @endif
            @if (session('shopping_error'))
                <p class="ck-note ck-note-bad" role="alert">{{ session('shopping_error') }}</p>
            @endif
            @if ($errors->any())
                <p class="ck-note ck-note-bad" role="alert">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('shopping.checkout.store') }}" class="ck-grid">
                @csrf
                <div>
                    @foreach ($items as $index => $item)
                        @php
                            $line = (string) ($item['line'] ?? md5((string) $item['domain']));
                            $qty = (int) ($item['quantity'] ?? 1);
                            $unit = (float) $item['price'];
                            $lineTotal = $unit * $qty;
                            $prefix = trim((string) ($item['price_prefix'] ?? ''));
                            $title = ($item['kind'] ?? '') === 'package'
                                ? (string) ($item['display_name'] ?? 'Package')
                                : 'Publish on '.($item['display_name'] ?? $item['domain']);
                        @endphp
                        <article class="ck-card">
                            <h2>{{ $title }}</h2>
                            <p class="ck-meta">
                                {{ $item['order_label'] ?? 'Placement' }}
                                · Qty {{ $qty }}
                            </p>
                            <p class="ck-price">{{ $prefix !== '' ? $prefix.' ' : '' }}${{ number_format($lineTotal, 2) }}</p>

                            <input type="hidden" name="items[{{ $index }}][line]" value="{{ $line }}">
                            <div class="ck-fields">
                                <label>
                                    Target URL *
                                    <input type="url" name="items[{{ $index }}][target_url]" value="{{ old('items.'.$index.'.target_url') }}" required maxlength="2048" placeholder="https://your-site.com/page">
                                    @error('items.'.$index.'.target_url')<p class="ck-error">{{ $message }}</p>@enderror
                                </label>
                                <label>
                                    Anchor text *
                                    <input type="text" name="items[{{ $index }}][anchor_text]" value="{{ old('items.'.$index.'.anchor_text') }}" required maxlength="255" placeholder="Your brand or keyword">
                                    @error('items.'.$index.'.anchor_text')<p class="ck-error">{{ $message }}</p>@enderror
                                </label>
                                <label>
                                    Article topic *
                                    <input type="text" name="items[{{ $index }}][article_topic]" value="{{ old('items.'.$index.'.article_topic', $item['content_topic'] ?? '') }}" required maxlength="255" placeholder="Topic for this placement">
                                    @error('items.'.$index.'.article_topic')<p class="ck-error">{{ $message }}</p>@enderror
                                </label>
                                <label class="ck-span">
                                    Additional requirements
                                    <textarea name="items[{{ $index }}][requirements]" maxlength="5000" placeholder="Niche notes, preferred pages, or other instructions">{{ old('items.'.$index.'.requirements') }}</textarea>
                                    @error('items.'.$index.'.requirements')<p class="ck-error">{{ $message }}</p>@enderror
                                </label>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="ck-side">
                    <h2>Order summary</h2>
                    <div class="ck-row"><span>Items</span><strong>{{ $quantity }}</strong></div>
                    <div class="ck-row"><span>Subtotal</span><strong>${{ number_format($subtotal, 2) }}</strong></div>
                    @if ($discount > 0)
                        <div class="ck-row"><span>Coupon {{ $coupon }}</span><strong>-${{ number_format($discount, 2) }}</strong></div>
                    @endif
                    <div class="ck-row ck-total"><span>Total</span><span>${{ number_format($total, 2) }}</span></div>

                    <div class="ck-coupon-box">
                        <p>Have a coupon? Enter your code</p>
                        <div class="ck-coupon-row">
                            <input form="checkout-coupon" type="text" name="coupon_code" value="{{ old('coupon_code', $coupon) }}" maxlength="40" placeholder="COUPON CODE" aria-label="Coupon code">
                            <button class="ck-apply" form="checkout-coupon" type="submit">Apply</button>
                            @if ($coupon)
                                <button class="ck-remove" form="checkout-coupon-remove" type="submit">Remove</button>
                            @endif
                        </div>
                        @error('coupon_code')<p class="ck-error" style="margin-top:8px">{{ $message }}</p>@enderror
                    </div>

                    <input type="hidden" name="coupon_code" value="{{ $coupon }}">
                    <button class="ck-submit" type="submit">Place order</button>
                    <p>After you place the order you can complete payment. Your target URL, anchor text and requirements stay with the order.</p>
                    <a class="ck-back" href="{{ route('shopping.index') }}">← Back to cart</a>
                </aside>
            </form>

            <form id="checkout-coupon" method="POST" action="{{ route('shopping.coupon') }}" hidden>
                @csrf
            </form>
            <form id="checkout-coupon-remove" method="POST" action="{{ route('shopping.coupon.destroy') }}" hidden>
                @csrf
                @method('DELETE')
            </form>
        </div>
    </section>
@endsection
