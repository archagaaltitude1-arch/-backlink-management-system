@php
    $loyaltyEvery = (int) ($loyalty->qualifying_orders ?? 3);
@endphp
<section class="panel overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-navy/10 px-5 py-4 sm:flex-row sm:items-end sm:justify-between sm:px-6">
        <div>
            <h2 class="font-semibold">Customer coupons</h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-navy/70">Paid orders and unused coupons for each customer. A loyalty coupon is due after every {{ $loyaltyEvery }} paid orders.</p>
        </div>
        <form method="GET" action="{{ route('admin.coupons') }}" class="flex w-full gap-2 sm:w-auto">
            <input type="hidden" name="q" value="{{ $search }}">
            <input type="hidden" name="view" value="{{ $view }}">
            <label class="min-w-0 flex-1 text-sm font-medium sm:w-64">
                <span class="sr-only">Search customers</span>
                <input name="customer" type="search" value="{{ $customerSearch }}" placeholder="Search customer name or email" class="w-full rounded-xl border border-navy/15 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30">
            </label>
            <button type="submit" class="btn-navy">Search</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-[980px] w-full text-left text-sm">
            <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                <tr>
                    <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Customer</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold">Paid orders</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Loyalty</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Pending coupons</th>
                    <th scope="col" class="px-5 py-3 text-right font-semibold sm:px-6">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy/10">
                @forelse ($customers as $customer)
                    @php
                        $standing = app(\App\Support\CustomerDiscounts::class)->loyaltyStanding((int) $customer->paid_orders_count, $customer->coupons, $loyalty);
                        $pending = $customer->coupons->filter(fn ($coupon): bool => $coupon->is_active && (int) $coupon->usages_count === 0 && ($coupon->expires_at === null || $coupon->expires_at->isFuture()));
                    @endphp
                    <tr class="align-top hover:bg-canvas/80">
                        <td class="px-5 py-4 sm:px-6">
                            <p class="font-medium">{{ $customer->name }}</p>
                            <p class="mt-1 text-navy/60">{{ $customer->email }}</p>
                        </td>
                        <td class="px-4 py-4 text-right font-semibold tabular-nums">{{ $standing['paid'] }}</td>
                        <td class="px-4 py-4 text-navy/80">{{ $standing['label'] }}</td>
                        <td class="px-4 py-4">
                            @forelse ($pending as $coupon)
                                <p>{{ $coupon->code }} · {{ $coupon->discountSummary() }} · {{ str($coupon->type ?: 'promotional')->headline() }}@if ($coupon->expires_at) · until {{ $coupon->expires_at->format('M j, Y') }}@endif</p>
                            @empty
                                <p class="text-navy/60">No pending coupons</p>
                            @endforelse
                        </td>
                        <td class="px-5 py-4 text-right sm:px-6">
                            @if ($standing['due'])
                                <form method="POST" action="{{ route('admin.coupons.customers.loyalty', $customer) }}">
                                    @csrf
                                    <button type="submit" class="font-semibold text-brand">Issue coupon</button>
                                </form>
                            @else
                                <span class="text-navy/40">Up to date</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-sm text-navy/60 sm:px-6">No customers with orders or coupons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['records' => $customers])
</section>
