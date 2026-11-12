@props([
    'name' => 'check',
])

<svg {{ $attributes->merge(['class' => 'h-6 w-6', 'viewBox' => '0 0 24 24', 'fill' => 'none']) }} stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('check')
            <path d="M20 6 9 17l-5-5" />
            @break
        @case('shield')
            <path d="M12 3 5 6v6c0 4.2 2.8 7.2 7 8.5 4.2-1.3 7-4.3 7-8.5V6Z" />
            <path d="m9 12 2 2 4-4" />
            @break
        @case('search')
            <circle cx="11" cy="11" r="6.5" />
            <path d="m16 16 4 4" />
            @break
        @case('chart')
            <path d="M4 19V5" />
            <path d="M4 19h16" />
            <path d="m8 15 3.5-4 2.5 2.5L19 8" />
            @break
        @case('layers')
            <path d="m12 3 9 5-9 5L3 8Z" />
            <path d="m3 13 9 5 9-5" />
            @break
        @case('file')
            <path d="M7 3h7l5 5v13H7Z" />
            <path d="M14 3v5h5" />
            <path d="M9 13h6M9 17h6" />
            @break
        @case('users')
            <path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20" />
            <circle cx="10" cy="8" r="3" />
            <path d="M20 20v-1.5a3.2 3.2 0 0 0-2.4-3.1" />
            <path d="M16.5 5.2a3 3 0 0 1 0 5.6" />
            @break
        @case('filter')
            <path d="M4 5h16l-6 7v6l-4 2v-8Z" />
            @break
        @case('eye')
            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" />
            <circle cx="12" cy="12" r="2.5" />
            @break
        @case('ban')
            <circle cx="12" cy="12" r="8" />
            <path d="m6 6 12 12" />
            @break
        @case('sliders')
            <path d="M4 8h16M4 16h16" />
            <circle cx="9" cy="8" r="2" />
            <circle cx="15" cy="16" r="2" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="8" />
            <path d="M12 8v5l3 2" />
            @break
        @case('globe')
            <circle cx="12" cy="12" r="8" />
            <path d="M2 12h20" />
            <path d="M12 4c2.2 2.4 3.3 5.1 3.3 8S14.2 17.6 12 20c-2.2-2.4-3.3-5.1-3.3-8S9.8 6.4 12 4Z" />
            @break
        @case('link')
            <path d="M10 13a5 5 0 0 0 7.1.1l1.4-1.4a5 5 0 0 0-7.1-7.1L10.2 5.8" />
            <path d="M14 11a5 5 0 0 0-7.1-.1L5.5 12.3a5 5 0 0 0 7.1 7.1l1.2-1.2" />
            @break
        @case('type')
            <path d="M5 6h14" />
            <path d="M12 6v12" />
            <path d="M8 18h8" />
            @break
        @case('article')
            <path d="M6 4h9l3 3v13H6Z" />
            <path d="M15 4v3h3" />
            <path d="M9 11h6M9 15h4" />
            @break
        @case('pen')
            <path d="M4 20h4l10-10-4-4L4 16Z" />
            <path d="m13 7 4 4" />
            @break
        @case('edit')
            <path d="M4 6h10" />
            <path d="M4 12h7" />
            <path d="M4 18h6" />
            <path d="m14 15 6-6-2-2-6 6v2h2Z" />
            @break
        @case('quote')
            <path d="M8 17c-2.2 0-4-1.6-4-4V8h5v5H6.5c0 1.2.8 2 1.5 2Z" />
            <path d="M18 17c-2.2 0-4-1.6-4-4V8h5v5h-2.5c0 1.2.8 2 1.5 2Z" />
            @break
        @default
            <circle cx="12" cy="12" r="8" />
    @endswitch
</svg>
