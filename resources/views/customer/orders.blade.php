@extends('layouts.customer')

@section('title', 'Orders')

@section('content')
    <section class="panel p-6 sm:p-8">
        <p class="text-xs font-semibold tracking-wider text-brand uppercase">Member 2 · Customer orders</p>
        <h1 class="mt-2 text-2xl font-semibold">Orders</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-navy/70">Track your orders and review the publishing details submitted for each service.</p>
        <div class="mt-6 flex flex-wrap gap-3"><a class="btn-primary" href="{{ route('orders.cart') }}">Open cart</a><a class="btn-navy" href="{{ route('orders.index') }}">Order history</a></div>
    </section>
@endsection
