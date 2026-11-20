@php
    $tabs = $tabs ?? [];
    $active = $tabs[0]['key'] ?? 'starter';
    $looks = [
        'starter' => 'is-slate',
        'professional' => 'is-blue',
        'pro' => 'is-brand',
        'enterprise' => 'is-teal',
    ];
@endphp

<style>
    .tier-cards { display: grid; gap: 12px; }
    .tier-card {
        border: 1px solid #E3E5E8;
        border-radius: 20px;
        background: #fff;
        padding: 16px;
        color: #152029;
        font: inherit;
        text-align: left;
        cursor: pointer;
    }
    .tier-card:hover { border-color: #152029; }
    .tier-card.is-active { border-color: #FF4917; background: #FFF8F6; box-shadow: 0 14px 32px rgba(255, 73, 23, 0.14); }
    .tier-card-top, .tier-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .tier-name { font-size: 15px; font-weight: 800; }
    .tier-best { border-radius: 999px; background: #FF4917; color: #fff; padding: 2px 8px; font-size: 11px; font-weight: 800; }
    .tier-range { display: block; margin-top: 4px; color: #4A5560; font-size: 13px; font-weight: 700; }
    .tier-off { display: inline-flex; margin-top: 12px; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 800; }
    .tier-off.is-slate { background: #F1F3F5; color: #3E4C59; }
    .tier-off.is-blue { background: #E7F0FA; color: #1D4E89; }
    .tier-off.is-brand { background: #FFF1EC; color: #D63A10; }
    .tier-off.is-teal { background: #E5F6F2; color: #0F766E; }
    .tier-card-price { display: block; margin-top: 12px; font-size: 22px; line-height: 1; font-weight: 800; letter-spacing: -0.03em; }
    .tier-live { display: grid; gap: 16px; margin-top: 16px; align-items: stretch; }
    .tier-panel { border: 1px solid #E3E5E8; border-radius: 24px; background: #fff; padding: 22px; }
    .tier-panel[hidden] { display: none; }
    .tier-kicker { margin: 0; color: #C0340D; font-size: 12px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; }
    .tier-panel h3 { margin: 8px 0 0; color: #152029; font-family: var(--font-sans), sans-serif; font-size: 24px; font-weight: 800; letter-spacing: -0.03em; }
    .tier-copy { margin: 8px 0 0; max-width: 48ch; color: #4A5560; font-size: 15px; line-height: 1.6; }
    .tier-qty { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 20px; }
    .tier-qty p { margin: 0; font-size: 14px; font-weight: 800; }
    .tier-stepper { display: flex; align-items: center; gap: 8px; }
    .tier-step, .tier-chip { border: 0; font: inherit; font-weight: 800; cursor: pointer; }
    .tier-step { display: grid; width: 44px; height: 44px; place-items: center; border: 1px solid #E3E5E8; border-radius: 999px; background: #fff; color: #152029; font-size: 20px; }
    .tier-step:hover { border-color: #152029; }
    .tier-step:disabled { opacity: 0.4; cursor: default; }
    .tier-count { min-width: 48px; text-align: center; font-size: 30px; font-weight: 800; font-variant-numeric: tabular-nums; }
    .tier-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    .tier-chip { display: grid; min-width: 44px; height: 44px; place-items: center; border-radius: 12px; background: #F4F7FA; color: #152029; padding: 0 8px; font-size: 14px; }
    .tier-chip.is-on, .tier-chip[aria-pressed="true"] { background: #152029; color: #fff; }
    .tier-range-input { width: 100%; margin-top: 16px; accent-color: #FF4917; }
    .tier-scale { display: flex; justify-content: space-between; margin-top: 8px; color: #4A5560; font-size: 12px; font-weight: 700; }
    .tier-receipt { display: flex; flex-direction: column; border-radius: 24px; background: #152029; color: #fff; padding: 22px; }
    .tier-receipt .tier-kicker { color: #FF8A55; }
    .tier-summary { display: grid; gap: 8px; margin-top: 16px; }
    .tier-row { color: #D5DCE3; font-size: 14px; }
    .tier-row strong { color: #fff; }
    .tier-row.is-save, .tier-row.is-save strong { color: #8DDBA8; }
    .tier-final-label { margin: 20px 0 0; color: #D5DCE3; font-size: 13px; font-weight: 700; }
    .tier-final { margin: 4px 0 0; font-family: var(--font-sans), sans-serif; font-size: 40px; line-height: 1; font-weight: 800; }
    .tier-go { display: inline-flex; width: 100%; min-height: 46px; align-items: center; justify-content: center; margin-top: auto; border: 0; border-radius: 999px; background: #D63A10; color: #fff; font: inherit; font-size: 15px; font-weight: 800; cursor: pointer; }
    .tier-go:hover { background: #FF4917; }
    .tier-box :focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
    @keyframes price-live { from { opacity: 0.35; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    .price-live { animation: price-live 220ms ease; }
    @media (min-width: 720px) { .tier-cards { grid-template-columns: 1fr 1fr; } }
    @media (min-width: 1100px) {
        .tier-cards { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .tier-live { grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr); }
    }
</style>

<div class="tier-box" data-tier-box>
    <div class="tier-cards" role="tablist" aria-label="Package options">
        @foreach ($tabs as $tab)
            @php $quote = $tab['quote']; @endphp
            <button
                type="button"
                class="tier-card {{ $tab['key'] === $active ? 'is-active' : '' }}"
                role="tab"
                id="tier-tab-{{ $tab['key'] }}"
                aria-selected="{{ $tab['key'] === $active ? 'true' : 'false' }}"
                aria-controls="tier-panel-{{ $tab['key'] }}"
                data-tier-tab="{{ $tab['key'] }}"
            >
                <span class="tier-card-top">
                    <span class="tier-name">{{ $tab['label'] }}</span>
                    @if ($tab['key'] === 'pro')
                        <span class="tier-best">Best value</span>
                    @endif
                </span>
                <span class="tier-range">{{ $tab['custom'] ? $tab['min'].'+ links' : $tab['min'].'–'.$tab['max'].' links' }}</span>
                <span class="tier-off {{ $looks[$tab['key']] ?? 'is-slate' }}">{{ $tab['custom'] ? 'Custom quote' : $tab['discount'].'% off' }}</span>
                <strong class="tier-card-price" data-card-price="{{ $tab['key'] }}" data-from="${{ number_format((float) $quote['final'], 2) }}">{{ $tab['key'] === $active ? '' : 'From ' }}${{ number_format((float) $quote['final'], 2) }}</strong>
            </button>
        @endforeach
    </div>

    <div class="tier-live">
        @foreach ($tabs as $tab)
            @php
                $quote = $tab['quote'];
                $span = $tab['max'] - $tab['min'];
            @endphp
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
                <input type="hidden" name="backlinks" value="{{ $tab['min'] }}" data-tier-count>
                <input type="hidden" data-tier-input min="{{ $tab['min'] }}" max="{{ $tab['max'] }}" value="{{ $tab['min'] }}">
                <p class="tier-kicker">Your selection</p>
                <h3>{{ $tab['label'] }} package</h3>
                <p class="tier-copy">{{ $tab['summary'] }}</p>
                <div class="tier-qty">
                    <p>Backlinks</p>
                    <div class="tier-stepper">
                        <button class="tier-step" type="button" data-tier-step="-1" aria-label="Fewer backlinks">−</button>
                        <span class="tier-count" data-out="count">{{ $tab['min'] }}</span>
                        <button class="tier-step" type="button" data-tier-step="1" aria-label="More backlinks">+</button>
                    </div>
                </div>
                @if ($span <= 14)
                    <div class="tier-chips">
                        @for ($count = $tab['min']; $count <= $tab['max']; $count++)
                            <button class="tier-chip {{ $count === $tab['min'] ? 'is-on' : '' }}" type="button" data-tier-chip="{{ $count }}" aria-pressed="{{ $count === $tab['min'] ? 'true' : 'false' }}">{{ $count }}</button>
                        @endfor
                    </div>
                @else
                    <input class="tier-range-input" type="range" min="{{ $tab['min'] }}" max="{{ $tab['max'] }}" value="{{ $tab['min'] }}" data-tier-range>
                    <div class="tier-scale"><span>{{ $tab['min'] }}</span><span>{{ $tab['max'] }}</span></div>
                @endif
            </form>
        @endforeach

        <div class="tier-receipt" aria-live="polite">
            <p class="tier-kicker">Live price</p>
            <div class="tier-summary">
                <div class="tier-row"><span>Quantity</span><strong data-live="quantity">{{ $tabs[0]['min'] ?? 0 }}</strong></div>
                <div class="tier-row" data-live-row="unit"><span>Price per backlink</span><strong data-live="unit"></strong></div>
                <div class="tier-row" data-live-row="subtotal"><span>Subtotal</span><strong data-live="subtotal"></strong></div>
                <div class="tier-row is-save" data-live-row="discount"><span data-live="discount-label">Discount</span><strong data-live="discount"></strong></div>
                <div class="tier-row" data-live-row="custom" hidden><span>Pricing</span><strong>Custom quotation</strong></div>
            </div>
            <p class="tier-final-label" data-live="final-label">Final price</p>
            <p class="tier-final price-live" data-live="final"></p>
            <button class="tier-go" type="button" data-tier-submit>Continue to Checkout</button>
        </div>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('[data-tier-box]').forEach(function (box) {
    if (box.getAttribute('data-tier-ready') === '1') return;
    box.setAttribute('data-tier-ready', '1');
    var money = function (amount) {
        return '$' + Number(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
    var activePanel = function () {
        return box.querySelector('[data-tier-panel]:not([hidden])');
    };
    var paint = function (panel) {
        var input = panel.querySelector('[data-tier-input]');
        var hidden = panel.querySelector('[data-tier-count]');
        if (!input || !hidden) return;
        var min = Number(input.min);
        var max = Number(input.max);
        var quantity = Math.min(max, Math.max(min, Number(input.value) || min));
        input.value = String(quantity);
        hidden.value = String(quantity);
        var count = panel.querySelector('[data-out="count"]');
        if (count) count.textContent = String(quantity);
        panel.querySelectorAll('[data-tier-step]').forEach(function (button) {
            var down = button.getAttribute('data-tier-step') === '-1';
            button.disabled = down ? quantity <= min : quantity >= max;
        });
        panel.querySelectorAll('[data-tier-chip]').forEach(function (chip) {
            var on = Number(chip.getAttribute('data-tier-chip')) === quantity;
            chip.classList.toggle('is-on', on);
            chip.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        var range = panel.querySelector('[data-tier-range]');
        if (range) range.value = String(quantity);
        var custom = panel.getAttribute('data-custom') === '1';
        var finalPrice = Number(panel.getAttribute('data-starting') || 0);
        box.querySelector('[data-live="quantity"]').textContent = String(quantity);
        ['unit', 'subtotal', 'discount'].forEach(function (key) {
            box.querySelector('[data-live-row="' + key + '"]').hidden = custom;
        });
        box.querySelector('[data-live-row="custom"]').hidden = !custom;
        if (!custom) {
            var unit = Number(panel.getAttribute('data-unit') || 0);
            var discountRate = Number(panel.getAttribute('data-discount') || 0) / 100;
            var subtotal = Math.round(unit * quantity * 100) / 100;
            var discount = Math.round(subtotal * discountRate * 100) / 100;
            finalPrice = Math.round((subtotal - discount) * 100) / 100;
            box.querySelector('[data-live="unit"]').textContent = money(unit);
            box.querySelector('[data-live="subtotal"]').textContent = money(subtotal);
            box.querySelector('[data-live="discount-label"]').textContent = 'Discount (' + panel.getAttribute('data-discount') + '%)';
            box.querySelector('[data-live="discount"]').textContent = '-' + money(discount);
        }
        box.querySelector('[data-live="final-label"]').textContent = custom ? 'Starting price' : 'Final price';
        var finalNode = box.querySelector('[data-live="final"]');
        finalNode.textContent = money(finalPrice);
        finalNode.classList.remove('price-live');
        void finalNode.offsetWidth;
        finalNode.classList.add('price-live');
        var key = panel.getAttribute('data-tier-panel');
        box.querySelectorAll('[data-card-price]').forEach(function (node) {
            var from = node.getAttribute('data-from');
            node.textContent = node.getAttribute('data-card-price') === key ? money(finalPrice) : 'From ' + from;
        });
    };
    var show = function (key) {
        box.querySelectorAll('[data-tier-tab]').forEach(function (button) {
            var on = button.getAttribute('data-tier-tab') === key;
            button.classList.toggle('is-active', on);
            button.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        box.querySelectorAll('[data-tier-panel]').forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-tier-panel') !== key;
        });
        var panel = activePanel();
        if (panel) paint(panel);
    };
    box.querySelectorAll('[data-tier-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () { show(tab.getAttribute('data-tier-tab')); });
    });
    box.addEventListener('click', function (event) {
        var panel = activePanel();
        if (!panel) return;
        var input = panel.querySelector('[data-tier-input]');
        var step = event.target.closest('[data-tier-step]');
        var chip = event.target.closest('[data-tier-chip]');
        if (step) {
            input.value = String(Number(input.value) + Number(step.getAttribute('data-tier-step')));
            paint(panel);
        }
        if (chip) {
            input.value = chip.getAttribute('data-tier-chip');
            paint(panel);
        }
    });
    box.querySelectorAll('[data-tier-range]').forEach(function (range) {
        range.addEventListener('input', function () {
            var panel = range.closest('[data-tier-panel]');
            panel.querySelector('[data-tier-input]').value = range.value;
            paint(panel);
        });
    });
    var submit = box.querySelector('[data-tier-submit]');
    if (submit) {
        submit.addEventListener('click', function () {
            var panel = activePanel();
            if (panel) panel.requestSubmit();
        });
    }
    var first = activePanel();
    if (first) paint(first);
    });
})();
</script>
