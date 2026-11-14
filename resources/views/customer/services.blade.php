@extends('layouts.customer')

@section('title', 'Services')

@section('content')
    <section class="panel p-6 sm:p-8">
        <h1 class="text-2xl font-semibold">Guest posting services</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-navy/70">
            Browse publisher sites, compare prices, and choose where to place your backlink.
        </p>
        <a class="btn-primary mt-5" href="{{ route('customer.websites') }}">Browse sites</a>
    </section>
@endsection
