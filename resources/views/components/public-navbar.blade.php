@php
    $classicBrand = $classicBrand ?? false;
    $links = [
        ['label' => 'Home', 'url' => url('/'), 'match' => '/'],
        ['label' => 'Services', 'url' => url('/services'), 'match' => 'services'],
        ['label' => 'Niches', 'url' => url('/niches'), 'match' => 'niches'],
        ['label' => 'Packages', 'url' => route('packages.index'), 'match' => 'packages'],
        ['label' => 'Sites', 'url' => route('sites.index'), 'match' => 'sites'],
        ['label' => 'Tools', 'url' => route('tools.index'), 'match' => 'tools'],
    ];

    $linkIsCurrent = function (array $link): bool {
        if ($link['match'] === '/') {
            return request()->is('/') || request()->is('home');
        }

        return request()->is($link['match']) || request()->is($link['match'].'/*');
    };

    $serviceIcons = [
        'guest-post' => 'pen',
        'niche-edit' => 'edit',
        'contextual-link' => 'article',
        'text-link' => 'type',
    ];
    $serviceTones = [
        'guest-post' => 'brand',
        'niche-edit' => 'teal',
        'contextual-link' => 'blue',
        'text-link' => 'slate',
    ];
    $serviceMenu = [];

    foreach (config('backlink_services.menu', []) as $item) {
        $serviceMenu[] = [
            'name' => $item['name'],
            'menu' => $item['line'],
            'icon' => $serviceIcons[$item['slug']] ?? 'link',
            'tone' => $serviceTones[$item['slug']] ?? 'brand',
            'url' => url('/services/'.$item['slug']),
        ];
    }

    $servicesCurrent = request()->is('services', 'services/*');
    $nichesCurrent = request()->is('niches', 'niches/*');
    $packagesCurrent = request()->is('packages', 'packages/*');
    $packageMenu = \App\Support\PublicPackages::groups();
    $toolsCurrent = request()->is('tools', 'tools/*');
    $toolMenu = \App\Support\PublicTools::items();
    $sitesCurrent = request()->is('sites', 'sites/*');
    $siteMenu = \App\Support\PublicSites::items();
    $nicheMenu = [
        ['label' => 'Technology', 'query' => 'Technology'],
        ['label' => 'Software and IT', 'query' => 'Software & IT'],
        ['label' => 'Business', 'query' => 'Business'],
        ['label' => 'Finance', 'query' => 'Finance'],
        ['label' => 'Health', 'query' => 'Health'],
        ['label' => 'Education', 'query' => 'Education'],
        ['label' => 'Travel', 'query' => 'Travel'],
        ['label' => 'Fashion and Beauty', 'query' => 'Fashion & Beauty'],
        ['label' => 'Lifestyle', 'query' => 'Lifestyle'],
        ['label' => 'Real Estate', 'query' => 'Real Estate'],
        ['label' => 'E-commerce', 'query' => 'E-commerce'],
        ['label' => 'Automotive', 'query' => 'Automotive'],
        ['label' => 'Sports', 'query' => 'Sports'],
        ['label' => 'Food', 'query' => 'Food'],
        ['label' => 'Agriculture & Farming', 'query' => 'Agriculture & Farming'],
        ['label' => 'Legal', 'query' => 'Legal'],
    ];
    $nicheMarks = [
        'Technology' => ['cpu', 'blue'],
        'Software and IT' => ['code', 'violet'],
        'Business' => ['briefcase', 'navy'],
        'Finance' => ['coins', 'green'],
        'Health' => ['health', 'rose'],
        'Education' => ['book', 'amber'],
        'Travel' => ['plane', 'sky'],
        'Fashion and Beauty' => ['spark', 'rose'],
        'Lifestyle' => ['sun', 'amber'],
        'Real Estate' => ['home', 'teal'],
        'E-commerce' => ['bag', 'violet'],
        'Automotive' => ['car', 'slate'],
        'Sports' => ['trophy', 'green'],
        'Food' => ['utensils', 'brand'],
        'Agriculture & Farming' => ['sprout', 'green'],
        'Legal' => ['scale', 'navy'],
    ];

    foreach ($nicheMenu as $index => $niche) {
        [$nicheMenu[$index]['icon'], $nicheMenu[$index]['tone']] = $nicheMarks[$niche['label']] ?? ['layers', 'navy'];
    }

    $packageMarks = [
        'Text backlink package' => ['type', 'slate'],
        'Contextual backlink package' => ['article', 'blue'],
        'Guest post package' => ['pen', 'brand'],
    ];
    $tierMarks = [
        'Starter' => ['seed', 'slate'],
        'Pro' => ['star', 'brand'],
        'Professional' => ['badge', 'teal'],
        'Enterprise' => ['building', 'navy'],
    ];
    $siteMarks = [
        'premium-guest-posting-sites' => ['crown', 'amber'],
        'link-insertions-niche-edits' => ['edit', 'teal'],
        'guest-posting-organic-traffic' => ['chart', 'green'],
        'top-traffic-by-country' => ['globe', 'sky'],
        'sidebar-footer-blogroll' => ['layout', 'violet'],
        'favorite-websites' => ['heart', 'rose'],
        'organic-keywords-guest-posts' => ['hash', 'blue'],
    ];
    $toolMarks = [
        'website-authority-checker' => ['shield', 'navy'],
        'backlink-checker' => ['link', 'brand'],
        'domain-rating-checker' => ['gauge', 'amber'],
        'domain-authority-checker' => ['award', 'blue'],
        'traffic-checker' => ['chart', 'green'],
        'spam-score-checker' => ['alert', 'rose'],
        'anchor-text-analyzer' => ['anchor', 'violet'],
        'competitor-backlink-analyzer' => ['users', 'teal'],
    ];

    $wishlistCount = 0;

    foreach (session('wishlist', []) as $savedItem) {
        if (is_array($savedItem) && filled($savedItem['domain'] ?? null)) {
            $wishlistCount++;
        }
    }

    $savedHref = Route::has('wishlist.index') ? route('wishlist.index') : (Route::has('customer.saved-websites') ? route('customer.saved-websites') : '#');
    $cartHref = Route::has('shopping.index') ? route('shopping.index') : (Route::has('orders.cart') ? route('orders.cart') : '#');
    $loginHref = Route::has('auth.login') ? route('auth.login') : url('/login');
    $accountHref = Route::has('customer.dashboard') ? route('customer.dashboard') : $loginHref;
    $logoutHref = Route::has('auth.logout') ? route('auth.logout') : null;

    $dashboardHref = null;

    if (auth()->check()) {
        $account = auth()->user();

        if (method_exists($account, 'dashboardUrl')) {
            $dashboardHref = $account->dashboardUrl();
        } elseif (Route::has('customer.dashboard')) {
            $dashboardHref = route('customer.dashboard');
        }
    }

    $cartCount = 0;

    foreach (session('shopping_cart', []) as $cartItem) {
        if (is_array($cartItem) && filled($cartItem['domain'] ?? null)) {
            $cartCount += max(1, (int) ($cartItem['quantity'] ?? 1));
        }
    }
