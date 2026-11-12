{{-- Public site shell. Pages use @extends('layouts.public'). No sidebar. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@hasSection('document_title')@yield('document_title')@else @yield('title', 'Home') · BacklinkPro @endif</title>
        @hasSection('meta_description')
            <meta name="description" content="@yield('meta_description')">
        @endif
        @fonts
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:600,700,800|ubuntu:400,500,700&display=swap" rel="stylesheet">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        @stack('head')
    </head>
    <body class="overflow-x-hidden bg-canvas font-sans text-base text-navy antialiased">
        <a
            href="#main"
            class="absolute top-4 left-4 z-[60] -translate-y-24 rounded-xl bg-white px-4 py-2 text-sm font-medium text-navy shadow focus:translate-y-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"
        >
            Skip to content
        </a>
        <div class="flex min-h-screen flex-col">
            @include('components.public-navbar')
            <main id="main" class="flex-1">
                @yield('content')
            </main>
            @include('components.public-footer')
        </div>
        @stack('scripts')
    </body>
</html>
