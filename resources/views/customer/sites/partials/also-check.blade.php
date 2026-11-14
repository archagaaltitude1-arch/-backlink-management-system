@php
    $siteOptions = $siteOptions ?? \App\Support\PublicSites::items();
    $icons = [
        'premium-guest' => '⭐',
        'guest-traffic' => '📈',
        'niche-edit' => '🔗',
        'by-country' => '🌐',
        'sidebar' => '📌',
        'favorites' => '❤️',
        'keywords' => '🔍',
    ];
@endphp
@once
    <style>
        .site-also { margin-top: 28px; }
        .site-also-label {
            margin: 0;
            color: #152029;
            font-family: Montserrat, sans-serif;
            font-size: 16px;
            font-weight: 800;
        }
        .site-also-grid {
            display: grid;
            gap: 10px 28px;
            margin: 14px 0 0;
            padding: 0;
            list-style: none;
        }
        .site-also-grid a {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            min-height: 40px;
            color: #C0340D;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.45;
            text-decoration: none;
        }
        .site-also-grid a:hover { text-decoration: underline; }
        .site-also-grid a:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; border-radius: 4px; }
        .site-also-grid a.is-current { color: #152029; text-decoration: underline; }
        .site-also-icon {
            flex-shrink: 0;
            width: 1.35em;
            text-align: center;
            line-height: 1.45;
        }
        @media (min-width: 700px) {
            .site-also-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1000px) {
            .site-also-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
    </style>
@endonce

<div class="site-also">
    <p class="site-also-label">Also check:</p>
    <ul class="site-also-grid">
        @foreach ($siteOptions as $option)
            <li>
                <a
                    href="{{ route('sites.show', $option['slug']) }}"
                    @class(['is-current' => isset($currentSlug) && $currentSlug === $option['slug']])
                    @if (isset($currentSlug) && $currentSlug === $option['slug']) aria-current="page" @endif
                >
                    <span class="site-also-icon" aria-hidden="true">{{ $icons[$option['kind']] ?? '●' }}</span>
                    <span>{{ $option['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
