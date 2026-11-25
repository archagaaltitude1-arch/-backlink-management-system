@extends('layouts.admin')

@section('title', $package->exists ? 'Edit package' : 'Add package')

@section('content')
    @php
        $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-[#F7F8FA] px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/40 focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/30';
    @endphp
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="overflow-hidden rounded-2xl bg-navy text-white shadow-[0_18px_40px_rgba(21,32,41,0.16)]">
            <div class="h-1.5 bg-brand"></div>
            <div class="bg-[radial-gradient(ellipse_at_0%_0%,rgba(255,73,23,0.28),transparent_46%)] p-6 sm:p-8">
                <a href="{{ route('admin.packages') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Package Management</a>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $package->exists ? 'Edit package' : 'Add package' }}</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-white/75">A price of 0 means the price is not set. A credit amount of 0 means a custom campaign.</p>
            </div>
        </section>
        <form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="space-y-6">
            @csrf
            @if ($package->exists)
                @method('PUT')
            @endif
            <section class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-brand bg-gradient-to-r from-brand/10 to-transparent px-6 py-4 sm:px-8">
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Campaign</p>
                    <h2 class="mt-1 text-lg font-semibold">Package details</h2>
                </div>
                <div class="space-y-5 p-6 sm:p-8">
                    <div>
                        <label for="package_name" class="text-sm font-medium">Package name</label>
                        <input id="package_name" name="package_name" type="text" value="{{ old('package_name', $package->package_name) }}" required maxlength="150" placeholder="Professional Package" class="{{ $input }}">
                        @error('package_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="price" class="text-sm font-medium">Price</label>
                            <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $package->exists ? $package->price : '') }}" required placeholder="199.00" class="{{ $input }}">
                            @error('price') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="credit_amount" class="text-sm font-medium">Credit amount</label>
                            <input id="credit_amount" name="credit_amount" type="number" min="0" step="1" value="{{ old('credit_amount', $package->exists ? $package->credit_amount : '') }}" required placeholder="25" class="{{ $input }}">
                            @error('credit_amount') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $package->exists ? 'Save changes' : 'Add package' }}</button>
                <a href="{{ $package->exists ? route('admin.packages.show', $package) : route('admin.packages') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
