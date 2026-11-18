<div>
    @unless ($compact ?? false)
        <p class="font-semibold">{{ $coupon->code }}</p>
        <p class="mt-1 text-sm text-navy/70">{{ $coupon->discountSummary() }} on a package or an individual backlink.</p>
    @endunless
    @if ($coupon->saved_for_later_at)
        <p class="mt-1 text-sm text-navy/70">Saved for later. Use it before {{ $coupon->expires_at?->format('M j, Y') }}.</p>
    @elseif (! ($compact ?? false))
        <p class="mt-1 text-sm text-navy/70">Deadline {{ $coupon->expires_at?->format('M j, Y') }}.</p>
    @endif
    @if (($showActions ?? true) && ! $coupon->saved_for_later_at)
        <div class="mt-3 flex flex-wrap gap-2">
            <form method="POST" action="{{ $applyUrl }}">
                @csrf
                @if ($usesCode ?? true)
                    <input type="hidden" name="coupon_code" value="{{ $coupon->code }}">
                @endif
                <button class="btn-primary" type="submit" data-reward-apply>Apply</button>
            </form>
            <form method="POST" action="{{ route('customer.coupons.save', $coupon) }}">
                @csrf
                <button class="btn-navy" type="submit">Save for later</button>
            </form>
        </div>
    @endif
</div>
