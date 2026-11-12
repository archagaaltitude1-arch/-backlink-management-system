<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Sign in · Backlink Management</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-canvas font-sans text-navy antialiased">
        <main class="grid min-h-screen lg:grid-cols-[minmax(0,1.05fr)_minmax(22rem,0.95fr)]">
            <section class="relative hidden overflow-hidden bg-navy text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-brand"></div>
                <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-brand/20 blur-3xl"></div>
                <div class="pointer-events-none absolute bottom-0 -left-20 h-64 w-64 rounded-full bg-white/5 blur-2xl"></div>

                <div class="relative px-12 pt-12">
                    <div class="flex items-center gap-3">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand font-display text-xl font-bold">B</span>
                        <span>
                            <span class="block font-display text-lg font-semibold leading-tight">Backlink</span>
                            <span class="block text-sm text-white/60">Management</span>
                        </span>
                    </div>
                </div>

                <div class="relative px-12">
                    <p class="text-sm font-medium tracking-wide text-brand uppercase">Admin workspace</p>
                    <h1 class="mt-4 max-w-md font-display text-4xl leading-tight font-semibold">Run websites, services, and packages from one place.</h1>
                    <p class="mt-4 max-w-md text-sm leading-6 text-white/70">Sign in with an active admin account to review the directory, customer accounts, and catalogue.</p>
                    <ul class="mt-8 space-y-3 text-sm text-white/80">
                        <li class="flex items-center gap-3">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-brand/20 text-brand" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12.5 9.5 17 19 7" /></svg>
                            </span>
                            Publisher websites and SEO metrics
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-brand/20 text-brand" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12.5 9.5 17 19 7" /></svg>
                            </span>
                            Services, niches, and packages
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="grid h-6 w-6 place-items-center rounded-full bg-brand/20 text-brand" aria-hidden="true">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12.5 9.5 17 19 7" /></svg>
                            </span>
                            Customer and staff accounts
                        </li>
                    </ul>
                </div>

                <p class="relative px-12 pb-10 text-xs text-white/45">&copy; {{ date('Y') }} Backlink Management System</p>
            </section>

            <section class="flex items-center justify-center px-4 py-10 sm:px-8">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-brand font-display text-lg font-bold text-white">B</span>
                        <span>
                            <span class="block font-display text-base font-semibold leading-tight">Backlink</span>
                            <span class="block text-xs text-navy/55">Management</span>
                        </span>
                    </div>

                    <p class="text-sm font-medium tracking-wide text-brand uppercase">Admin sign in</p>
                    <h2 class="mt-2 font-display text-3xl font-semibold tracking-tight">Welcome back</h2>
                    <p class="mt-2 text-sm leading-6 text-navy/65">Use the email and password for an active admin account.</p>

                    <form method="POST" action="{{ route('login.store') }}" class="panel mt-8 space-y-5 p-6 sm:p-8">
                        @csrf

                        @if ($errors->any())
                            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div>
                            <label for="email" class="text-sm font-medium">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="name@company.com"
                                class="mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-navy/35 focus:border-brand focus:ring-2 focus:ring-brand/30"
                            >
                        </div>

                        <div>
                            <label for="password" class="text-sm font-medium">Password</label>
                            <div class="relative mt-2">
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                    class="w-full rounded-xl border border-navy/15 bg-white px-4 py-3 pr-16 text-sm outline-none transition placeholder:text-navy/35 focus:border-brand focus:ring-2 focus:ring-brand/30"
                                >
                                <button
                                    type="button"
                                    id="toggle-password"
                                    class="absolute inset-y-0 right-0 px-4 text-xs font-semibold text-navy/55 hover:text-navy"
                                    aria-label="Show password"
                                >
                                    Show
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary w-full py-3">Sign in</button>
                        <p class="text-center text-xs leading-5 text-navy/50">Only active admin accounts can open this workspace.</p>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
