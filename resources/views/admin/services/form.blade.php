@extends('layouts.admin')

@section('title', $service->exists ? 'Edit service' : 'Add service')

@section('content')
    @php
        $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-[#F7F8FA] px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/40 focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/30';
    @endphp
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <section class="overflow-hidden rounded-2xl bg-navy text-white shadow-[0_18px_40px_rgba(21,32,41,0.16)]">
            <div class="h-1.5 bg-brand"></div>
            <div class="bg-[radial-gradient(ellipse_at_0%_0%,rgba(255,73,23,0.28),transparent_46%)] p-6 sm:p-8">
                <a href="{{ route('admin.services') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Service Management</a>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $service->exists ? 'Edit service' : 'Add service' }}</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-white/75">Name the placement, describe what the customer receives, and set the starting price and credit cost.</p>
            </div>
        </section>
        <form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" class="space-y-6">
            @csrf
            @if ($service->exists)
                @method('PUT')
            @endif
            <section class="panel overflow-hidden">
                <div class="border-t-[3px] border-t-brand bg-gradient-to-r from-brand/10 to-transparent px-6 py-4 sm:px-8">
                    <p class="text-xs font-semibold tracking-wider text-brand uppercase">Offering</p>
                    <h2 class="mt-1 text-lg font-semibold">Service details</h2>
                </div>
                <div class="space-y-5 p-6 sm:p-8">
                    <div>
                        <label for="service_name" class="text-sm font-medium">Service name</label>
                        <input id="service_name" name="service_name" type="text" value="{{ old('service_name', $service->service_name) }}" required maxlength="150" placeholder="Guest Post" class="{{ $input }}">
                        @error('service_name') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="text-sm font-medium">Description</label>
                        <textarea id="description" name="description" rows="4" placeholder="Publish a new article with a backlink on a publisher website." class="{{ $input }}">{{ old('description', $service->description) }}</textarea>
                        @error('description') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="base_price" class="text-sm font-medium">Base price</label>
                            <input id="base_price" name="base_price" type="number" min="0" step="0.01" value="{{ old('base_price', $service->exists ? $service->base_price : '') }}" required placeholder="120.00" class="{{ $input }}">
                            @error('base_price') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="credit_cost" class="text-sm font-medium">Credit cost</label>
                            <input id="credit_cost" name="credit_cost" type="number" min="0" step="1" value="{{ old('credit_cost', $service->exists ? $service->credit_cost : '') }}" required placeholder="12" class="{{ $input }}">
                            @error('credit_cost') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">{{ $service->exists ? 'Save changes' : 'Add service' }}</button>
                <a href="{{ $service->exists ? route('admin.services.show', $service) : route('admin.services') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
