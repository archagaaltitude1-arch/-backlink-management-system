@props([
    'title',
    'message' => null,
])

<div {{ $attributes->class('panel px-6 py-12 text-center') }} role="status">
    <svg class="mx-auto h-10 w-10 text-navy/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="12" cy="12" r="9" />
        <path d="M8 12h8" />
    </svg>
    <h3 class="mt-4 font-display text-lg font-bold text-navy">{{ $title }}</h3>
    @if ($message)
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-navy/70">{{ $message }}</p>
    @endif
    @if (trim($slot) !== '')
        <div class="mt-6 flex justify-center">
            {{ $slot }}
        </div>
    @endif
</div>
