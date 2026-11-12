@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-[#D63A10] text-white hover:bg-[#FF4917]',
        'navy' => 'bg-navy text-white hover:bg-[#1d2b36]',
        'outline' => 'border-[1.5px] border-navy bg-white text-navy hover:bg-[#F2F2F2]',
    ];

    $sizes = [
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-[22px] py-[14px] text-base',
        'lg' => 'px-[22px] py-[14px] text-base',
    ];

    $classes = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-xl font-bold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#D63A10] '
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
