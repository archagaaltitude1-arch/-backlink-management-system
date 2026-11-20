@php
    $reward = collect($savedRewards ?? [])
        ->filter(fn ($coupon) => filled($coupon->code ?? null) && ($appliedCode ?? null) !== $coupon->code)
        ->sortByDesc(fn ($coupon) => $coupon->created_at?->getTimestamp() ?? 0)
        ->first();
@endphp
@if ($reward && ! session()->has('saved_coupon_popup'))
    <div class="ck-popup" data-saved-coupon-select role="dialog" aria-modal="true" aria-labelledby="saved-coupon-select-title">
        <div class="ck-popup-card">
            <h2 id="saved-coupon-select-title">Your new coupon</h2>
            <p>{{ $reward->discountSummary() }} on a package or an individual backlink. Apply this coupon before you place the order, or leave it for later.</p>
            <p class="ck-popup-code">{{ $reward->code }}</p>
            <div class="ck-popup-actions">
                <form method="POST" action="{{ $applyUrl }}">
                    @csrf
                    <input type="hidden" name="coupon_code" value="{{ $reward->code }}">
                    <button class="ck-apply" type="submit">Apply</button>
                </form>
                <button class="ck-remove" type="button" onclick="this.closest('[data-saved-coupon-select]').remove()">Not now</button>
            </div>
        </div>
    </div>
@endif
