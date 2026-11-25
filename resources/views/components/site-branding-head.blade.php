@if (! empty($siteSettings['faviconUrl']))
    <link rel="icon" href="{{ $siteSettings['faviconUrl'] }}" type="image/x-icon">
@endif
@if (! empty($applyTheme) && ! empty($siteSettings['theme']))
    <style id="site-theme">
        :root {
            --color-brand: {{ $siteSettings['theme']['button'] }};
            --color-brand-dark: {{ $siteSettings['theme']['secondary'] }};
            --color-navy: {{ $siteSettings['theme']['primary'] }};
            --site-accent: {{ $siteSettings['theme']['accent'] }};
            --site-bg: {{ $siteSettings['theme']['background'] }};
            --site-text: {{ $siteSettings['theme']['text'] }};
        }
    </style>
@endif
