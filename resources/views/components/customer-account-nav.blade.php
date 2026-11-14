@php
    $storeLinks = [
        'Home' => route('customer.dashboard'),
        'Services' => route('customer.services'),
        'Packages' => route('customer.wallet').'#credit-packages',
        'Niches' => route('customer.websites').'#niche',
        'Sites' => route('customer.websites'),
        'My account' => route('customer.dashboard'),
    ];

    $accountLinks = [
        ['Dashboard', 'customer.dashboard', 'M3 12a9 9 0 1 1 18 0v6H3z M12 12l4-4'],
        ['Wallet', 'customer.wallet', 'M3 6h16v13H3z M19 10h3v5h-7v-5z'],
        ['Invoices', 'customer.invoices', 'M6 3h9l4 4v14H6z M9 12h7m-7 4h7'],
        ['Service orders', 'orders.index', 'M3 8h18v13H3z M7 8l2-5h6l2 5m-5 0v5'],
        ['Projects', 'customer.projects', 'M3 6h7l2 2h9v12H3z'],
        ['Addresses', 'customer.addresses', 'M3 10l9-7 9 7v11H3z M9 21v-7h6v7'],
        ['Notifications', 'customer.notifications', 'M5 17h14l-2-3V9a5 5 0 0 0-10 0v5z M10 21h4'],
        ['Payment methods', 'customer.payment-methods', 'M3 5h18v14H3z M3 10h18 M6 15h4'],
        ['Account details', 'customer.profile', 'M3 5h18v14H3z M8 10h2m-3 5h4m3-5h4m-4 5h4'],
        ['Security settings', 'customer.profile', 'M6 10h12v11H6z M8 10V7a4 4 0 0 1 8 0v3 M12 14v3', '#security-settings'],
    ];
@endphp

<header class="border-b border-slate-200">
    <nav aria-label="Marketplace" class="relative mx-auto flex max-w-[1600px] flex-wrap items-center gap-x-4 gap-y-1 px-4 py-2 text-sm font-medium sm:gap-x-7 sm:px-6 lg:px-8">
        @foreach ($storeLinks as $label => $url)
            <a class="inline-flex min-h-11 items-center hover:text-brand-dark" href="{{ $url }}">{{ $label }}</a>
        @endforeach
        <details class="sm:relative">
            <summary class="cursor-pointer py-3 hover:text-brand-dark">Tools</summary>
            <div class="absolute inset-x-4 z-20 grid min-w-40 gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:right-auto sm:left-0">
                <a class="hover:text-brand-dark" href="{{ route('customer.saved-websites') }}">Saved websites</a>
                <a class="hover:text-brand-dark" href="{{ route('customer.backlinks') }}">Backlinks</a>
                <a class="hover:text-brand-dark" href="{{ route('customer.reports') }}">SEO reports</a>
                <a class="hover:text-brand-dark" href="{{ route('customer.payment-history') }}">Payment history</a>
            </div>
        </details>
        <a class="inline-flex min-h-11 items-center hover:text-brand-dark" href="{{ route('orders.cart') }}">Cart</a>
    </nav>
</header>

<div class="mx-auto w-full max-w-[1600px] px-4 pt-4 sm:px-6 lg:px-8">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-600">
        <a class="hover:underline" href="{{ route('customer.dashboard') }}">Home</a>
        <span aria-hidden="true">›</span>
        <span>My account</span>
    </nav>
    <div class="panel my-5 overflow-hidden">
        <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-3 bg-navy px-4 py-3 text-white sm:px-5">
            <div class="min-w-0">
                @if (request()->routeIs('customer.dashboard'))
                    <h1 class="text-lg font-bold">My account</h1>
                @else
                    <p class="font-display text-lg font-bold">My account</p>
                @endif
                <p class="break-words text-sm text-white/75">{{ auth()->user()->name }}</p>
            </div>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="btn-inverse min-h-11 whitespace-nowrap">
                    <svg class="size-4 shrink-0 text-orange-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M10 4H5v16h5m4-12 4 4-4 4m4-4H9" />
                    </svg>
                    Sign out
                </button>
            </form>
        </div>

        <nav aria-label="My account" class="grid grid-cols-2 gap-2 p-2.5 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($accountLinks as $link)
                @php
                    [$label, $routeName, $icon] = $link;
                    $fragment = $link[3] ?? '';
                    $active = ! $fragment && (request()->routeIs($routeName)
                        || ($routeName === 'orders.index' && request()->routeIs('orders.show', 'orders.payment')));
                @endphp
                <a href="{{ route($routeName).$fragment }}"
                    @class([
                        'flex min-h-11 min-w-0 items-center gap-2 rounded-xl px-3 py-2 text-sm transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand',
                        'bg-orange-50 font-bold text-orange-900 ring-1 ring-brand/35' => $active,
                        'font-medium text-navy hover:bg-canvas' => ! $active,
                    ])
                    @if ($active) aria-current="page" @endif>
                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}" /></svg>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>
</div>
