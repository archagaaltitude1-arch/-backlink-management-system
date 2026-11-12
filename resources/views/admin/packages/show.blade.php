@extends('layouts.admin')

@section('title', $package->package_name)

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('admin.packages') }}" class="text-xs font-semibold tracking-wider text-brand uppercase">Package Management</a>
                <h1 class="mt-2 text-2xl font-semibold">{{ $package->package_name }}</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.packages.edit', $package) }}" class="btn-primary">Edit</a>
                <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" onsubmit="return confirm(@js('Delete '.$package->package_name.'?'))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 hover:bg-red-100">Delete</button>
                </form>
            </div>
        </section>
        @include('admin.niches.partials.alerts')
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Price</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ (float) $package->price === 0.0 ? 'Not set' : '$'.number_format((float) $package->price, 2) }}</p>
            </article>
            <article class="panel p-5">
                <p class="text-sm text-navy/60">Credits</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ (int) $package->credit_amount === 0 ? 'Custom' : $package->credit_amount }}</p>
            </article>
        </section>
    </div>
@endsection
