@extends('layouts.admin')

@section('title', $coupon->code)

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.coupons') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Coupon Management</a>
                <h1 class="mt-2 text-2xl font-semibold">{{ $coupon->code }}</h1>
                <p class="mt-2 text-sm leading-6 text-navy/70">{{ $coupon->discountSummary() }} · {{ $coupon->packageLabel() }} · Minimum ${{ number_format((float) $coupon->minimum_order_amount, 2) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn-primary">Edit</a>
                <form method="POST" action="{{ route('admin.coupons.activate', $coupon) }}">
                    @csrf
                    <button type="submit" class="rounded-xl bg-canvas px-4 py-2.5 text-sm font-medium hover:bg-brand/10">{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}</button>
                </form>
            </div>
        </section>
        @include('admin.niches.partials.alerts')
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Status</p>
                <p class="mt-3 text-lg font-semibold">{{ $coupon->expires_at?->isPast() ? 'Expired' : ($coupon->is_active ? 'Active' : 'Inactive') }}</p>
                <p class="mt-1 text-sm text-navy/60">{{ $coupon->starts_at?->toDayDateTimeString() ?? 'Starts immediately' }} – {{ $coupon->expires_at?->toDayDateTimeString() ?? 'No expiry' }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Redemptions</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $usages->count() }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Discount given</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">${{ number_format($discountTotal, 2) }}</p>
            </article>
        </section>

        <section class="panel p-6">
            <h2 class="text-lg font-semibold">Add to customer dashboard</h2>
            <p class="mt-1 text-sm text-navy/60">Reserve this code before the customer pays. It pops up on the checkout page, and the customer applies it there. Creating a coupon does not notify anyone.</p>
            <form method="POST" action="{{ route('admin.coupons.send', $coupon) }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="audience" class="text-sm font-medium">Recipients</label>
                    <select id="audience" name="audience" class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm sm:max-w-sm">
                        <option value="unpaid">Customers awaiting payment</option>
                        <option value="selected">Selected customers</option>
                        <option value="all">All registered customers</option>
                    </select>
                </div>
                <fieldset class="max-h-64 space-y-2 overflow-y-auto rounded-xl border border-navy/10 p-4">
                    <legend class="px-1 text-sm font-medium">Registered customers</legend>
                    @forelse ($customers as $customer)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="customers[]" value="{{ $customer->id }}">
                            <span>{{ $customer->name }} · {{ $customer->email }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-navy/60">No registered customers yet.</p>
                    @endforelse
                </fieldset>
                @error('customers') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
                <button type="submit" class="btn-primary">Notify customers</button>
            </form>
        </section>

        <section class="panel overflow-hidden">
            <h2 class="border-b border-navy/10 px-5 py-4 text-lg font-semibold sm:px-6">Usage history</h2>
            <div class="overflow-x-auto">
                <table class="min-w-[720px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Customer</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Order</th>
                            <th scope="col" class="px-4 py-3 text-right font-semibold">Discount</th>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Redeemed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($usages as $usage)
                            <tr>
                                <td class="px-5 py-3.5 sm:px-6">{{ $usage->customer?->name }}<br><span class="text-navy/55">{{ $usage->customer?->email }}</span></td>
                                <td class="px-4 py-3.5">{{ $usage->order?->order_number ?? 'Order '.$usage->order_id }}</td>
                                <td class="px-4 py-3.5 text-right tabular-nums">${{ number_format((float) $usage->discount_amount, 2) }}</td>
                                <td class="px-5 py-3.5 sm:px-6">{{ $usage->created_at?->toDayDateTimeString() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-sm text-navy/60 sm:px-6">This coupon has not been redeemed.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel overflow-hidden">
            <h2 class="border-b border-navy/10 px-5 py-4 text-lg font-semibold sm:px-6">Customers who received this coupon</h2>
            <div class="overflow-x-auto">
                <table class="min-w-[640px] w-full text-left text-sm">
                    <thead class="bg-canvas text-xs tracking-wide text-navy/55 uppercase">
                        <tr>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Customer</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Email</th>
                            <th scope="col" class="px-5 py-3 font-semibold sm:px-6">Reserved</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-navy/10">
                        @forelse ($coupon->emailLogs as $log)
                            <tr>
                                <td class="px-5 py-3.5 sm:px-6">{{ $log->customer?->name }}</td>
                                <td class="px-4 py-3.5">{{ $log->email }}</td>
                                <td class="px-5 py-3.5 sm:px-6">{{ $log->sent_at?->toDayDateTimeString() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-sm text-navy/60 sm:px-6">This coupon has not been reserved for a customer.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
