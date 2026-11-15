@php
    $cartItems = [];
    $cartQty = 0;
    $cartSubtotal = 0.0;

    foreach (session('shopping_cart', []) as $key => $item) {
        if (! is_array($item) || blank($item['domain'] ?? null)) {
            continue;
        }

        $qty = max(1, (int) ($item['quantity'] ?? 1));
        $price = is_numeric($item['price'] ?? null) ? (float) $item['price'] : null;
        $prefix = trim((string) ($item['price_prefix'] ?? ''));
        $title = (string) ($item['display_name'] ?? $item['domain']);
        $orderLabel = (string) ($item['order_label'] ?? (($item['kind'] ?? '') === 'package' ? 'Backlink campaign' : 'Guest post'));
        $line = (string) ($item['line'] ?? $key);
        $money = $price === null
            ? 'Price on request'
            : (($prefix !== '' ? $prefix.' ' : '').'$'.number_format($price * $qty, 2));

        if ($price !== null) {
            $cartSubtotal += $price * $qty;
        }

        $cartQty += $qty;
        $cartItems[] = [
            'line' => $line,
            'title' => $title,
            'order_label' => $orderLabel,
            'quantity' => $qty,
            'money' => $money,
            'kind' => (string) ($item['kind'] ?? 'publisher'),
            'badge' => (string) ($item['backlinks'] ?? $item['article_length'] ?? ''),
        ];
    }

    $cartHref = \Illuminate\Support\Facades\Route::has('shopping.index') ? route('shopping.index') : url('/cart');
    $checkoutHref = \Illuminate\Support\Facades\Route::has('shopping.checkout') ? route('shopping.checkout') : $cartHref;
    $openCart = (bool) session('open_cart');
@endphp

