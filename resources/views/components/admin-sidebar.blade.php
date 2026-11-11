@php
    $icons = [
        'dashboard' => ['M4 4h7v7H4z', 'M13 4h7v7h-7z', 'M4 13h7v7H4z', 'M13 13h7v7h-7z'],
        'customers' => ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2', 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8', 'M22 21v-2a4 4 0 0 0-3-3.87', 'M16 3.13a4 4 0 0 1 0 7.75'],
        'staff' => ['M4 5h16v14H4z', 'M8 12a2 2 0 1 0 0-4 2 2 0 0 0 0 4', 'M12 9h5', 'M12 13h5', 'M8 15h5'],
        'websites' => ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18', 'M3 12h18', 'M12 3c2.6 2.6 3.8 5.6 3.8 9s-1.2 6.4-3.8 9c-2.6-2.6-3.8-5.6-3.8-9S9.4 5.6 12 3'],
        'services' => ['M8 7V5h8v2', 'M4 7h16v12H4z', 'M4 12h16'],
        'niche' => ['M3 12V4h8l9 9-8 8-9-9z', 'M7.5 7.5h.01'],
        'package' => ['M12 3 20 7.5v9L12 21 4 16.5v-9L12 3z', 'M12 12l8-4.5', 'M12 12v9', 'M12 12 4 7.5'],
        'orders' => ['M9 3h6v4H9z', 'M8 5H6v15h12V5h-2', 'M9 12h6', 'M9 16h4'],
        'payments' => ['M3 6h18v12H3z', 'M3 10h18', 'M7 15h5'],
        'reports' => ['M4 19V9', 'M10 19V5', 'M16 19v-6', 'M22 19H2'],
        'performance' => ['M4 19V10', 'M9 19V5', 'M14 19v-7', 'M19 19V8'],
        'settings' => ['M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7', 'M12 2.5v2.5', 'M12 19v2.5', 'M2.5 12H5', 'M19 12h2.5', 'M5.4 5.4l1.8 1.8', 'M16.8 16.8l1.8 1.8', 'M18.6 5.4l-1.8 1.8', 'M7.2 16.8l-1.8 1.8'],
        'roles' => ['M12 3l8 4v5c0 5-3.5 7.5-8 9-4.5-1.5-8-4-8-9V7l8-4z'],
    ];

    $navigation = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Customer Management', 'route' => 'admin.customers', 'icon' => 'customers'],
        ['label' => 'Staff Management', 'route' => 'admin.staff', 'icon' => 'staff'],
        ['label' => 'SEO Staff Performance', 'route' => 'admin.staff-performance', 'icon' => 'performance'],
        ['label' => 'Roles & Permissions', 'route' => 'admin.roles', 'icon' => 'roles'],
        ['label' => 'Website Management', 'route' => 'admin.websites', 'icon' => 'websites'],
        ['label' => 'Service Management', 'route' => 'admin.services', 'icon' => 'services'],
        ['label' => 'Niche Management', 'route' => 'admin.niches', 'icon' => 'niche'],
        ['label' => 'Package Management', 'route' => 'admin.packages', 'icon' => 'package'],
        ['label' => 'Order Management', 'route' => 'admin.orders', 'icon' => 'orders'],
        ['label' => 'Payment Management', 'route' => 'admin.payments', 'icon' => 'payments'],
        ['label' => 'Reports & Analytics', 'route' => 'admin.reports', 'icon' => 'reports'],
        ['label' => 'Settings', 'route' => 'admin.settings', 'icon' => 'settings'],
    ];
@endphp

<aside
    id="app-sidebar"
    class="fixed top-16 bottom-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-navy text-white transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:bottom-auto lg:h-screen lg:shrink-0 lg:translate-x-0 lg:self-start"
>
    <a href="{{ route('admin.dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand font-display text-lg font-bold text-white">B</span>
        <span>
            <span class="block font-display text-sm font-semibold leading-tight">Backlink</span>
            <span class="block text-xs text-white/55">Management</span>
        </span>
    </a>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Admin">
        <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-white/40 uppercase">Menu</p>
        @foreach ($navigation as $item)
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand',
                    'bg-brand text-white' => request()->routeIs($item['route'], $item['route'].'.*'),
                    'text-white/75 hover:bg-white/10 hover:text-white' => ! request()->routeIs($item['route'], $item['route'].'.*'),
                ])
                @if (request()->routeIs($item['route'], $item['route'].'.*')) aria-current="page" @endif
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
        <p class="mt-1 text-sm font-medium">{{ $userName ?? 'Admin User' }}</p>
    </div>
</aside>
