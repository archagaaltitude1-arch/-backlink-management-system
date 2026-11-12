{{-- SEO team shell. Pages use @extends('layouts.seo') with @section('title') and @section('content'). --}}
@php
    $portal = 'SEO Team';
    $userName = auth()->user()?->name ?? 'SEO User';
    $userRole = 'SEO Team';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'SEO Team') · Backlink Management</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="bg-canvas font-sans text-navy antialiased">
        <div class="flex min-h-screen">
            <div id="sidebar-backdrop" class="fixed inset-x-0 top-16 bottom-0 z-30 hidden bg-navy/50 lg:hidden"></div>
            @include('components.seo-sidebar')
            <div class="flex min-w-0 flex-1 flex-col">
                @include('components.navbar')
                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
                @include('components.footer')
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
