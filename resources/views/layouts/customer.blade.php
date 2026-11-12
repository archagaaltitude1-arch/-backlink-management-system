{{-- Customer shell. Pages use @extends('layouts.customer') with @section('title') and @section('content'). --}}
@php
    $portal = 'Customer';
    $userName = auth()->user()?->name ?? 'Guest';
    $userRole = 'Customer';
    $isAccountPage = auth()->check()
        && request()->routeIs('customer.*', 'orders.index', 'orders.show')
        && ! request()->routeIs('customer.websites', 'customer.services');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Customer') · Backlink Management</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body @class(['font-sans text-navy antialiased', 'bg-white' => $isAccountPage, 'bg-canvas' => ! $isAccountPage])>
        @if ($isAccountPage)
            <div class="flex min-h-screen flex-col">
                @include('components.customer-account-nav')
                <main id="account-content" class="mx-auto w-full max-w-[1600px] flex-1 px-4 pb-10 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
                @include('components.footer')
            </div>
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
        @stack('scripts')
    </body>
</html>
