<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Staff Login · Backlink Management</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-canvas font-sans text-navy antialiased flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex items-center justify-center gap-3">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-brand font-display text-xl font-bold text-white shadow-md">B</span>
                <div>
                    <span class="block font-display text-xl font-bold leading-tight text-navy">Backlink Management</span>
                    <span class="block text-xs font-semibold tracking-wider text-brand uppercase">SEO Staff Portal · Member 4</span>
                </div>
            </div>
            <h2 class="mt-6 text-center font-display text-2xl font-bold tracking-tight text-navy">
                SEO Staff Sign In
            </h2>
            <p class="mt-2 text-center text-sm text-navy/70">
                Fulfillment, content review & backlink verification workspace
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="panel p-8 sm:p-10">
                @if (session('info'))
                    <div class="mb-5 rounded-xl bg-sky-50 p-4 text-sm font-medium text-sky-800 ring-1 ring-sky-200">
                        {{ session('info') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-xl bg-rose-50 p-4 text-sm font-medium text-rose-800 ring-1 ring-rose-200">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('seo.login.post') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-navy">Email Address</label>
                        <div class="mt-1.5">
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                value="{{ old('email', 'raweesha@example.com') }}"
                                class="block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy placeholder:text-navy/40 shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                                placeholder="staff@example.com"
                            >
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium text-navy">Password</label>
                            <span class="text-xs text-navy/50">Default: password</span>
                        </div>
                        <div class="mt-1.5">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                value="password"
                                class="block w-full rounded-xl border border-navy/20 bg-white px-3.5 py-2.5 text-sm text-navy placeholder:text-navy/40 shadow-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20 transition"
                                placeholder="••••••••"
                            >
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                class="h-4 w-4 rounded border-navy/20 text-brand focus:ring-brand"
                                checked
                            >
                            <span class="text-xs text-navy/70">Remember this device</span>
                        </label>
                    </div>

                    <div>
                        <button type="submit" class="btn-primary w-full shadow-md py-3 text-sm font-semibold">
                            Sign In to SEO Dashboard
                        </button>
                    </div>
                </form>

                <div class="mt-6 border-t border-navy/10 pt-5 text-center">
                    <p class="text-xs text-navy/60">
                        Demo Account: <strong class="text-navy">raweesha@example.com</strong> / <strong class="text-navy">password</strong>
                    </p>
                    <a href="{{ route('seo.dashboard') }}" class="mt-3 inline-block text-xs font-semibold text-brand hover:underline">
                        Or jump directly to preview SEO Dashboard &rarr;
                    </a>
                </div>
            </div>

            <div class="mt-6 text-center text-xs text-navy/50">
                Backlink Management System &copy; {{ date('Y') }} · All rights reserved.
            </div>
        </div>
    </body>
</html>
