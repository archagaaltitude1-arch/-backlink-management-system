{{-- Customer shell. Pages use @extends('layouts.customer') with @section('title') and @section('content'). --}}
@php
    $portal = 'Customer';
    $userName = auth()->user()?->name ?? 'Guest';
    $userRole = 'Customer';
    $isAccountPage = auth()->check()
        && request()->routeIs('customer.*', 'orders.cart', 'orders.checkout', 'orders.index', 'orders.show', 'orders.payment', 'orders.thanks');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Customer') · Backlink Management</title>
        @fonts
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="overflow-x-hidden bg-canvas font-sans text-navy antialiased">
        @if ($isAccountPage)
            <a
                href="#account-content"
                class="absolute top-4 left-4 z-[60] -translate-y-24 rounded-xl bg-white px-4 py-2 text-sm font-medium text-navy shadow focus:translate-y-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"
            >
                Skip to content
            </a>
            <div class="flex min-h-screen flex-col">
                @include('components.public-navbar')
                @include('components.customer-account-nav')
                <main id="account-content" class="mx-auto w-full max-w-[1600px] flex-1 px-4 pb-10 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
                <x-ui.country-browser centered />
                @include('components.public-footer')
            </div>
            @include('components.public-cart-drawer')
        @else
        <div class="flex min-h-screen">
            @auth
                <div id="sidebar-backdrop" class="fixed inset-x-0 top-16 bottom-0 z-30 hidden bg-navy/50 lg:hidden"></div>
                @include('components.customer-sidebar')
            @endauth
            <div class="flex min-w-0 flex-1 flex-col">
                @include('components.navbar')
                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
                @include('components.footer')
            </div>
        </div>
        @endif
        @unless (request()->routeIs('orders.payment'))
            @include('customer.coupons.saved-popup')
        @endunless
        @stack('scripts')
    </body>
</html>
