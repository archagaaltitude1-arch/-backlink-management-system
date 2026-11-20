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
        @case('cpu')
            <rect x="7" y="7" width="10" height="10" rx="1.5" />
            <path d="M9 3v3M12 3v3M15 3v3M9 18v3M12 18v3M15 18v3M3 9h3M3 12h3M3 15h3M18 9h3M18 12h3M18 15h3" />
            @break
        @case('code')
            <path d="m8 8-4 4 4 4M16 8l4 4-4 4M13 6l-2 12" />
            @break
        @case('briefcase')
            <rect x="3" y="7" width="18" height="13" rx="2" />
            <path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7M3 12h18" />
            @break
        @case('coins')
            <ellipse cx="12" cy="7" rx="7" ry="3" />
            <path d="M5 7v5c0 1.7 3.1 3 7 3s7-1.3 7-3V7M5 12v5c0 1.7 3.1 3 7 3s7-1.3 7-3v-5" />
            @break
        @case('heart')
            <path d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2 4 4 0 0 1 7 2c0 4.6-7 9-7 9Z" />
            @break
        @case('book')
            <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5Z" />
            <path d="M4 5.5V21" />
            @break
        @case('plane')
            <path d="m3 12 18-8-6 16-3-6Z" />
            @break
        @case('spark')
            <path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M18.4 5.6l-2.8 2.8M8.4 15.6l-2.8 2.8" />
            @break
        @case('sun')
            <circle cx="12" cy="12" r="3.5" />
            <path d="M12 3v2.5M12 18.5V21M3 12h2.5M18.5 12H21M5.4 5.4l1.8 1.8M16.8 16.8l1.8 1.8M18.6 5.4l-1.8 1.8M7.2 16.8l-1.8 1.8" />
            @break
        @case('home')
            <path d="m4 11 8-7 8 7" />
            <path d="M6 10.5V20h12v-9.5M10 20v-5h4v5" />
            @break
        @case('bag')
            <path d="M6 8h12l-1 12H7Z" />
            <path d="M9 8V6.5a3 3 0 0 1 6 0V8" />
            @break
        @case('car')
            <path d="M4 16h16M6.5 16l1.7-6h7.6l1.7 6" />
            <path d="M6 16v2M18 16v2" />
            <circle cx="8" cy="16" r="1.15" fill="currentColor" stroke="none" />
            <circle cx="16" cy="16" r="1.15" fill="currentColor" stroke="none" />
            @break
        @case('trophy')
            <path d="M8 4h8v5a4 4 0 0 1-8 0Z" />
            <path d="M8 6H5.5A2.5 2.5 0 0 0 8 10.2M16 6h2.5A2.5 2.5 0 0 1 16 10.2M12 13v3M9 20h6" />
            @break
        @case('utensils')
            <path d="M6 3v8a2 2 0 0 0 2 2V3M8 13v8M6 7h4M16 3c1.6 2 2.2 4 2.2 6a2.2 2.2 0 0 1-2.2 2.2V21" />
            @break
        @case('sprout')
            <path d="M12 21V10" />
            <path d="M12 14C8 14 6 12 5 8c4 0 6 2 7 6Z" />
            <path d="M12 12c3-.5 5-2.5 6-6-4 .2-6 2-6 6Z" />
            @break
        @case('scale')
            <path d="M12 4v16M8 20h8M6 7 12 4l6 3" />
            <path d="M6 7 4 12h4ZM18 7l-2 5h4Z" />
            @break
        @case('crown')
            <path d="m4 16 2-8 4 4 2-7 2 7 4-4 2 8Z" />
            <path d="M5 19h14" />
            @break
        @case('layout')
            <rect x="4" y="4" width="16" height="16" rx="2" />
            <path d="M4 9h16M9 9v11" />
            @break
        @case('hash')
            <path d="M9 4 7 20M17 4l-2 16M4 9h16M3 15h16" />
            @break
        @case('gauge')
            <path d="M5 16a7 7 0 1 1 14 0" />
            <path d="m12 16 4-4" />
            <circle cx="12" cy="16" r="1.2" fill="currentColor" stroke="none" />
            @break
        @case('award')
            <circle cx="12" cy="9" r="5" />
            <path d="m9 13.5-1 6.5 4-2 4 2-1-6.5" />
            @break
        @case('alert')
            <path d="M12 4 3 19h18Z" />
            <path d="M12 10v4M12 17h.01" />
            @break
        @case('star')
            <path d="m12 3 2.4 5.2L20 9.2l-4 3.8.9 5.5L12 16l-4.9 2.5.9-5.5-4-3.8 5.6-1Z" />
            @break
        @case('badge')
            <circle cx="12" cy="12" r="7" />
            <path d="m9 12 2 2 4-4" />
            @break
        @case('building')
            <path d="M4 20h16M6 20V6h8v14M14 10h4v10M8.5 9h3M8.5 12.5h3M8.5 16h3" />
            @break
        @case('seed')
            <circle cx="12" cy="12" r="3" />
            @break
        @default
            <circle cx="12" cy="12" r="8" />
    @endswitch
</svg>
