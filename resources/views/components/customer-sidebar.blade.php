@php
    $icons = [
        'dashboard' => ['M4 4h7v7H4z', 'M13 4h7v7h-7z', 'M4 13h7v7H4z', 'M13 13h7v7h-7z'],
        'profile' => ['M20 21a8 8 0 0 0-16 0', 'M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8'],
        'websites' => ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18', 'M3 12h18', 'M12 3c2.6 2.6 3.8 5.6 3.8 9s-1.2 6.4-3.8 9c-2.6-2.6-3.8-5.6-3.8-9S9.4 5.6 12 3'],
        'services' => ['M8 7V5h8v2', 'M4 7h16v12H4z', 'M4 12h16'],
        'orders' => ['M9 3h6v4H9z', 'M8 5H6v15h12V5h-2', 'M9 12h6', 'M9 16h4'],
    ];

    $navigation = [
        ['label' => 'Dashboard', 'route' => 'customer.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Profile', 'route' => 'customer.profile', 'icon' => 'profile'],
        ['label' => 'Websites', 'route' => 'customer.websites', 'icon' => 'websites'],
        ['label' => 'Services', 'route' => 'customer.services', 'icon' => 'services'],
        ['label' => 'Orders', 'route' => 'customer.orders', 'icon' => 'orders'],
    ];
@endphp

<aside
    id="app-sidebar"
    class="fixed top-16 bottom-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-navy text-white transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:bottom-auto lg:h-screen lg:shrink-0 lg:translate-x-0 lg:self-start"
>
    <a href="{{ route('customer.dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand font-display text-lg font-bold text-white">B</span>
        <span>
            <span class="block font-display text-sm font-semibold leading-tight">Backlink</span>
            <span class="block text-xs text-white/55">Customer</span>
        </span>
    </a>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Customer">
        <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-white/40 uppercase">Account</p>
        @foreach ($navigation as $item)
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand',
                    'bg-brand text-white' => request()->routeIs($item['route']),
                    'text-white/75 hover:bg-white/10 hover:text-white' => ! request()->routeIs($item['route']),
                ])
                @if (request()->routeIs($item['route'])) aria-current="page" @endif
            >
                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    @foreach ($icons[$item['icon']] ?? [] as $path)
                        <path d="{{ $path }}" />
                    @endforeach
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-white/10 px-5 py-4">
        <p class="text-xs text-white/50">Signed in as</p>
        <p class="mt-1 text-sm font-medium">{{ $userName ?? 'Customer User' }}</p>
    </div>
</aside>
