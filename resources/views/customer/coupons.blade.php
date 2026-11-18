@extends('layouts.customer')

@section('title', 'Coupons')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <p class="text-xs font-semibold tracking-wider text-brand uppercase">My account</p>
            <h1 class="mt-2 text-3xl font-semibold">Coupons</h1>
        </div>

        @if (session('success'))
            <p class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('success') }}</p>
        @endif
        @if ($errors->any())
            <p class="rounded-xl bg-red-50 p-4 text-sm text-red-800" role="alert">{{ $errors->first() }}</p>
        @endif

        @if ($firstOrder)
            <section class="panel p-5">
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">First order</p>
                <h2 class="mt-2 text-xl font-semibold">{{ $firstOrder->summary() }} on your first purchase</h2>
                <p class="mt-2 text-sm text-navy/70">This discount is applied automatically at checkout. No code is required. @if ((float) $firstOrder->minimum_order_amount > 0) Minimum purchase ${{ number_format((float) $firstOrder->minimum_order_amount, 2) }}. @endif It can be used once, and only after payment succeeds.</p>
            </section>
        @elseif ($redeemedFirstOrder)
            <section class="panel p-5">
                <h2 class="font-semibold">First-order discount used</h2>
                <p class="mt-2 text-sm text-navy/70">You saved ${{ number_format((float) $redeemedFirstOrder->discount_amount, 2) }} on your first paid order.</p>
            </section>
        @endif

        @foreach (['Available coupons' => $available, 'Used coupons' => $used, 'Expired coupons' => $expired] as $heading => $group)
            <section class="panel overflow-hidden">
                <div class="border-b border-navy/10 px-5 py-4"><h2 class="font-semibold">{{ $heading }}</h2></div>
                <div class="divide-y divide-navy/10">
                    @forelse ($group as $coupon)
                        <article class="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                            <div>
                                <p class="font-semibold">{{ $coupon->code }}</p>
                                <p class="mt-1 text-sm text-navy/70">{{ $coupon->discountSummary() }} · {{ str($coupon->type ?: 'promotional')->headline() }}</p>
                                <p class="mt-1 text-sm text-navy/70">{{ $coupon->packageLabel() }} · Minimum ${{ number_format((float) $coupon->minimum_order_amount, 2) }}</p>
                                <p class="mt-1 text-xs text-navy/55">{{ $coupon->expires_at ? 'Expires '.$coupon->expires_at->format('M j, Y') : 'No expiry date' }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="badge bg-orange-100 text-orange-800">{{ $heading === 'Used coupons' ? 'Used' : ($heading === 'Expired coupons' ? 'Expired' : 'Available') }}</span>
                                @if ($heading === 'Available coupons' && $coupon->type === 'order_reward')
                                    @include('customer.coupons.order-reward', ['coupon' => $coupon, 'applyUrl' => route('customer.coupons.apply', $coupon), 'usesCode' => false, 'compact' => true])
                                @elseif ($heading === 'Available coupons')
                                    <button class="btn-navy" type="button" data-copy-coupon="{{ $coupon->code }}">Copy code</button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="p-6 text-sm text-navy/55">None in this list.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-copy-coupon]').forEach(function (button) {
    button.addEventListener('click', function () {
        var code = button.getAttribute('data-copy-coupon');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code);
        }
        button.textContent = 'Copied';
    });
});
</script>
@endpush
