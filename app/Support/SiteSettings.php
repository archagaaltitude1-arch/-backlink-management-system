<?php

namespace App\Support;

use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSettings
{
    public function __construct(private HtmlSanitizer $sanitizer) {}

    public function record(): GlobalSetting
    {
        return GlobalSetting::query()->first() ?? GlobalSetting::query()->create(GlobalSettingDefaults::attributes());
    }

    public function forget(): void
    {
        Cache::forget('global-settings.public.v1');
    }

    /**
     * @return array<string, mixed>
     */
    public function publicPayload(): array
    {
        return Cache::remember(
            'global-settings.public.v1',
            3600,
            fn (): array => $this->present($this->record()),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function present(GlobalSetting $settings): array
    {
        $version = $settings->updated_at?->getTimestamp() ?? time();
        $theme = [
            'primary' => $this->hex($settings->primary_colour, '#152029'),
            'secondary' => $this->hex($settings->secondary_colour, '#D63A10'),
            'accent' => $this->hex($settings->accent_colour, '#FF4917'),
            'background' => $this->hex($settings->background_colour, '#FFFFFF'),
            'text' => $this->hex($settings->text_colour, '#152029'),
            'button' => $this->hex($settings->button_colour, '#FF4917'),
        ];
        $position = in_array($settings->wallpaper_position, GlobalSettingDefaults::wallpaperPositions(), true)
            ? $settings->wallpaper_position
            : 'center';
        $cssPosition = $position === 'top' ? 'center top' : 'center center';
        $wallpaperUrl = $this->assetUrl($settings->wallpaper_path, $version);

        return [
            'companyName' => (string) $settings->company_name,
            'websiteName' => (string) $settings->website_name,
            'websiteUrl' => (string) $settings->website_url,
            'companyEmail' => (string) $settings->company_email,
            'supportEmail' => (string) $settings->support_email,
            'phoneNumber' => (string) $settings->phone_number,
            'address' => (string) $settings->company_address,
            'city' => (string) $settings->city,
            'state' => (string) $settings->state,
            'postalCode' => (string) $settings->postal_code,
            'country' => (string) $settings->country,
            'currency' => (string) $settings->currency,
            'timezone' => (string) $settings->timezone,
            'dateFormat' => (string) $settings->date_format,
            'language' => (string) $settings->default_language,
            'copyright' => (string) $settings->copyright_text,
            'logoUrl' => $this->assetUrl($settings->logo_path, $version),
            'wallpaperUrl' => $wallpaperUrl,
            'wallpaperPosition' => $position,
            'wallpaperCssPosition' => $cssPosition,
            'faviconUrl' => $this->assetUrl($settings->favicon_path, $version),
            'footerCopyright' => (string) $settings->footer_copyright,
            'footerDescription' => (string) $settings->footer_description,
            'footerHtml' => $this->sanitizer->sanitize($settings->footer_html),
            'footerLinks' => $this->links(is_array($settings->footer_links) ? $settings->footer_links : []),
            'theme' => $theme,
            'bodyStyle' => $this->bodyStyle($theme, $wallpaperUrl, $cssPosition),
        ];
    }

    /**
     * @param  array<string, string>  $theme
     */
    private function bodyStyle(array $theme, ?string $wallpaperUrl, string $cssPosition): string
    {
        $style = 'background-color: '.$theme['background'].'; color: '.$theme['text'].';';

        if ($wallpaperUrl === null) {
            return $style;
        }

        $url = str_replace(['\\', '"', "'", '(', ')', "\n", "\r"], '', $wallpaperUrl);

        return $style.' background-image: url("'.$url.'"); background-repeat: no-repeat; background-size: cover; background-position: '.$cssPosition.'; background-attachment: fixed;';
    }

    private function assetUrl(?string $path, int $version): ?string
    {
        if ($path === null || preg_match('#^branding/[A-Za-z0-9][A-Za-z0-9._-]*$#', $path) !== 1) {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path).'?v='.$version;
    }

    /**
     * @param  array<int, mixed>  $links
     * @return list<array{label: string, url: string, target: string}>
     */
    private function links(array $links): array
    {
        $safe = [];

        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }

            $label = trim((string) ($link['label'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));
            $target = ($link['target'] ?? '_self') === '_blank' ? '_blank' : '_self';

            if ($label === '' || $url === '' || $this->safePublicUrl($url) === false) {
                continue;
            }

            $safe[] = [
                'label' => $label,
                'url' => $url,
                'target' => $target,
            ];
        }

        return $safe;
    }

    private function safePublicUrl(string $url): bool
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            return false;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_starts_with($url, '/\\')) {
            return true;
        }

        $parts = parse_url($url);

        return is_array($parts) && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true);
    }

    private function hex(?string $value, string $fallback): string
    {
        $value = strtoupper((string) $value);

        return preg_match('/^#[0-9A-F]{6}$/', $value) === 1 ? $value : $fallback;
    }
}
