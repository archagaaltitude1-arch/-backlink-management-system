<header class="sticky top-0 z-50 flex h-16 items-center justify-between gap-4 border-b border-navy/10 bg-white px-4 shadow-sm sm:px-6">
    <div class="flex min-w-0 items-center gap-3">
        @auth
            <button
                type="button"
                id="sidebar-toggle"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-navy hover:bg-canvas lg:hidden"
                aria-controls="app-sidebar"
                aria-expanded="false"
                aria-label="Open navigation"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        @endauth
        <div class="min-w-0">
            <p class="truncate font-display text-sm font-semibold text-navy">@yield('title', $portal ?? 'Dashboard')</p>
            <p class="truncate text-xs text-navy/55">{{ $portal ?? 'Workspace' }}</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <div class="hidden text-right sm:block">
            <p class="text-sm font-medium text-navy">{{ $userName ?? 'User' }}</p>
            <p class="text-xs text-navy/55">{{ $userRole ?? ($portal ?? 'Member') }}</p>
        </div>
        <span class="grid h-10 w-10 place-items-center rounded-full bg-navy text-sm font-semibold text-white" aria-hidden="true">
            {{ strtoupper(substr($userName ?? 'U', 0, 1)) }}
        </span>
        @auth
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button class="btn rounded-xl text-sm text-navy hover:bg-canvas" type="submit">Sign out</button>
            </form>
        @elseif (! request()->routeIs('auth.login', 'auth.register'))
            <a class="btn rounded-xl text-sm text-navy hover:bg-canvas" href="{{ route('auth.login') }}">Sign in</a>
        @endauth
    </div>
</header>
