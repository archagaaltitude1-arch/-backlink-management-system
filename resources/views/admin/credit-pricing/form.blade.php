@extends('layouts.admin')

@section('title', $pricing->exists ? 'Edit credit price' : 'Add credit price')

@section('content')
    @php $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30'; @endphp
    <div class="mx-auto max-w-2xl space-y-6">
        <section>
            <a href="{{ route('admin.packages') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Package Management</a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $pricing->exists ? 'Edit credit price' : 'Add credit price' }}</h1>
        </section>
        <form method="POST" action="{{ $pricing->exists ? route('admin.credit-pricing.update', $pricing) : route('admin.credit-pricing.store') }}" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @if ($pricing->exists)
                @method('PUT')
            @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="credit_amount" class="text-sm font-medium">Credit amount</label>
                    <input id="credit_amount" name="credit_amount" type="number" min="1" step="1" value="{{ old('credit_amount', $pricing->credit_amount) }}" required class="{{ $input }}">
                    @error('credit_amount') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="price" class="text-sm font-medium">Price</label>
                    <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $pricing->price) }}" required class="{{ $input }}">
                    @error('price') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="status" class="text-sm font-medium">Status</label>
                <select id="status" name="status" required class="{{ $input }}">
                    @foreach (['active', 'inactive'] as $option)
                        <option value="{{ $option }}" @selected(old('status', $pricing->status ?: 'active') === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
                @error('status') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $pricing->exists ? 'Save changes' : 'Add credit price' }}</button>
                <a href="{{ route('admin.packages') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-navy/70 hover:bg-canvas">Cancel</a>
            </div>
        </form>
    </div>
@endsection
