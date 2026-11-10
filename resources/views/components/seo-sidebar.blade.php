@php
    $icons = [
        'dashboard' => ['M4 4h7v7H4z', 'M13 4h7v7h-7z', 'M4 13h7v7H4z', 'M13 13h7v7h-7z'],
        'assigned' => ['M9 3h6v4H9z', 'M8 5H6v15h12V5h-2', 'M9 12h6', 'M9 16h4'],
        'articles' => ['M7 3h7l5 5v13H7z', 'M14 3v5h5', 'M10 13h6', 'M10 17h6'],
        'submissions' => ['M22 2 11 13', 'M22 2l-7 20-4-9-9-4 20-7'],
        'completed' => ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18', 'M8 12l2.5 2.5L16 9'],
    ];

    $navigation = [
        ['label' => 'Dashboard', 'route' => 'seo.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Assigned Orders', 'route' => 'seo.assigned-orders', 'icon' => 'assigned'],
        ['label' => 'Articles', 'route' => 'seo.articles', 'icon' => 'articles'],
        ['label' => 'Submissions', 'route' => 'seo.submissions', 'icon' => 'submissions'],
        ['label' => 'Completed Orders', 'route' => 'seo.completed-orders', 'icon' => 'completed'],
    ];
@endphp

<aside
    id="app-sidebar"
    class="fixed top-16 bottom-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-navy text-white transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:bottom-auto lg:h-screen lg:shrink-0 lg:translate-x-0 lg:self-start"
>
    <a href="{{ route('seo.dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand font-display text-lg font-bold text-white">B</span>
        <span>
            <span class="block font-display text-sm font-semibold leading-tight">Backlink</span>
            <span class="block text-xs text-white/55">SEO Team</span>
        </span>
    </a>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="SEO team">
        <p class="px-3 pb-2 text-[11px] font-semibold tracking-wider text-white/40 uppercase">Work queue</p>
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
        <div class="flex items-center justify-between">
            <a href="{{ route('seo.profile') }}" class="group block min-w-0">
                <p class="text-xs text-white/50 group-hover:text-brand transition">Signed in as</p>
                <p class="truncate text-sm font-semibold text-white group-hover:text-brand transition">{{ $userName ?? 'SEO User' }}</p>
                <p class="text-[11px] text-white/40">SEO Operations</p>
            </a>
            <form action="{{ route('seo.logout') }}" method="POST">
                @csrf
                <button
                    type="submit"
                    title="Sign Out"
                    class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 text-white/70 hover:bg-brand hover:text-white transition"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
