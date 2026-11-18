@php
    $selectableRewards = collect($savedRewards ?? [])
        ->filter(fn ($reward) => $reward->saved_for_later_at !== null && ($appliedCode ?? null) !== $reward->code)
        ->values();
@endphp
@if ($selectableRewards->isNotEmpty() && ! session()->has('saved_coupon_popup'))
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-navy/45 p-4" data-saved-coupon-select role="dialog" aria-modal="true" aria-labelledby="saved-coupon-select-title">
        <div class="max-h-screen w-full max-w-md overflow-y-auto rounded-2xl border border-navy/10 bg-white p-6">
            <h2 id="saved-coupon-select-title" class="font-display text-2xl font-semibold">Select your saved coupon</h2>
            <p class="mt-2 text-sm leading-6 text-navy/70">{{ $selectableRewards->count() === 1 ? 'You must select this coupon before you make a payment.' : 'You must select one of these coupons before you make a payment.' }}</p>
            <div class="mt-4 flex flex-col gap-3">
                @foreach ($selectableRewards as $reward)
                    <div class="rounded-xl bg-canvas px-4 py-3">
                        <p class="text-center font-display text-xl font-semibold tracking-wide">{{ $reward->code }}</p>
                        <p class="mt-1 text-center text-sm text-navy/70">{{ $reward->discountSummary() }} on a package or an individual backlink. Use it before {{ $reward->expires_at?->format('M j, Y') }}.</p>
                        <form method="POST" action="{{ $applyUrl }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="coupon_code" value="{{ $reward->code }}">
                            <button class="btn-primary w-full" type="submit">Apply</button>
                        </form>
                    </div>
                @endforeach
            </div>
            <button class="btn-navy mt-4 w-full" type="button" onclick="this.closest('[data-saved-coupon-select]').remove()">Not now</button>
        </div>
    </div>
@endif
