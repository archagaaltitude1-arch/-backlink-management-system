@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'rounded-full bg-[#D63A10] text-white hover:bg-[#FF4917] motion-safe:hover:-translate-y-0.5 motion-safe:hover:shadow-[0_10px_24px_rgba(21,32,41,0.14)]',
        'navy' => 'rounded-lg bg-navy text-white hover:bg-[#1d2b36]',
        'outline' => 'rounded-full border-[1.5px] border-navy bg-white text-navy hover:bg-[#F2F2F2] motion-safe:hover:-translate-y-0.5 motion-safe:hover:shadow-[0_10px_24px_rgba(21,32,41,0.14)]',
    ];

    $sizes = [
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-[22px] py-[14px] text-base',
        'lg' => 'px-[22px] py-[14px] text-base',
    ];

    $classes = 'inline-flex min-h-11 items-center justify-center gap-2 font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10] '
        .($variants[$variant] ?? $variants['primary']).' '
        .($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
