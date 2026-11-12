@props([
    'title' => null,
])

<div {{ $attributes->class('rounded-[20px] border border-[#E3E5E8] bg-white p-7 shadow-sm sm:p-8') }}>
    @if ($title)
        <h3 class="font-display text-xl font-extrabold text-navy">{{ $title }}</h3>
    @endif
    <div @class(['mt-4' => filled($title)])>
        {{ $slot }}
    </div>
</div>