@endphp

<header class="sticky top-0 z-50 border-b border-[#E3E5E8] bg-white">
    <div class="mx-auto flex items-center justify-between gap-3 px-6 py-[14px]">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
            @if (! $classicBrand && ! empty($siteSettings['logoUrl']))
                <img src="{{ $siteSettings['logoUrl'] }}" alt="" class="h-10 w-auto max-w-[250px] object-contain">
            @else
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand text-white" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.1.1l1.4-1.4a5 5 0 0 0-7.1-7.1L10.2 5.8" />
                        <path d="M14 11a5 5 0 0 0-7.1-.1L5.5 12.3a5 5 0 0 0 7.1 7.1l1.2-1.2" />
                    </svg>
                </span>
            @endif
            <span class="font-sans text-[20px] leading-none font-extrabold text-navy">{{ $classicBrand ? 'BacklinkPro' : ($siteSettings['websiteName'] ?? 'BacklinkPro') }}</span>
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary">
            @foreach ($links as $link)
                @if ($link['label'] === 'Services')
                    <div id="services-menu">
                        <button
                            type="button"
                            id="services-toggle"
                            class="{{ $servicesCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} inline-flex min-h-11 items-center gap-1 rounded-lg px-3 text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                            aria-expanded="false"
                            aria-controls="services-panel"
                            @if ($servicesCurrent) aria-current="page" @endif
                        >
                            Services
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div id="services-panel" class="absolute inset-x-0 top-full z-50 hidden border-t-2 border-[#FF4917] bg-white shadow-[0_24px_48px_rgba(21,32,41,0.14)]" hidden>
                            <div class="mx-auto grid max-w-[1180px] items-stretch gap-8 px-6 py-8 lg:grid-cols-[220px_minmax(0,1fr)]">
                                <div class="flex h-full flex-col rounded-2xl bg-[#FFF7F4] p-5">
                                    <p class="eyebrow text-[#C0340D]">Services</p>
                                    <p class="mt-3 font-sans text-[15px] leading-6 text-[#4A5560]">Four ways to place a backlink.</p>
                                    <a href="{{ url('/services') }}" class="mt-auto inline-flex pt-6 font-sans text-[14px] font-bold text-[#C0340D] hover:text-[#152029] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Compare all services →</a>
                                </div>
                                <div class="grid grid-cols-2 gap-1">
                                    @foreach ($serviceMenu as $item)
                                        <a href="{{ $item['url'] }}" class="group flex items-start gap-3 rounded-xl px-3 py-3 hover:bg-[#FFF7F4] focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#D63A10]">
                                            <x-ui.menu-mark :name="$item['icon']" :tone="$item['tone']" />
                                            <span class="min-w-0">
                                                <span class="block font-sans text-[14px] font-semibold leading-5 text-[#152029] group-hover:text-[#C0340D]">{{ $item['name'] }}</span>
                                                <span class="mt-0.5 block font-sans text-[12px] leading-4 text-[#5C6770]">{{ $item['menu'] }}</span>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($link['label'] === 'Niches')
                    <div id="niches-menu">
                        <button
                            type="button"
                            id="niches-toggle"
                            class="{{ $nichesCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} inline-flex min-h-11 items-center gap-1 rounded-lg px-3 text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                            aria-expanded="false"
                            aria-controls="niches-panel"
                            @if ($nichesCurrent) aria-current="page" @endif
                        >
                            Niches
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div id="niches-panel" class="absolute inset-x-0 top-full z-50 hidden border-t-2 border-[#FF4917] bg-white shadow-[0_24px_48px_rgba(21,32,41,0.14)]" hidden>
                            <div class="mx-auto grid max-w-[1180px] items-stretch gap-8 px-6 py-8 lg:grid-cols-[220px_minmax(0,1fr)]">
                                <div class="flex h-full flex-col rounded-2xl bg-[#FFF7F4] p-5">
                                    <p class="eyebrow text-[#C0340D]">Niches</p>
                                    <p class="mt-3 font-sans text-[15px] leading-6 text-[#4A5560]">Browse publisher websites by topic.</p>
                                    <a href="{{ route('niches.index') }}" class="mt-auto inline-flex pt-6 font-sans text-[14px] font-bold text-[#C0340D] hover:text-[#152029] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">View all niches →</a>
                                </div>
                                <div class="grid grid-cols-2 gap-1 xl:grid-cols-4">
                                    @foreach ($nicheMenu as $niche)
                                        @php $nicheHref = route('niches.show', \Illuminate\Support\Str::slug($niche['query'])); @endphp
                                        <a href="{{ $nicheHref }}" @if (request()->url() === $nicheHref) aria-current="page" @endif class="group flex items-center gap-2.5 rounded-xl px-3 py-3 hover:bg-[#FFF7F4] focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#D63A10] {{ request()->url() === $nicheHref ? 'bg-[#FFF7F4]' : '' }}">
                                            <x-ui.menu-mark :name="$niche['icon']" :tone="$niche['tone']" />
                                            <span class="min-w-0 truncate font-sans text-[14px] font-semibold {{ request()->url() === $nicheHref ? 'text-[#C0340D]' : 'text-[#152029] group-hover:text-[#C0340D]' }}">{{ $niche['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($link['label'] === 'Packages')
                    <div id="packages-menu">
                        <button
                            type="button"
                            id="packages-toggle"
                            class="{{ $packagesCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} inline-flex min-h-11 items-center gap-1 rounded-lg px-3 text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                            aria-expanded="false"
                            aria-controls="packages-panel"
                            @if ($packagesCurrent) aria-current="page" @endif
                        >
                            Packages
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div id="packages-panel" class="absolute inset-x-0 top-full z-50 hidden border-t-2 border-[#FF4917] bg-white shadow-[0_24px_48px_rgba(21,32,41,0.14)]" hidden>
                            <div class="mx-auto grid max-w-[1180px] items-stretch gap-8 px-6 py-8 lg:grid-cols-[220px_minmax(0,1fr)]">
                                <a href="{{ route('packages.index') }}#select" class="flex h-full flex-col rounded-2xl bg-[#FFF7F4] p-5 hover:bg-[#FFE8E1] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                                    <p class="eyebrow text-[#C0340D]">Packages</p>
                                    <p class="mt-3 font-sans text-[15px] leading-6 text-[#4A5560]">Campaign sizes for each placement type.</p>
                                    <span class="mt-auto inline-flex pt-6 font-sans text-[14px] font-bold text-[#C0340D]">View all packages →</span>
                                </a>
                                <div class="grid grid-cols-3 gap-6">
                                    @foreach ($packageMenu as $group)
                                        @foreach ($group['sections'] as $section)
                                            @php [$sectionIcon, $sectionTone] = $packageMarks[$section['title']] ?? ['layers', 'navy']; @endphp
                                            <div>
                                                <div class="flex items-center gap-2.5 px-3 pb-2">
                                                    <x-ui.menu-mark :name="$sectionIcon" :tone="$sectionTone" />
                                                    <p class="font-sans text-[13px] font-bold leading-4 text-[#152029]">{{ $section['title'] }}</p>
                                                </div>
                                                @foreach ($section['items'] as $item)
                                                    @php [$tierIcon, $tierTone] = $tierMarks[$item['label']] ?? ['seed', 'slate']; @endphp
                                                    <a href="{{ $item['href'] ?? route('packages.show', $item['slug']) }}" class="group flex items-center gap-2.5 rounded-xl px-3 py-3 hover:bg-[#FFF7F4] focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#D63A10]">
                                                        <x-ui.menu-mark :name="$tierIcon" :tone="$tierTone" />
                                                        <span class="font-sans text-[14px] font-semibold text-[#152029] group-hover:text-[#C0340D]">{{ $item['label'] }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($link['label'] === 'Sites')
                    <div id="sites-menu">
                        <button
                            type="button"
                            id="sites-toggle"
                            class="{{ $sitesCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} inline-flex min-h-11 items-center gap-1 rounded-lg px-3 text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                            aria-expanded="false"
                            aria-controls="sites-panel"
                            @if ($sitesCurrent) aria-current="page" @endif
                        >
                            Sites
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div id="sites-panel" class="absolute inset-x-0 top-full z-50 hidden border-t-2 border-[#FF4917] bg-white shadow-[0_24px_48px_rgba(21,32,41,0.14)]" hidden>
                            <div class="mx-auto grid max-w-[1180px] items-stretch gap-8 px-6 py-8 lg:grid-cols-[220px_minmax(0,1fr)]">
                                <div class="flex h-full flex-col rounded-2xl bg-[#FFF7F4] p-5">
                                    <p class="eyebrow text-[#C0340D]">Sites</p>
                                    <p class="mt-3 font-sans text-[15px] leading-6 text-[#4A5560]">Publisher lists, filtered and ready to order.</p>
                                    <a href="{{ route('sites.index') }}" class="mt-auto inline-flex pt-6 font-sans text-[14px] font-bold text-[#C0340D] hover:text-[#152029] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Browse all sites →</a>
                                </div>
                                <div class="grid grid-cols-2 gap-1">
                                    @foreach ($siteMenu as $site)
                                        @php [$siteIcon, $siteTone] = $siteMarks[$site['slug']] ?? ['globe', 'navy']; @endphp
                                        <a href="{{ route('sites.show', $site['slug']) }}" class="group flex items-center gap-3 rounded-xl px-3 py-3 hover:bg-[#FFF7F4] focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#D63A10]">
                                            <x-ui.menu-mark :name="$siteIcon" :tone="$siteTone" />
                                            <span class="min-w-0 font-sans text-[14px] font-semibold leading-5 text-[#152029] group-hover:text-[#C0340D]">{{ $site['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($link['label'] === 'Tools')
                    <div id="tools-menu">
                        <button
                            type="button"
                            id="tools-toggle"
                            class="{{ $toolsCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} inline-flex min-h-11 items-center gap-1 rounded-lg px-3 text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                            aria-expanded="false"
                            aria-controls="tools-panel"
                            @if ($toolsCurrent) aria-current="page" @endif
                        >
                            Tools
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div id="tools-panel" class="absolute inset-x-0 top-full z-50 hidden border-t-2 border-[#FF4917] bg-white shadow-[0_24px_48px_rgba(21,32,41,0.14)]" hidden>
                            <div class="mx-auto grid max-w-[1180px] items-stretch gap-8 px-6 py-8 lg:grid-cols-[220px_minmax(0,1fr)]">
                                <div class="flex h-full flex-col rounded-2xl bg-[#FFF7F4] p-5">
                                    <p class="eyebrow text-[#C0340D]">SEO tools</p>
                                    <p class="mt-3 font-sans text-[15px] leading-6 text-[#4A5560]">Check a publisher before you buy.</p>
                                    <a href="{{ route('tools.index') }}" class="mt-auto inline-flex pt-6 font-sans text-[14px] font-bold text-[#C0340D] hover:text-[#152029] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Open all tools →</a>
                                </div>
                                <div class="grid grid-cols-2 gap-1">
                                    @foreach ($toolMenu as $tool)
                                        @php [$toolIcon, $toolTone] = $toolMarks[$tool['slug']] ?? ['search', 'navy']; @endphp
                                        <a href="{{ route('tools.show', $tool['slug']) }}" class="group flex items-start gap-3 rounded-xl px-3 py-3 hover:bg-[#FFF7F4] focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#D63A10]">
                                            <x-ui.menu-mark :name="$toolIcon" :tone="$toolTone" />
                                            <span class="min-w-0">
                                                <span class="block font-sans text-[14px] font-semibold leading-5 text-[#152029] group-hover:text-[#C0340D]">{{ $tool['label'] }}</span>
                                                <span class="mt-0.5 block font-sans text-[12px] leading-4 text-[#5C6770]">{{ $tool['summary'] }}</span>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                <a
                    href="{{ $link['url'] }}"
                    @class([
                        'inline-flex min-h-11 items-center rounded-lg px-3 text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]',
                        'bg-[#FFF1EC] text-[#C0340D]' => $linkIsCurrent($link),
                        'text-navy hover:bg-[#F2F2F2]' => ! $linkIsCurrent($link),
                    ])
                    @if ($linkIsCurrent($link)) aria-current="page" @endif
                >
                    {{ $link['label'] }}
                </a>
                @endif
            @endforeach
        </nav>

        <div class="flex items-center gap-1 sm:gap-2">
            <a
                    href="{{ $savedHref }}"
                    class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                    aria-label="Wishlist{{ $wishlistCount > 0 ? ', '.$wishlistCount.' saved' : '' }}"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19.5 12.6 12 20l-7.5-7.4a4.5 4.5 0 0 1 6.4-6.3L12 7.4l1.1-1.1a4.5 4.5 0 0 1 6.4 6.3Z" />
                    </svg>
                    @if ($wishlistCount > 0)
                        <span class="absolute top-0.5 right-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-[#D63A10] px-1 text-[11px] font-bold text-white">{{ $wishlistCount }}</span>
                    @endif
                </a>

                <a
                    href="{{ $cartHref }}"
                    data-cart-open
                    class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                aria-label="Cart, {{ $cartCount }} {{ $cartCount === 1 ? 'item' : 'items' }}"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 7h15l-1.5 9h-12Z" />
                        <path d="M6 7 5 4H2" />
                        <circle cx="9" cy="20" r="1.25" fill="currentColor" stroke="none" />
                        <circle cx="18" cy="20" r="1.25" fill="currentColor" stroke="none" />
                    </svg>
                <span class="absolute top-0.5 right-0.5 grid h-5 min-w-5 place-items-center rounded-full bg-[#D63A10] px-1 text-[11px] font-bold text-white">{{ $cartCount }}</span>
            </a>

            @auth
                <div class="relative hidden lg:block">
                    <button
                        type="button"
                        id="public-account-toggle"
                        class="inline-flex min-h-11 max-w-48 items-center gap-2 rounded-xl px-3 text-[15px] font-bold text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                        aria-haspopup="menu"
                        aria-expanded="false"
                        aria-controls="public-account-menu"
                    >
                        <span class="truncate">{{ auth()->user()->name }}</span>
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                    <div id="public-account-menu" class="absolute right-0 z-50 mt-2 hidden w-52 rounded-xl border border-[#E3E5E8] bg-white p-2 shadow-lg" role="menu" aria-label="Account">
                        @if ($dashboardHref)
                            <a href="{{ $dashboardHref }}" class="block min-h-11 rounded-lg px-3 py-2.5 text-[15px] text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]" role="menuitem">My Dashboard</a>
                        @endif
                        @if ($logoutHref)
                            <form method="POST" action="{{ $logoutHref }}">
                                @csrf
                                <button type="submit" class="block min-h-11 w-full rounded-lg px-3 py-2.5 text-left text-[15px] text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]" role="menuitem">Logout</button>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <div class="hidden items-center gap-2 lg:flex">
                    <a href="{{ $loginHref }}" class="btn-outline">Login</a>
                    <a href="{{ $accountHref }}" class="btn-primary">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.25" />
                            <path d="M5.5 19.25a6.5 6.5 0 0 1 13 0" />
                        </svg>
                        My Account
                    </a>
                </div>
            @endauth

            <button
                type="button"
                id="public-nav-toggle"
                class="inline-flex h-11 w-11 items-center justify-center rounded-xl text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10] lg:hidden"
                aria-controls="public-nav-menu"
                aria-expanded="false"
                aria-label="Open menu"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <div id="public-nav-menu" class="hidden w-full border-t border-[#E3E5E8] bg-white lg:hidden">
        <nav class="mx-auto flex w-full flex-col gap-1 px-6 py-4" aria-label="Mobile">
            @foreach ($links as $link)
                @if ($link['label'] === 'Services')
                    <button
                        type="button"
                        id="mobile-services-toggle"
                        class="{{ $servicesCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} flex min-h-11 w-full items-center justify-between rounded-lg px-3 text-left text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                        aria-expanded="{{ $servicesCurrent ? 'true' : 'false' }}"
                        aria-controls="mobile-services-panel"
                    >
                        Services
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                    </button>
                    <div id="mobile-services-panel" class="{{ $servicesCurrent ? '' : 'hidden' }} flex flex-col gap-1 pl-2" @unless ($servicesCurrent) hidden @endunless>
                        @foreach ($serviceMenu as $item)
                            <a href="{{ $item['url'] }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-[15px] font-medium text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                                <x-ui.menu-mark :name="$item['icon']" :tone="$item['tone']" />
                                <span>
                                    <span class="block font-sans text-[14px] font-semibold">{{ $item['name'] }}</span>
                                    <span class="block font-sans text-[12px] leading-4 text-[#5C6770]">{{ $item['menu'] }}</span>
                                </span>
                            </a>
                        @endforeach
                        <a href="{{ url('/services') }}" class="flex min-h-11 items-center rounded-lg px-3 text-[15px] font-bold text-[#C0340D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Compare all services →</a>
                    </div>
                @elseif ($link['label'] === 'Niches')
                    <button
                        type="button"
                        id="mobile-niches-toggle"
                        class="{{ $nichesCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} flex min-h-11 w-full items-center justify-between rounded-lg px-3 text-left text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                        aria-expanded="{{ $nichesCurrent ? 'true' : 'false' }}"
                        aria-controls="mobile-niches-panel"
                    >
                        Niches
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                    </button>
                    <div id="mobile-niches-panel" class="{{ $nichesCurrent ? '' : 'hidden' }} grid grid-cols-1 gap-1 pl-2 sm:grid-cols-2" @unless ($nichesCurrent) hidden @endunless>
                        @foreach ($nicheMenu as $niche)
                            <a href="{{ route('niches.show', \Illuminate\Support\Str::slug($niche['query'])) }}" class="flex min-h-11 items-center gap-2.5 rounded-lg px-3 text-[15px] font-semibold text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                                <x-ui.menu-mark :name="$niche['icon']" :tone="$niche['tone']" />
                                <span class="truncate">{{ $niche['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @elseif ($link['label'] === 'Packages')
                    <button
                        type="button"
                        id="mobile-packages-toggle"
                        class="{{ $packagesCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} flex min-h-11 w-full items-center justify-between rounded-lg px-3 text-left text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                        aria-expanded="{{ $packagesCurrent ? 'true' : 'false' }}"
                        aria-controls="mobile-packages-panel"
                    >
                        Packages
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                    </button>
                    <div id="mobile-packages-panel" class="{{ $packagesCurrent ? '' : 'hidden' }} flex flex-col pl-2" @unless ($packagesCurrent) hidden @endunless>
                        @foreach ($packageMenu as $group)
                            @foreach ($group['sections'] as $section)
                                @php [$sectionIcon, $sectionTone] = $packageMarks[$section['title']] ?? ['layers', 'navy']; @endphp
                                <p class="flex items-center gap-2.5 px-3 pt-3 font-sans text-[13px] font-bold leading-5 text-[#152029]"><x-ui.menu-mark :name="$sectionIcon" :tone="$sectionTone" />{{ $section['title'] }}</p>
                                @foreach ($section['items'] as $item)
                                    @php [$tierIcon, $tierTone] = $tierMarks[$item['label']] ?? ['seed', 'slate']; @endphp
                                    <a href="{{ $item['href'] ?? route('packages.show', $item['slug']) }}" class="flex min-h-11 items-center gap-2.5 rounded-lg px-3 text-[15px] font-semibold text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                                        <x-ui.menu-mark :name="$tierIcon" :tone="$tierTone" />
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            @endforeach
                        @endforeach
                    </div>
                @elseif ($link['label'] === 'Sites')
                    <button
                        type="button"
                        id="mobile-sites-toggle"
                        class="{{ $sitesCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} flex min-h-11 w-full items-center justify-between rounded-lg px-3 text-left text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                        aria-expanded="{{ $sitesCurrent ? 'true' : 'false' }}"
                        aria-controls="mobile-sites-panel"
                    >
                        Sites
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                    </button>
                    <div id="mobile-sites-panel" class="{{ $sitesCurrent ? '' : 'hidden' }} flex flex-col pl-2" @unless ($sitesCurrent) hidden @endunless>
                        @foreach ($siteMenu as $site)
                            @php [$siteIcon, $siteTone] = $siteMarks[$site['slug']] ?? ['globe', 'navy']; @endphp
                            <a href="{{ route('sites.show', $site['slug']) }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-[15px] leading-5 font-semibold text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                                <x-ui.menu-mark :name="$siteIcon" :tone="$siteTone" />
                                <span>{{ $site['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @elseif ($link['label'] === 'Tools')
                    <button
                        type="button"
                        id="mobile-tools-toggle"
                        class="{{ $toolsCurrent ? 'bg-[#FFF1EC] text-[#C0340D]' : 'text-navy hover:bg-[#F2F2F2]' }} flex min-h-11 w-full items-center justify-between rounded-lg px-3 text-left text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]"
                        aria-expanded="{{ $toolsCurrent ? 'true' : 'false' }}"
                        aria-controls="mobile-tools-panel"
                    >
                        Tools
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                    </button>
                    <div id="mobile-tools-panel" class="{{ $toolsCurrent ? '' : 'hidden' }} flex flex-col pl-2" @unless ($toolsCurrent) hidden @endunless>
                        @foreach ($toolMenu as $tool)
                            @php [$toolIcon, $toolTone] = $toolMarks[$tool['slug']] ?? ['search', 'navy']; @endphp
                            <a href="{{ route('tools.show', $tool['slug']) }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 text-[15px] font-semibold text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">
                                <x-ui.menu-mark :name="$toolIcon" :tone="$toolTone" />
                                <span>
                                    <span class="block font-sans text-[14px] font-semibold">{{ $tool['label'] }}</span>
                                    <span class="block font-sans text-[12px] leading-4 text-[#5C6770]">{{ $tool['summary'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                <a
                    href="{{ $link['url'] }}"
                    @class([
                        'flex min-h-11 items-center rounded-lg px-3 text-[15px] font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]',
                        'bg-[#FFF1EC] text-[#C0340D]' => $linkIsCurrent($link),
                        'text-navy hover:bg-[#F2F2F2]' => ! $linkIsCurrent($link),
                    ])
                    @if ($linkIsCurrent($link)) aria-current="page" @endif
                >
                    {{ $link['label'] }}
                </a>
                @endif
            @endforeach

            <div class="mt-2 flex flex-col gap-2 border-t border-[#E3E5E8] pt-3">
                @auth
                    <p class="px-3 text-sm font-bold text-navy">{{ auth()->user()->name }}</p>
                    @if ($dashboardHref)
                        <a href="{{ $dashboardHref }}" class="flex min-h-11 items-center rounded-lg px-3 text-[15px] font-medium text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">My Dashboard</a>
                    @endif
                    @if ($logoutHref)
                        <form method="POST" action="{{ $logoutHref }}">
                            @csrf
                            <button type="submit" class="flex min-h-11 w-full items-center rounded-lg px-3 text-left text-[15px] font-medium text-navy hover:bg-[#F2F2F2] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10]">Logout</button>
                        </form>
                    @endif
                @else
                    <a href="{{ $loginHref }}" class="btn-outline w-full">Login</a>
                    <a href="{{ $accountHref }}" class="btn-primary w-full">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.25" />
                            <path d="M5.5 19.25a6.5 6.5 0 0 1 13 0" />
                        </svg>
                        My Account
                    </a>
                @endauth
            </div>
        </nav>
    </div>
</header>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.getElementById('public-nav-toggle');
            var menu = document.getElementById('public-nav-menu');
            var accountToggle = document.getElementById('public-account-toggle');
            var accountMenu = document.getElementById('public-account-menu');
            var servicesMenu = document.getElementById('services-menu');
            var servicesToggle = document.getElementById('services-toggle');
            var servicesPanel = document.getElementById('services-panel');
            var mobileServicesToggle = document.getElementById('mobile-services-toggle');
            var mobileServicesPanel = document.getElementById('mobile-services-panel');
            var nichesMenu = document.getElementById('niches-menu');
            var nichesToggle = document.getElementById('niches-toggle');
            var nichesPanel = document.getElementById('niches-panel');
            var mobileNichesToggle = document.getElementById('mobile-niches-toggle');
            var mobileNichesPanel = document.getElementById('mobile-niches-panel');
            var packagesMenu = document.getElementById('packages-menu');
            var packagesToggle = document.getElementById('packages-toggle');
            var packagesPanel = document.getElementById('packages-panel');
            var mobilePackagesToggle = document.getElementById('mobile-packages-toggle');
            var mobilePackagesPanel = document.getElementById('mobile-packages-panel');
            var toolsMenu = document.getElementById('tools-menu');
            var toolsToggle = document.getElementById('tools-toggle');
            var toolsPanel = document.getElementById('tools-panel');
            var mobileToolsToggle = document.getElementById('mobile-tools-toggle');
            var mobileToolsPanel = document.getElementById('mobile-tools-panel');
            var sitesMenu = document.getElementById('sites-menu');
            var sitesToggle = document.getElementById('sites-toggle');
            var sitesPanel = document.getElementById('sites-panel');
            var mobileSitesToggle = document.getElementById('mobile-sites-toggle');
            var mobileSitesPanel = document.getElementById('mobile-sites-panel');

            function setMenu(open) {
                if (!toggle || !menu) {
                    return;
                }

                menu.classList.toggle('hidden', !open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            }

            function setAccount(open) {
                if (!accountToggle || !accountMenu) {
                    return;
                }

                accountMenu.classList.toggle('hidden', !open);
                accountToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            if (toggle && menu) {
                toggle.addEventListener('click', function () {
                    setMenu(menu.classList.contains('hidden'));
                });
            }

            if (accountToggle && accountMenu) {
                accountToggle.addEventListener('click', function (event) {
                    event.stopPropagation();
                    setAccount(accountMenu.classList.contains('hidden'));
                });
            }

            function setServices(open) {
                if (!servicesToggle || !servicesPanel) {
                    return;
                }

                servicesPanel.classList.toggle('hidden', !open);
                servicesPanel.hidden = !open;
                servicesToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                var servicesIcon = servicesToggle.querySelector('svg');
                if (servicesIcon) {
                    servicesIcon.style.transition = 'transform 150ms';
                    servicesIcon.style.transform = open ? 'rotate(180deg)' : '';
                }
            }

            function setMobileServices(open) {
                if (!mobileServicesToggle || !mobileServicesPanel) {
                    return;
                }

                mobileServicesPanel.classList.toggle('hidden', !open);
                mobileServicesPanel.hidden = !open;
                mobileServicesToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function setNiches(open) {
                if (!nichesToggle || !nichesPanel) {
                    return;
                }

                nichesPanel.classList.toggle('hidden', !open);
                nichesPanel.hidden = !open;
                nichesToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                var nichesIcon = nichesToggle.querySelector('svg');
                if (nichesIcon) {
                    nichesIcon.style.transition = 'transform 150ms';
                    nichesIcon.style.transform = open ? 'rotate(180deg)' : '';
                }
            }

            function setMobileNiches(open) {
                if (!mobileNichesToggle || !mobileNichesPanel) {
                    return;
                }

                mobileNichesPanel.classList.toggle('hidden', !open);
                mobileNichesPanel.hidden = !open;
                mobileNichesToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function setPackages(open) {
                if (!packagesToggle || !packagesPanel) {
                    return;
                }

                packagesPanel.classList.toggle('hidden', !open);
                packagesPanel.hidden = !open;
                packagesToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                var packagesIcon = packagesToggle.querySelector('svg');
                if (packagesIcon) {
                    packagesIcon.style.transition = 'transform 150ms';
                    packagesIcon.style.transform = open ? 'rotate(180deg)' : '';
                }
            }

            function setMobilePackages(open) {
                if (!mobilePackagesToggle || !mobilePackagesPanel) {
                    return;
                }

                mobilePackagesPanel.classList.toggle('hidden', !open);
                mobilePackagesPanel.hidden = !open;
                mobilePackagesToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function setTools(open) {
                if (!toolsToggle || !toolsPanel) {
                    return;
                }

                toolsPanel.classList.toggle('hidden', !open);
                toolsPanel.hidden = !open;
                toolsToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                var toolsIcon = toolsToggle.querySelector('svg');
                if (toolsIcon) {
                    toolsIcon.style.transition = 'transform 150ms';
                    toolsIcon.style.transform = open ? 'rotate(180deg)' : '';
                }
            }

            function setMobileTools(open) {
                if (!mobileToolsToggle || !mobileToolsPanel) {
                    return;
                }

                mobileToolsPanel.classList.toggle('hidden', !open);
                mobileToolsPanel.hidden = !open;
                mobileToolsToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function setSites(open) {
                if (!sitesToggle || !sitesPanel) {
                    return;
                }

                sitesPanel.classList.toggle('hidden', !open);
                sitesPanel.hidden = !open;
                sitesToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                var sitesIcon = sitesToggle.querySelector('svg');
                if (sitesIcon) {
                    sitesIcon.style.transition = 'transform 150ms';
                    sitesIcon.style.transform = open ? 'rotate(180deg)' : '';
                }
            }

            function setMobileSites(open) {
                if (!mobileSitesToggle || !mobileSitesPanel) {
                    return;
                }

                mobileSitesPanel.classList.toggle('hidden', !open);
                mobileSitesPanel.hidden = !open;
                mobileSitesToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            var servicesPinned = false;
            var servicesCloseTimer = null;
            var nichesPinned = false;
            var nichesCloseTimer = null;
            var packagesPinned = false;
            var packagesCloseTimer = null;
            var toolsPinned = false;
            var toolsCloseTimer = null;
            var sitesPinned = false;
            var sitesCloseTimer = null;

            if (servicesToggle && servicesPanel && servicesMenu) {
                servicesToggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    servicesPinned = servicesPanel.classList.contains('hidden');
                    setServices(servicesPinned);
                    if (servicesPinned) {
                        nichesPinned = false;
                        setNiches(false);
                        packagesPinned = false;
                        setPackages(false);
                        toolsPinned = false;
                        setTools(false);
                        sitesPinned = false;
                        setSites(false);
                    }
                });
                servicesMenu.addEventListener('mouseenter', function () {
                    clearTimeout(servicesCloseTimer);
                    nichesPinned = false;
                    setNiches(false);
                    packagesPinned = false;
                    setPackages(false);
                    toolsPinned = false;
                    setTools(false);
                    sitesPinned = false;
                    setSites(false);
                    setServices(true);
                });
                servicesMenu.addEventListener('mouseleave', function () {
                    if (servicesPinned) {
                        return;
                    }
                    servicesCloseTimer = setTimeout(function () { setServices(false); }, 250);
                });
                servicesPanel.addEventListener('click', function (event) {
                    if (event.target.closest('a')) {
                        servicesPinned = false;
                    }
                });
            }

            if (mobileServicesToggle && mobileServicesPanel) {
                mobileServicesToggle.addEventListener('click', function () {
                    var open = mobileServicesPanel.classList.contains('hidden');
                    setMobileServices(open);
                    if (open) {
                        setMobileNiches(false);
                        setMobilePackages(false);
                        setMobileTools(false);
                        setMobileSites(false);
                    }
                });
            }

            if (nichesToggle && nichesPanel && nichesMenu) {
                nichesToggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    nichesPinned = nichesPanel.classList.contains('hidden');
                    setNiches(nichesPinned);
                    if (nichesPinned) {
                        servicesPinned = false;
                        setServices(false);
                        packagesPinned = false;
                        setPackages(false);
                        toolsPinned = false;
                        setTools(false);
                        sitesPinned = false;
                        setSites(false);
                    }
                });
                nichesMenu.addEventListener('mouseenter', function () {
                    clearTimeout(nichesCloseTimer);
                    servicesPinned = false;
                    setServices(false);
                    packagesPinned = false;
                    setPackages(false);
                    toolsPinned = false;
                    setTools(false);
                    sitesPinned = false;
                    setSites(false);
                    setNiches(true);
                });
                nichesMenu.addEventListener('mouseleave', function () {
                    if (nichesPinned) {
                        return;
                    }
                    nichesCloseTimer = setTimeout(function () { setNiches(false); }, 250);
                });
                nichesPanel.addEventListener('click', function (event) {
                    if (event.target.closest('a')) {
                        nichesPinned = false;
                    }
                });
            }

            if (mobileNichesToggle && mobileNichesPanel) {
                mobileNichesToggle.addEventListener('click', function () {
                    var open = mobileNichesPanel.classList.contains('hidden');
                    setMobileNiches(open);
                    if (open) {
                        setMobileServices(false);
                        setMobilePackages(false);
                        setMobileTools(false);
                        setMobileSites(false);
                    }
                });
            }

            if (packagesToggle && packagesPanel && packagesMenu) {
                packagesToggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    packagesPinned = packagesPanel.classList.contains('hidden');
                    setPackages(packagesPinned);
                    if (packagesPinned) {
                        servicesPinned = false;
                        setServices(false);
                        nichesPinned = false;
                        setNiches(false);
                        toolsPinned = false;
                        setTools(false);
                        sitesPinned = false;
                        setSites(false);
                    }
                });
                packagesMenu.addEventListener('mouseenter', function () {
                    clearTimeout(packagesCloseTimer);
                    servicesPinned = false;
                    setServices(false);
                    nichesPinned = false;
                    setNiches(false);
                    toolsPinned = false;
                    setTools(false);
                    sitesPinned = false;
                    setSites(false);
                    setPackages(true);
                });
                packagesMenu.addEventListener('mouseleave', function () {
                    if (packagesPinned) {
                        return;
                    }
                    packagesCloseTimer = setTimeout(function () { setPackages(false); }, 250);
                });
                packagesPanel.addEventListener('click', function (event) {
                    if (event.target.closest('a')) {
                        packagesPinned = false;
                    }
                });
            }

            if (mobilePackagesToggle && mobilePackagesPanel) {
                mobilePackagesToggle.addEventListener('click', function () {
                    var open = mobilePackagesPanel.classList.contains('hidden');
                    setMobilePackages(open);
                    if (open) {
                        setMobileServices(false);
                        setMobileNiches(false);
                        setMobileTools(false);
                        setMobileSites(false);
                    }
                });
            }

            if (toolsToggle && toolsPanel && toolsMenu) {
                toolsToggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    toolsPinned = toolsPanel.classList.contains('hidden');
                    setTools(toolsPinned);
                    if (toolsPinned) {
                        servicesPinned = false;
                        setServices(false);
                        nichesPinned = false;
                        setNiches(false);
                        packagesPinned = false;
                        setPackages(false);
                        sitesPinned = false;
                        setSites(false);
                    }
                });
                toolsMenu.addEventListener('mouseenter', function () {
                    clearTimeout(toolsCloseTimer);
                    servicesPinned = false;
                    setServices(false);
                    nichesPinned = false;
                    setNiches(false);
                    packagesPinned = false;
                    setPackages(false);
                    sitesPinned = false;
                    setSites(false);
                    setTools(true);
                });
                toolsMenu.addEventListener('mouseleave', function () {
                    if (toolsPinned) {
                        return;
                    }
                    toolsCloseTimer = setTimeout(function () { setTools(false); }, 250);
                });
                toolsPanel.addEventListener('click', function (event) {
                    if (event.target.closest('a')) {
                        toolsPinned = false;
                    }
                });
            }

            if (mobileToolsToggle && mobileToolsPanel) {
                mobileToolsToggle.addEventListener('click', function () {
                    var open = mobileToolsPanel.classList.contains('hidden');
                    setMobileTools(open);
                    if (open) {
                        setMobileServices(false);
                        setMobileNiches(false);
                        setMobilePackages(false);
                        setMobileSites(false);
                    }
                });
            }

            if (sitesToggle && sitesPanel && sitesMenu) {
                sitesToggle.addEventListener('click', function (event) {
                        event.preventDefault();
                    event.stopPropagation();
                    sitesPinned = sitesPanel.classList.contains('hidden');
                    setSites(sitesPinned);
                    if (sitesPinned) {
                        servicesPinned = false;
                        setServices(false);
                        nichesPinned = false;
                        setNiches(false);
                        packagesPinned = false;
                        setPackages(false);
                        toolsPinned = false;
                        setTools(false);
                    }
                });
                sitesMenu.addEventListener('mouseenter', function () {
                    clearTimeout(sitesCloseTimer);
                    servicesPinned = false;
                    setServices(false);
                    nichesPinned = false;
                    setNiches(false);
                    packagesPinned = false;
                    setPackages(false);
                    toolsPinned = false;
                    setTools(false);
                    setSites(true);
                });
                sitesMenu.addEventListener('mouseleave', function () {
                    if (sitesPinned) {
                        return;
                    }
                    sitesCloseTimer = setTimeout(function () { setSites(false); }, 250);
                });
                sitesPanel.addEventListener('click', function (event) {
                    if (event.target.closest('a')) {
                        sitesPinned = false;
                    }
                });
            }

            if (mobileSitesToggle && mobileSitesPanel) {
                mobileSitesToggle.addEventListener('click', function () {
                    var open = mobileSitesPanel.classList.contains('hidden');
                    setMobileSites(open);
                    if (open) {
                        setMobileServices(false);
                        setMobileNiches(false);
                        setMobilePackages(false);
                        setMobileTools(false);
                    }
                });
            }

            document.addEventListener('click', function (event) {
                if (accountMenu && accountToggle && !accountMenu.contains(event.target) && !accountToggle.contains(event.target)) {
                    setAccount(false);
                }

                if (servicesMenu && !servicesMenu.contains(event.target)) {
                    servicesPinned = false;
                    setServices(false);
                }

                if (nichesMenu && !nichesMenu.contains(event.target)) {
                    nichesPinned = false;
                    setNiches(false);
                }

                if (packagesMenu && !packagesMenu.contains(event.target)) {
                    packagesPinned = false;
                    setPackages(false);
                }

                if (toolsMenu && !toolsMenu.contains(event.target)) {
                    toolsPinned = false;
                    setTools(false);
                }

                if (sitesMenu && !sitesMenu.contains(event.target)) {
                    sitesPinned = false;
                    setSites(false);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setMenu(false);
                    setAccount(false);
                    servicesPinned = false;
                    setServices(false);
                    setMobileServices(false);
                    nichesPinned = false;
                    setNiches(false);
                    setMobileNiches(false);
                    packagesPinned = false;
                    setPackages(false);
                    setMobilePackages(false);
                    toolsPinned = false;
                    setTools(false);
                    setMobileTools(false);
                    sitesPinned = false;
                    setSites(false);
                    setMobileSites(false);
                }
            });
        });
    </script>
@endpush
