<section class="grid gap-4 lg:grid-cols-3">
    @foreach ($campaigns as $campaign)
        @php
            $titles = ['first_order' => 'First-order discount', 'loyalty' => 'Loyalty reward', 'comeback' => 'Comeback coupon', 'order_reward' => 'Order reward'];
        @endphp
        <form method="POST" action="{{ route('admin.coupons.campaigns.update', $campaign) }}" class="panel space-y-3 p-5">
            @csrf
            @method('PUT')
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-semibold">{{ $titles[$campaign->type] ?? $campaign->type }}</h2>
                <label class="text-sm font-medium"><input type="hidden" name="is_enabled" value="0"><input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $campaign->is_enabled))> On</label>
            </div>
            <label class="block text-sm">Discount
                <select name="discount_type" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2">
                    <option value="percentage" @selected($campaign->discount_type === 'percentage')>Percentage</option>
                    <option value="fixed" @selected($campaign->discount_type === 'fixed')>Fixed amount</option>
                </select>
            </label>
            <label class="block text-sm">Value
                <input name="discount_value" type="number" min="0.01" step="0.01" value="{{ $campaign->discount_value }}" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2" required>
            </label>
            <label class="block text-sm">Minimum purchase
                <input name="minimum_order_amount" type="number" min="0" step="0.01" value="{{ $campaign->minimum_order_amount }}" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2" required>
            </label>
            @if ($campaign->type === 'first_order')
                <label class="flex items-center gap-2 text-sm"><input type="hidden" name="combines_with_package_discount" value="0"><input type="checkbox" name="combines_with_package_discount" value="1" @checked($campaign->combines_with_package_discount)> Combine with package discounts</label>
            @endif
            @if ($campaign->type === 'loyalty')
                <label class="block text-sm">Orders required
                    <input name="qualifying_orders" type="number" min="1" value="{{ $campaign->qualifying_orders }}" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2">
                </label>
                <label class="flex items-center gap-2 text-sm"><input type="hidden" name="recurring" value="0"><input type="checkbox" name="recurring" value="1" @checked($campaign->recurring)> Repeat at every milestone</label>
            @endif
            @if ($campaign->type === 'comeback')
                <label class="block text-sm">Inactive for
                    <select name="inactivity_days" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2">
                        @foreach ([30, 60, 90] as $days)
                            <option value="{{ $days }}" @selected((int) $campaign->inactivity_days === $days)>{{ $days }} days</option>
                        @endforeach
                    </select>
                </label>
            @endif
            @if ($campaign->type !== 'first_order')
                <label class="block text-sm">{{ $campaign->type === 'order_reward' ? 'Save-for-later deadline (days)' : 'Expires after (days)' }}
                    <input name="expiry_days" type="number" min="1" max="365" value="{{ $campaign->expiry_days }}" class="mt-1 w-full rounded-lg border border-navy/15 px-3 py-2">
                </label>
            @endif
            <button class="btn-navy" type="submit">Save</button>
        </form>
    @endforeach
</section>
@if ($redemptions->isNotEmpty())
    <section class="panel overflow-hidden">
        <div class="border-b border-navy/10 px-5 py-4"><h2 class="font-semibold">First-order redemptions</h2></div>
        <div class="divide-y divide-navy/10">
            @foreach ($redemptions as $redemption)
                <p class="px-5 py-3 text-sm">{{ $redemption->customer?->name }} · {{ $redemption->customer?->email }} · ${{ number_format((float) $redemption->discount_amount, 2) }}</p>
            @endforeach
        </div>
    </section>
@endif
