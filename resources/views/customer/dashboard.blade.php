@extends('layouts.customer')

@section('title', 'Customer Dashboard')

@section('content')
    <div class="space-y-4 sm:space-y-6">
        @include('components.customer-service-cards')

        <section class="panel flex flex-col items-start justify-between gap-2 p-4 sm:flex-row sm:items-center sm:gap-4 sm:p-5">
            <div>
                <h2 class="font-semibold">Saved payment card</h2>
                <p class="mt-1 text-sm text-navy/65">
                    {{ $savedCard ? $savedCard->brand.' ending in '.$savedCard->last_four : 'No demo card saved yet.' }}
                </p>
            </div>
            <a class="text-sm font-semibold text-brand-dark hover:underline" href="{{ route('customer.payment-methods') }}">Manage payment methods</a>
        </section>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4"><a href="{{ route('customer.wallet') }}" class="panel min-w-0 p-3 sm:p-5"><p class="text-sm text-navy/55">Available credits</p><p class="mt-2 break-words text-2xl font-semibold tabular-nums sm:text-3xl">{{ number_format($wallet->available_balance) }}</p><p class="mt-2 text-xs text-navy/55">{{ number_format($wallet->reserved_balance) }} reserved</p></a><a href="{{ route('orders.index') }}" class="panel min-w-0 p-3 sm:p-5"><p class="text-sm text-navy/55">Orders</p><p class="mt-2 break-words text-2xl font-semibold tabular-nums sm:text-3xl">{{ $orderCount }}</p><p class="mt-2 text-xs text-navy/55">View order progress</p></a><a href="{{ route('customer.saved-websites') }}" class="panel min-w-0 p-3 sm:p-5"><p class="text-sm text-navy/55">Saved websites</p><p class="mt-2 break-words text-2xl font-semibold tabular-nums sm:text-3xl">{{ $savedWebsiteCount }}</p><p class="mt-2 text-xs text-navy/55">Your marketplace shortlist</p></a><a href="{{ route('customer.notifications') }}" class="panel min-w-0 p-3 sm:p-5"><p class="text-sm text-navy/55">Unread notifications</p><p class="mt-2 break-words text-2xl font-semibold tabular-nums sm:text-3xl">{{ $unreadNotificationCount }}</p><p class="mt-2 text-xs text-navy/55">Order and account updates</p></a></div>
        <div class="grid gap-4 sm:gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]"><section class="panel overflow-hidden"><div class="flex flex-wrap items-center justify-between gap-2 border-b border-navy/10 px-5 py-4"><h2 class="font-semibold">Recent orders</h2><a class="text-sm font-semibold text-brand hover:underline" href="{{ route('orders.index') }}">All orders</a></div><div class="divide-y divide-navy/10">@forelse ($orders as $order)<a href="{{ route('orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 hover:bg-canvas/70"><div class="min-w-0"><p class="break-words font-medium">{{ $order->order_number }}</p><p class="mt-1 break-words text-sm text-navy/70">{{ $order->items->pluck('item_name')->implode(', ') }}</p><p class="mt-1 text-xs text-navy/55">{{ $order->created_at->format('M j, Y') }} · {{ $order->items->sum('quantity') }} item(s)</p></div><div class="sm:text-right"><p class="font-semibold">${{ number_format($order->total_amount, 2) }}</p><p class="mt-1 text-xs text-navy/55">{{ str($order->order_status)->headline() }}</p></div></a>@empty<div class="p-8 text-center text-sm text-navy/55">No orders yet. Browse the marketplace to get started.</div>@endforelse</div></section>
            <section class="panel overflow-hidden"><div class="border-b border-navy/10 px-5 py-4"><h2 class="font-semibold">Project progress</h2></div><div class="divide-y divide-navy/10">@forelse ($projects as $project)<div class="px-5 py-4"><div class="flex flex-wrap items-start justify-between gap-2"><p class="break-words font-medium">{{ $project->name }}</p><span class="badge bg-orange-100 text-orange-800">{{ ucfirst($project->status) }}</span></div><p class="mt-2 text-xs text-navy/55">{{ $project->verification_status === 'verified' ? 'Backlink verified' : 'Verification pending' }}</p></div>@empty<div class="p-8 text-center text-sm text-navy/55">Your projects will appear here after your first order.</div>@endforelse</div></section></div>
    </div>
@endsection