<style>
    .cart-drawer-root { position: relative; z-index: 80; }
    .cart-drawer-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(21, 32, 41, 0.45);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .cart-drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        display: flex;
        flex-direction: column;
        width: min(100vw, 420px);
        height: 100vh;
        background: #fff;
        color: #152029;
        box-shadow: -12px 0 40px rgba(21, 32, 41, 0.18);
        transform: translateX(100%);
        transition: transform 0.28s ease;
    }
    .cart-drawer-root.is-open .cart-drawer-backdrop {
        opacity: 1;
        pointer-events: auto;
    }
    .cart-drawer-root.is-open .cart-drawer-panel { transform: translateX(0); }
    .cart-drawer-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid #E3E5E8;
        padding: 18px 20px;
    }
    .cart-drawer-head h2 {
        margin: 0;
        font-family: Montserrat, sans-serif;
        font-size: 22px;
        font-weight: 800;
    }
    .cart-drawer-close {
        display: inline-grid;
        place-items: center;
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #152029;
        cursor: pointer;
    }
    .cart-drawer-close:hover { background: #F2F2F2; }
    .cart-drawer-body {
        flex: 1;
        overflow: auto;
        padding: 8px 20px 20px;
    }
    .cart-drawer-empty {
        margin: 28px 0 0;
        color: #4A5560;
        font-size: 15px;
        line-height: 1.6;
    }
    .cart-drawer-list { margin: 0; padding: 0; list-style: none; }
    .cart-drawer-item {
        display: grid;
        grid-template-columns: 72px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: start;
        padding: 18px 0;
        border-bottom: 1px solid #EDEFF1;
    }
    .cart-drawer-thumb {
        position: relative;
        overflow: hidden;
        width: 72px;
        height: 72px;
        border-radius: 10px;
        background: linear-gradient(145deg, #152029, #2A3F4D);
        color: #fff;
        padding: 8px;
    }
    .cart-drawer-thumb span {
        display: block;
        font-size: 10px;
        font-weight: 800;
        line-height: 1.25;
        text-transform: uppercase;
        word-break: break-word;
    }
    .cart-drawer-thumb b {
        position: absolute;
        top: 0;
        right: 0;
        background: #FF4917;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 800;
    }
    .cart-drawer-copy h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.35;
    }
    .cart-drawer-copy p {
        margin: 6px 0 0;
        color: #4A5560;
        font-size: 13px;
        line-height: 1.45;
    }
    .cart-drawer-copy strong {
        display: block;
        margin-top: 10px;
        font-size: 16px;
        font-weight: 800;
    }
    .cart-drawer-remove {
        display: inline-grid;
        place-items: center;
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #4A5560;
        cursor: pointer;
    }
    .cart-drawer-remove:hover { background: #FFF1EC; color: #C0340D; }
    .cart-drawer-foot {
        border-top: 1px solid #E3E5E8;
        padding: 16px 20px 22px;
        background: #fff;
    }
    .cart-drawer-sub {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin: 0 0 14px;
        font-size: 15px;
        font-weight: 800;
    }
    .cart-drawer-checkout {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 52px;
        border: 0;
        border-radius: 8px;
        background: #FF4917;
        color: #fff;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-decoration: none;
        text-transform: uppercase;
    }
    .cart-drawer-checkout:hover { background: #D63A10; color: #fff; }
    .cart-drawer-view {
        display: block;
        margin-top: 14px;
        color: #152029;
        font-size: 15px;
        font-weight: 700;
        text-align: center;
        text-decoration: underline;
    }
    .cart-drawer-view:hover { color: #C0340D; }
    .cart-drawer-root :focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
    body.cart-drawer-open { overflow: hidden; }
</style>

<div
    id="cart-drawer"
    class="cart-drawer-root{{ $openCart ? ' is-open' : '' }}"
    data-open="{{ $openCart ? '1' : '0' }}"
    data-count="{{ $cartQty }}"
    @if (! $openCart) hidden @endif
    aria-hidden="{{ $openCart ? 'false' : 'true' }}"
>
    <div class="cart-drawer-backdrop" data-cart-close tabindex="-1"></div>
    <aside class="cart-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title">
        <div class="cart-drawer-head">
            <h2 id="cart-drawer-title">Shopping Cart ({{ $cartQty }})</h2>
            <button type="button" class="cart-drawer-close" data-cart-close aria-label="Close cart">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>

        <div class="cart-drawer-body">
            @if ($cartItems === [])
                <p class="cart-drawer-empty">Your cart is empty. Add a package or publisher to get started.</p>
            @else
                <ul class="cart-drawer-list">
                    @foreach ($cartItems as $item)
                        <li class="cart-drawer-item">
                            <div class="cart-drawer-thumb" aria-hidden="true">
                                <span>{{ $item['title'] }}</span>
                                @if ($item['badge'] !== '')
                                    <b>{{ $item['badge'] }}</b>
                                @endif
                            </div>
                            <div class="cart-drawer-copy">
                                <h3>{{ $item['title'] }}</h3>
                                <p><span style="color:#4A5560">Order Type:</span> {{ $item['order_label'] }}</p>
                                <strong>{{ $item['money'] }}</strong>
                            </div>
                            <form method="POST" action="{{ route('shopping.destroy') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="line" value="{{ $item['line'] }}">
                                <button class="cart-drawer-remove" type="submit" aria-label="Remove {{ $item['title'] }}">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M9 7V5h6v2m-8 0 1 14h8l1-14"/></svg>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="cart-drawer-foot">
            <p class="cart-drawer-sub">
                <span>Subtotal ({{ $cartQty }} {{ $cartQty === 1 ? 'item' : 'items' }})</span>
                <span>${{ number_format($cartSubtotal, 2) }}</span>
            </p>
            <a class="cart-drawer-checkout" href="{{ $checkoutHref }}">Checkout</a>
            <a class="cart-drawer-view" href="{{ $cartHref }}">View Cart</a>
        </div>
    </aside>
</div>

<script>
(function () {
    var root = document.getElementById('cart-drawer');
    if (!root) return;
    var open = function () {
        root.hidden = false;
        root.classList.add('is-open');
        root.setAttribute('aria-hidden', 'false');
        document.body.classList.add('cart-drawer-open');
    };
    var close = function () {
        root.classList.remove('is-open');
        root.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('cart-drawer-open');
        window.setTimeout(function () {
            if (!root.classList.contains('is-open')) root.hidden = true;
        }, 280);
    };

    window.BacklinkProCart = { open: open, close: close };

    root.querySelectorAll('[data-cart-close]').forEach(function (node) {
        node.addEventListener('click', close);
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && root.classList.contains('is-open')) close();
    });

    document.querySelectorAll('[data-cart-open]').forEach(function (node) {
        node.addEventListener('click', function (event) {
            event.preventDefault();
            open();
        });
    });

    if (root.getAttribute('data-open') === '1') {
        document.body.classList.add('cart-drawer-open');
        open();
    }
})();
</script>
