@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'level' => 'h2',
    'align' => 'left',
    'headingId' => null,
])

@php
    $tag = in_array($level, ['h1', 'h2', 'h3'], true) ? $level : 'h2';
    $headingClass = $tag === 'h1'
        ? 'font-display text-[36px] leading-tight font-extrabold text-navy lg:text-[56px]'
        : 'font-display text-[30px] leading-tight font-extrabold text-navy lg:text-[40px]';
@endphp

<div {{ $attributes->class(['max-w-3xl' => $align !== 'center', 'mx-auto max-w-3xl text-center' => $align === 'center']) }}>
    @if ($eyebrow)
        <p class="text-sm font-bold tracking-[0.08em] text-[#C0340D] uppercase">{{ $eyebrow }}</p>
    @endif
    <{{ $tag }} @if ($headingId) id="{{ $headingId }}" @endif @class([$headingClass, 'mt-3' => filled($eyebrow)])>{{ $title }}</{{ $tag }}>
    @if ($subtitle)
        <p class="mt-4 text-base leading-7 text-[#4A5560]">{{ $subtitle }}</p>
    @endif
</div>
