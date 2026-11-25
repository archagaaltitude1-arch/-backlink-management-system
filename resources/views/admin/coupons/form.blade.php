@extends('layouts.admin')

@section('title', $coupon->exists ? 'Edit coupon' : 'Add coupon')

@section('content')
    @php
        $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-[#F7F8FA] px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/40 focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/30';
        $choice = 'flex items-center gap-2.5 rounded-xl border border-transparent bg-canvas px-3 py-2.5 text-sm transition has-[:checked]:border-brand has-[:checked]:bg-brand/10 has-[:checked]:font-medium';
        $selectedPackages = old('applicable_packages', $coupon->applicable_packages ?: ['all']);
        $selectedPackages = is_array($selectedPackages) ? $selectedPackages : ['all'];
    @endphp
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="overflow-hidden rounded-2xl bg-navy text-white shadow-[0_18px_40px_rgba(21,32,41,0.16)]">
            <div class="h-1.5 bg-brand"></div>
            <div class="bg-[radial-gradient(ellipse_at_0%_0%,rgba(255,73,23,0.28),transparent_46%)] p-6 sm:p-8">
                <a href="{{ route('admin.coupons') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Coupon Management</a>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $coupon->exists ? 'Edit coupon' : 'Add coupon' }}</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-white/75">Codes are stored in uppercase and must be unique. Leave the code blank and tick generate to create one.</p>
            </div>
        </section>
        <form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="space-y-6">
            @csrf
            @if ($coupon->exists)
                @method('PUT')
            @endif
            <section class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-brand bg-gradient-to-r from-brand/10 to-transparent px-6 py-4 sm:px-8">
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Promotion</p>
                    <h2 class="mt-1 text-lg font-semibold">Coupon details</h2>
                </div>
                <div class="space-y-5 p-6 sm:p-8">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="code" class="text-sm font-medium">Coupon code</label>
                            <input id="code" name="code" type="text" value="{{ old('code', $coupon->code) }}" maxlength="40" placeholder="BACKLINK10" class="{{ $input }} uppercase">
                            @error('code') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                        <label class="{{ $choice }} sm:mt-7">
                            <input type="checkbox" name="generate_code" value="1" class="size-4 accent-brand" @checked(old('generate_code'))>
                            Generate a unique code if the field is blank
                        </label>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="discount_type" class="text-sm font-medium">Discount type</label>
                            <select id="discount_type" name="discount_type" class="{{ $input }}">
                                <option value="percentage" @selected(old('discount_type', $coupon->discount_type) === 'percentage')>Percentage</option>
                                <option value="fixed" @selected(old('discount_type', $coupon->discount_type) === 'fixed')>Fixed amount</option>
                            </select>
                        </div>
                        <div>
                            <label for="discount_value" class="text-sm font-medium">Discount value</label>
                            <input id="discount_value" name="discount_value" type="number" min="0.01" step="0.01" value="{{ old('discount_value', $coupon->discount_value) }}" required placeholder="10" class="{{ $input }}">
                            @error('discount_value') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="maximum_discount_amount" class="text-sm font-medium">Maximum discount</label>
                            <input id="maximum_discount_amount" name="maximum_discount_amount" type="number" min="0.01" step="0.01" value="{{ old('maximum_discount_amount', $coupon->maximum_discount_amount) }}" placeholder="25.00" class="{{ $input }}">
                            @error('maximum_discount_amount') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <fieldset>
                        <legend class="text-sm font-medium">Applicable packages</legend>
                        <div class="mt-3 grid gap-2 sm:grid-cols-3 lg:grid-cols-5">
                            @foreach (['all' => 'All', 'starter' => 'Starter', 'pro' => 'Pro', 'professional' => 'Professional', 'enterprise' => 'Enterprise'] as $value => $label)
                                <label class="{{ $choice }}">
                                    <input type="checkbox" name="applicable_packages[]" value="{{ $value }}" class="size-4 accent-brand" @checked(in_array($value, $selectedPackages, true))>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('applicable_packages') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </fieldset>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="purchase_type" class="text-sm font-medium">Purchase type</label>
                            <select id="purchase_type" name="purchase_type" class="{{ $input }}">
                                @foreach (['both' => 'Individual and package', 'package' => 'Package', 'individual' => 'Individual'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('purchase_type', $coupon->purchase_type ?: 'both') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="minimum_order_amount" class="text-sm font-medium">Minimum order amount</label>
                            <input id="minimum_order_amount" name="minimum_order_amount" type="number" min="0" step="0.01" value="{{ old('minimum_order_amount', $coupon->minimum_order_amount ?? 0) }}" required placeholder="50.00" class="{{ $input }}">
                            @error('minimum_order_amount') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="starts_at" class="text-sm font-medium">Start date</label>
                            <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="expires_at" class="text-sm font-medium">Expiry date</label>
                            <input id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
                            @error('expires_at') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="usage_limit" class="text-sm font-medium">Total usage limit</label>
                            <input id="usage_limit" name="usage_limit" type="number" min="1" step="1" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="100" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="per_customer_limit" class="text-sm font-medium">Uses per customer</label>
                            <input id="per_customer_limit" name="per_customer_limit" type="number" min="1" step="1" value="{{ old('per_customer_limit', $coupon->per_customer_limit ?? 1) }}" required placeholder="1" class="{{ $input }}">
                            @error('per_customer_limit') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                        <label class="{{ $choice }} sm:mt-7">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="size-4 accent-brand" @checked(old('is_active', $coupon->exists ? $coupon->is_active : true))>
                            Active
                        </label>
                    </div>
                </div>
            </section>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $coupon->exists ? 'Save changes' : 'Add coupon' }}</button>
                <a href="{{ $coupon->exists ? route('admin.coupons.show', $coupon) : route('admin.coupons') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
