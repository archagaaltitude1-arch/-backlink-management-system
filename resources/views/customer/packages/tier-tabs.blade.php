@php
    $tabs = $tabs ?? [];
    $active = $tabs[0]['key'] ?? 'starter';
@endphp

<style>
    .tier-box {
        border: 1px solid #E3E8EE;
        border-radius: 24px;
        background: #fff;
        padding: 22px;
        box-shadow: 0 22px 48px -32px rgba(21, 32, 41, 0.45);
    }
    .tier-nav {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        border-radius: 16px;
        background: #F4F7FA;
        padding: 6px;
    }
    .tier-tab {
        min-height: 48px;
        border: 0;
        border-radius: 12px;
        background: transparent;
        color: #152029;
        font: inherit;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
    }
    .tier-tab.is-active {
        background: #152029;
        color: #fff;
        box-shadow: inset 0 -3px 0 #17663A;
    }
    .tier-panel { margin-top: 22px; }
    .tier-panel[hidden] { display: none; }
    .tier-panel h3 {
        margin: 0;
        color: #152029;
        font-family: Montserrat, sans-serif;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .tier-copy { margin: 8px 0 0; max-width: 62ch; color: #4A5560; font-size: 15px; line-height: 1.6; }
    .tier-discount {
        display: inline-flex;
        margin-top: 12px;
        border-radius: 999px;
        background: #E7F5EC;
        color: #17663A;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 800;
    }
    .tier-qty { margin-top: 18px; }
    .tier-qty label { display: block; color: #152029; font-size: 13px; font-weight: 800; }
    .tier-qty select, .tier-qty input {
        width: min(100%, 220px);
        min-height: 52px;
        margin-top: 8px;
        border: 1px solid #D5DDE5;
        border-radius: 14px;
        background: #fff;
        padding: 0 14px;
        color: #152029;
        font: inherit;
        font-size: 18px;
        font-weight: 800;
    }
    .tier-summary {
        display: grid;
        gap: 8px;
        margin-top: 18px;
        border-radius: 18px;
        background: #F7FAF8;
        padding: 16px 18px;
    }
    .tier-row { display: flex; justify-content: space-between; gap: 12px; color: #4A5560; font-size: 15px; }
    .tier-row strong { color: #152029; }
    .tier-row.is-save strong { color: #17663A; }
    .tier-final {
        margin-top: 6px;
        padding-top: 12px;
        border-top: 1px solid #D7E5DC;
        color: #152029;
        font-family: Montserrat, sans-serif;
        font-size: 22px;
        font-weight: 800;
    }
    .tier-go {
        display: inline-flex;
        width: 100%;
        min-height: 52px;
        align-items: center;
        justify-content: center;
        margin-top: 16px;
        border: 0;
        border-radius: 14px;
        background: #152029;
        color: #fff;
        font: inherit;
        font-size: 16px;
        font-weight: 800;
        cursor: pointer;
    }
    .tier-go:hover { background: #17663A; }
    .tier-box :focus-visible { outline: 2px solid #17663A; outline-offset: 2px; }
    @media (min-width: 760px) {
        .tier-nav { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
</style>

<div class="tier-box" data-tier-box>
    <div class="tier-nav" role="tablist" aria-label="Package options">
        @foreach ($tabs as $tab)
            <button
                type="button"
                class="tier-tab {{ $tab['key'] === $active ? 'is-active' : '' }}"
                role="tab"
                id="tier-tab-{{ $tab['key'] }}"
                aria-selected="{{ $tab['key'] === $active ? 'true' : 'false' }}"
                aria-controls="tier-panel-{{ $tab['key'] }}"
                data-tier-tab="{{ $tab['key'] }}"
            >{{ $tab['label'] }}</button>
        @endforeach
    </div>

    @foreach ($tabs as $tab)
        @php $quote = $tab['quote']; @endphp
        <form
            id="tier-panel-{{ $tab['key'] }}"
            class="tier-panel"
            role="tabpanel"
            aria-labelledby="tier-tab-{{ $tab['key'] }}"
            method="POST"
            action="{{ route('shopping.store') }}"
            data-tier-panel="{{ $tab['key'] }}"
            data-unit="{{ $quote['unit'] ?? '' }}"
            data-discount="{{ $tab['discount'] }}"
            data-custom="{{ $tab['custom'] ? '1' : '0' }}"
            data-starting="{{ $quote['final'] }}"
            @if ($tab['key'] !== $active) hidden @endif
        >
            @csrf
            <input type="hidden" name="package_slug" value="{{ $tab['slug'] }}">
            <input type="hidden" name="order_type" value="package">
            <input type="hidden" name="backlinks" value="{{ $quote['quantity'] }}" data-tier-count>
            <h3>{{ $tab['label'] }} Package</h3>
            <p class="tier-copy">{{ $tab['summary'] }}</p>
            @if ($tab['custom'])
                <p class="tier-discount">Custom pricing</p>
            @else
                <p class="tier-discount">{{ $tab['discount'] }}% package discount</p>
            @endif
            <div class="tier-qty">
                <label for="tier-qty-{{ $tab['key'] }}">Backlinks</label>
                @if ($tab['custom'])
                    <input id="tier-qty-{{ $tab['key'] }}" type="number" min="{{ $tab['min'] }}" max="{{ $tab['max'] }}" value="{{ $tab['min'] }}" data-tier-input>
                @else
                    <select id="tier-qty-{{ $tab['key'] }}" data-tier-input>
                        @for ($count = $tab['min']; $count <= $tab['max']; $count++)
                            <option value="{{ $count }}">{{ $count }}</option>
                        @endfor
                    </select>
                @endif
            </div>
            <div class="tier-summary" aria-live="polite">
                @if ($tab['custom'])
                    <div class="tier-row"><span>Quantity</span><strong data-out="quantity">{{ $quote['quantity'] }}</strong></div>
                    <div class="tier-row"><span>Pricing</span><strong>Custom quotation</strong></div>
                    <div class="tier-row tier-final"><span>Starting price</span><strong data-out="final">${{ number_format($quote['final'], 2) }}</strong></div>
                @else
                    <div class="tier-row"><span>Quantity</span><strong data-out="quantity">{{ $quote['quantity'] }}</strong></div>
                    <div class="tier-row"><span>Price per backlink</span><strong data-out="unit">${{ number_format((float) $quote['unit'], 2) }}</strong></div>
                    <div class="tier-row"><span>Subtotal</span><strong data-out="subtotal">${{ number_format((float) $quote['subtotal'], 2) }}</strong></div>
                    <div class="tier-row is-save"><span>Discount ({{ $tab['discount'] }}%)</span><strong data-out="discount">-${{ number_format((float) $quote['discount_amount'], 2) }}</strong></div>
                    <div class="tier-row tier-final"><span>Final price</span><strong data-out="final">${{ number_format($quote['final'], 2) }}</strong></div>
                @endif
            </div>
            <button class="tier-go" type="submit">Continue to Checkout</button>
        </form>
    @endforeach
</div>

<script>
(function () {
    var box = document.querySelector('[data-tier-box]');
    if (!box) return;
    var money = function (amount) {
        return '$' + Number(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
    var paint = function (panel) {
        var input = panel.querySelector('[data-tier-input]');
        var hidden = panel.querySelector('[data-tier-count]');
        if (!input || !hidden) return;
        var min = Number(input.min || input.options[0].value);
        var max = Number(input.max || input.options[input.options.length - 1].value);
        var quantity = Math.min(max, Math.max(min, Number(input.value) || min));
        input.value = String(quantity);
        hidden.value = String(quantity);
        var quantityNode = panel.querySelector('[data-out="quantity"]');
        if (quantityNode) quantityNode.textContent = String(quantity);
        if (panel.getAttribute('data-custom') === '1') return;
        var unit = Number(panel.getAttribute('data-unit') || 0);
        var discountRate = Number(panel.getAttribute('data-discount') || 0) / 100;
        var subtotal = Math.round(unit * quantity * 100) / 100;
        var discount = Math.round(subtotal * discountRate * 100) / 100;
        var finalPrice = Math.round((subtotal - discount) * 100) / 100;
        panel.querySelector('[data-out="unit"]').textContent = money(unit);
        panel.querySelector('[data-out="subtotal"]').textContent = money(subtotal);
        panel.querySelector('[data-out="discount"]').textContent = '-' + money(discount);
        panel.querySelector('[data-out="final"]').textContent = money(finalPrice);
    };
    box.querySelectorAll('[data-tier-panel]').forEach(function (panel) {
        var input = panel.querySelector('[data-tier-input]');
        if (input) input.addEventListener('input', function () { paint(panel); });
        if (input) input.addEventListener('change', function () { paint(panel); });
        paint(panel);
    });
    box.querySelectorAll('[data-tier-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-tier-tab');
            box.querySelectorAll('[data-tier-tab]').forEach(function (button) {
                var on = button === tab;
                button.classList.toggle('is-active', on);
                button.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            box.querySelectorAll('[data-tier-panel]').forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-tier-panel') !== key;
            });
        });
    });
})();
</script>