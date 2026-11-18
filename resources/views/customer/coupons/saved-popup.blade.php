@php
    $savedCoupon = session('saved_coupon_popup');
@endphp
@if (is_array($savedCoupon) && filled($savedCoupon['code'] ?? null))
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-navy/45 p-4" data-saved-coupon-popup role="dialog" aria-modal="true" aria-labelledby="saved-coupon-popup-title">
        <div class="w-full max-w-md rounded-2xl border border-navy/10 bg-white p-6">
            <h2 id="saved-coupon-popup-title" class="font-display text-2xl font-semibold">Saved for later</h2>
            <p class="mt-2 text-sm leading-6 text-navy/70">You must select this coupon before you make a payment.</p>
            <p class="mt-4 rounded-xl bg-canvas px-4 py-3 text-center font-display text-2xl font-semibold tracking-wide">{{ $savedCoupon['code'] }}</p>
            <p class="mt-3 text-sm leading-6 text-navy/70">{{ $savedCoupon['summary'] }} on a package or an individual backlink. Use it before {{ $savedCoupon['deadline'] }}.</p>
            <button class="btn-navy mt-4 w-full" type="button" onclick="this.closest('[data-saved-coupon-popup]').remove()">OK</button>
        </div>
    </div>
@endif
