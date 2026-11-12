@extends('layouts.admin')

@section('title', $service->exists ? 'Edit service' : 'Add service')

@section('content')
    @php $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30'; @endphp
    <div class="mx-auto max-w-2xl space-y-6">
        <section>
            <a href="{{ route('admin.services') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Service Management</a>
            <h1 class="mt-2 text-2xl font-semibold">{{ $service->exists ? 'Edit service' : 'Add service' }}</h1>
        </section>
        <form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" class="panel space-y-5 p-6 sm:p-8">
            @csrf
            @if ($service->exists)
                @method('PUT')
            @endif
            <div>
                <label for="service_name" class="text-sm font-medium">Service name</label>
                <input id="service_name" name="service_name" type="text" value="{{ old('service_name', $service->service_name) }}" required maxlength="150" class="{{ $input }}">
                @error('service_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="description" class="text-sm font-medium">Description</label>
                <textarea id="description" name="description" rows="4" class="{{ $input }}">{{ old('description', $service->description) }}</textarea>
                @error('description') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="base_price" class="text-sm font-medium">Base price</label>
                    <input id="base_price" name="base_price" type="number" min="0" step="0.01" value="{{ old('base_price', $service->base_price) }}" required class="{{ $input }}">
                    @error('base_price') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="credit_cost" class="text-sm font-medium">Credit cost</label>
                    <input id="credit_cost" name="credit_cost" type="number" min="0" step="1" value="{{ old('credit_cost', $service->credit_cost) }}" required class="{{ $input }}">
                    @error('credit_cost') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $service->exists ? 'Save changes' : 'Add service' }}</button>
                <a href="{{ $service->exists ? route('admin.services.show', $service) : route('admin.services') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-navy/70 hover:bg-canvas">Cancel</a>
            </div>
        </form>
    </div>
@endsection
