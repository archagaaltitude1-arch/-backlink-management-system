@extends('layouts.admin')

@section('title', $service->service_name)

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.services') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Service Management</a>
                <h1 class="mt-2 text-2xl font-semibold">{{ $service->service_name }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">{{ $service->description ?: 'No description has been added.' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.services.edit', $service) }}" class="btn-primary">Edit</a>
                <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm(@js('Delete '.$service->service_name.'?'))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100">Delete</button>
                </form>
            </div>
        </section>
        @include('admin.niches.partials.alerts')
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Base price</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">${{ number_format((float) $service->base_price, 2) }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Credit cost</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $service->credit_cost }}</p>
            </article>
        </section>
    </div>
@endsection
