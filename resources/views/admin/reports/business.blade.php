<section class="space-y-4">
    <div>
        <h2 class="text-lg font-semibold">Business statistics</h2>
        <p class="mt-1 text-sm text-navy/60">Orders and revenue for {{ $period->label() }}. Cancelled counts orders stored with that status.</p>
    </div>
    @include('admin.partials.period-filter', ['action' => route('admin.reports'), 'period' => $period])
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach (['today' => 'Today', 'week' => 'This week', 'month' => 'This month'] as $key => $label)
            <article class="panel p-5">
                <p class="text-sm text-navy/60">{{ $label }}</p>
                <p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $windows[$key]['orders'] }}</p>
                <p class="mt-1 text-xs text-navy/55">{{ $windows[$key]['completed'] }} completed · ${{ number_format($windows[$key]['revenue'], 2) }} revenue</p>
            </article>
        @endforeach
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="panel p-5"><p class="text-sm text-navy/60">Total orders</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $business['orders'] }}</p></article>
        <article class="panel p-5"><p class="text-sm text-navy/60">Completed orders</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $business['completed'] }}</p></article>
        <article class="panel p-5"><p class="text-sm text-navy/60">Pending orders</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $business['pending'] }}</p></article>
        <article class="panel p-5"><p class="text-sm text-navy/60">Cancelled orders</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $business['cancelled'] }}</p></article>
        <article class="panel p-5"><p class="text-sm text-navy/60">Total revenue</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">${{ number_format($business['revenue'], 2) }}</p></article>
        <article class="panel p-5"><p class="text-sm text-navy/60">Successful payments</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $paymentStats['successful'] }}</p></article>
        <article class="panel p-5"><p class="text-sm text-navy/60">Pending payments</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $paymentStats['pending'] }}</p></article>
        <article class="panel p-5"><p class="text-sm text-navy/60">Failed payments</p><p class="mt-3 font-display text-3xl font-semibold tabular-nums">{{ $paymentStats['failed'] }}</p></article>
    </div>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="panel p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Monthly revenue</h2>
            <p class="mt-1 text-sm text-navy/60">Successful payments for the last six months.</p>
            <div class="mt-6">@include('admin.partials.bar-chart', ['chart' => $revenueChart])</div>
        </article>
        <article class="panel p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Order statistics</h2>
            <p class="mt-1 text-sm text-navy/60">Order status inside the selected dates. Processing orders: {{ $business['processing'] }}.</p>
            <div class="mt-6">@include('admin.partials.bar-chart', ['chart' => $orderChart])</div>
        </article>
    </div>
</section>
