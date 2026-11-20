@php
    $accountLinks = [
        ['Dashboard', 'customer.dashboard', 'M3 12a9 9 0 1 1 18 0v6H3z M12 12l4-4'],
        ['Wallet', 'customer.wallet', 'M3 6h16v13H3z M19 10h3v5h-7v-5z'],
        ['Invoices', 'customer.invoices', 'M6 3h9l4 4v14H6z M9 12h7m-7 4h7'],
        ['Service orders', 'orders.index', 'M3 8h18v13H3z M7 8l2-5h6l2 5m-5 0v5'],
        ['Available coupons', 'customer.coupons', 'M4 7h16v10H4z M8 7v10 M4 12h4'],
        ['Projects', 'customer.projects', 'M3 6h7l2 2h9v12H3z'],
        ['Addresses', 'customer.addresses', 'M3 10l9-7 9 7v11H3z M9 21v-7h6v7'],
        ['Notifications', 'customer.notifications', 'M5 17h14l-2-3V9a5 5 0 0 0-10 0v5z M10 21h4'],
        ['Payment methods', 'customer.payment-methods', 'M3 5h18v14H3z M3 10h18 M6 15h4'],
        ['Account details', 'customer.profile', 'M3 5h18v14H3z M8 10h2m-3 5h4m3-5h4m-4 5h4'],
        ['Security settings', 'customer.profile', 'M6 10h12v11H6z M8 10V7a4 4 0 0 1 8 0v3 M12 14v3', '#security-settings'],
    ];
@endphp

<div class="mx-auto w-full max-w-[1600px] px-4 pt-4 sm:px-6 lg:px-8">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-600">
        <a class="hover:underline" href="{{ route('customer.dashboard') }}">Home</a>
        <span aria-hidden="true">›</span>
        <span>My account</span>
    </nav>
    @if (request()->routeIs('customer.dashboard'))
        <h1 class="mt-5 text-3xl font-bold text-black">My account</h1>
    @else
        <p class="mt-5 font-display text-3xl font-bold text-black">My account</p>
    @endif

    <nav aria-label="My account" class="mt-4 grid grid-cols-2 gap-x-2 gap-y-1 sm:flex sm:flex-wrap sm:justify-center sm:gap-x-5 lg:mt-8">
        @foreach ($accountLinks as $link)
            @php
                [$label, $routeName, $icon] = $link;
                $fragment = $link[3] ?? '';
                $active = ! $fragment && (request()->routeIs($routeName) || ($routeName === 'orders.index' && request()->routeIs('orders.show', 'orders.payment', 'orders.thanks')));
            @endphp
            <a href="{{ route($routeName).$fragment }}"
                @class(['inline-flex min-h-11 min-w-0 items-center gap-1.5 border-b px-2 py-2 text-sm font-medium text-brand-dark hover:border-brand-dark sm:px-0', 'border-navy' => $active, 'border-transparent' => ! $active])
                @if ($active) aria-current="page" @endif>
                <svg class="size-4 shrink-0 text-navy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}" /></svg>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <form method="POST" action="{{ route('auth.logout') }}" class="mb-5 mt-2 flex justify-end sm:mb-7 sm:justify-center">
        @csrf
        <button type="submit" class="inline-flex min-h-11 items-center gap-1.5 px-2 py-2 text-sm font-medium text-brand-dark hover:underline">
            <svg class="size-4 text-navy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 4H4v16h6m4-12 4 4-4 4m4-4H8" /></svg>
            Logout
        </button>
    </form>
</div>
