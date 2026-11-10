@extends('layouts.admin')

@section('title', $package->exists ? 'Edit package' : 'Add package')

@section('content')
    @php $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30'; @endphp
    <div class="mx-auto max-w-2xl space-y-6">
        <section>
            <a href="{{ route('admin.packages') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Package Management</a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $package->exists ? 'Edit package' : 'Add package' }}</h1>
            <p class="mt-2 text-sm leading-6 text-navy/70">A price of 0 means the price is not set. A credit amount of 0 means a custom campaign.</p>
        </section>
        <form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @if ($package->exists)
                @method('PUT')
            @endif
            <div>
                <label for="package_name" class="text-sm font-medium">Package name</label>
                <input id="package_name" name="package_name" type="text" value="{{ old('package_name', $package->package_name) }}" required maxlength="150" class="{{ $input }}">
                @error('package_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="price" class="text-sm font-medium">Price</label>
                    <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $package->price) }}" required class="{{ $input }}">
                    @error('price') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="credit_amount" class="text-sm font-medium">Credit amount</label>
                    <input id="credit_amount" name="credit_amount" type="number" min="0" step="1" value="{{ old('credit_amount', $package->credit_amount) }}" required class="{{ $input }}">
                    @error('credit_amount') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $package->exists ? 'Save changes' : 'Add package' }}</button>
                <a href="{{ $package->exists ? route('admin.packages.show', $package) : route('admin.packages') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-navy/70 hover:bg-canvas">Cancel</a>
            </div>
        </form>
    </div>
@endsection
