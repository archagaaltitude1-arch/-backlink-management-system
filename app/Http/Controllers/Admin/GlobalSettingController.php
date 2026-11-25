<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidBrandingImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandingUploadRequest;
use App\Http\Requests\Admin\UpdateGlobalSettingRequest;
use App\Models\GlobalSetting;
use App\Services\BrandingImageProcessor;
use App\Support\GlobalSettingDefaults;
use App\Support\HtmlSanitizer;
use App\Support\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class GlobalSettingController extends Controller
{
    public function edit(SiteSettings $settings): View
    {
        $record = $settings->record();

        return view('admin.global-settings.edit', [
            'settings' => $record,
            'preview' => $settings->present($record),
            'timezones' => timezone_identifiers_list(),
            'currencies' => GlobalSettingDefaults::currencies(),
            'dateFormats' => GlobalSettingDefaults::dateFormats(),
            'languages' => GlobalSettingDefaults::languages(),
        ]);
    }

    public function update(
        UpdateGlobalSettingRequest $request,
        SiteSettings $settings,
        BrandingImageProcessor $images,
        HtmlSanitizer $sanitizer,
    ): JsonResponse|RedirectResponse {
        $record = $settings->record();
        $data = $request->safe()->except(['footer_html']);
        $data['footer_html'] = $sanitizer->sanitize($request->string('footer_html')->toString());
        $data['footer_links'] = array_values($request->validated('footer_links') ?? []);

        foreach (['primary_colour', 'secondary_colour', 'accent_colour', 'background_colour', 'text_colour', 'button_colour'] as $colour) {
            $data[$colour] = strtoupper((string) $data[$colour]);
        }

        $previousWallpaper = $record->wallpaper_path;
        $replacement = null;

        if ($data['wallpaper_position'] !== $record->wallpaper_position && filled($record->wallpaper_source_path)) {
            $cropped = $images->recropWallpaper((string) $record->wallpaper_source_path, (string) $data['wallpaper_position']);
            $data['wallpaper_path'] = $cropped['path'];
            $replacement = $previousWallpaper;
        }

        $record->fill($data);
        $record->save();

        if (is_string($replacement) && $replacement !== $record->wallpaper_path) {
            $images->delete($replacement);
        }

        $settings->forget();
        $record->refresh();
        $payload = $settings->present($record);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Global settings saved.',
                'settings' => $payload,
            ]);
        }

        return redirect()->route('admin.global-settings')->with('success', 'Global settings saved.');
    }

    public function storeBranding(
        BrandingUploadRequest $request,
        string $asset,
        SiteSettings $settings,
        BrandingImageProcessor $images,
    ): JsonResponse {
        $record = $settings->record();

        try {
            $result = match ($asset) {
                'logo' => $this->storeLogo($record, $request, $images),
                'wallpaper' => $this->storeWallpaper($record, $request, $images),
                'favicon' => $this->storeFavicon($record, $request, $images),
                default => abort(404),
            };
        } catch (InvalidBrandingImage $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $settings->forget();
        $record->refresh();
        $payload = $settings->present($record);

        return response()->json([
            'message' => $result['message'],
            'url' => $payload[$result['urlKey']],
            'width' => $result['width'],
            'height' => $result['height'],
        ]);
    }

    public function destroyBranding(
        Request $request,
        string $asset,
        SiteSettings $settings,
        BrandingImageProcessor $images,
    ): JsonResponse {
        abort_unless(in_array($asset, ['logo', 'wallpaper', 'favicon'], true), 404);
        abort_unless($request->user()?->isActiveAdmin() === true, 403);

        $record = $settings->record();

        if ($asset === 'logo') {
            $images->delete($record->logo_path);
            $record->logo_path = null;
        }

        if ($asset === 'wallpaper') {
            $images->delete($record->wallpaper_path);
            $images->delete($record->wallpaper_source_path);
            $record->wallpaper_path = null;
            $record->wallpaper_source_path = null;
        }

        if ($asset === 'favicon') {
            $images->delete($record->favicon_path);
            $record->favicon_path = null;
        }

        $record->save();
        $settings->forget();

        return response()->json(['message' => 'Image removed.']);
    }

    /**
     * @return array{message: string, urlKey: string, width: int, height: int}
     */
    private function storeLogo(GlobalSetting $record, BrandingUploadRequest $request, BrandingImageProcessor $images): array
    {
        $stored = $images->storeLogo($request->file('image'));
        $previous = $record->logo_path;

        try {
            $record->logo_path = $stored['path'];
            $record->save();
        } catch (Throwable $exception) {
            $images->delete($stored['path']);

            throw $exception;
        }

        if ($previous !== $stored['path']) {
            $images->delete($previous);
        }

        return [
            'message' => 'Logo uploaded.',
            'urlKey' => 'logoUrl',
            'width' => $stored['width'],
            'height' => $stored['height'],
        ];
    }

    /**
     * @return array{message: string, urlKey: string, width: int, height: int}
     */
    private function storeWallpaper(GlobalSetting $record, BrandingUploadRequest $request, BrandingImageProcessor $images): array
    {
        $position = (string) $request->input('wallpaper_position', $record->wallpaper_position);

        if (! in_array($position, ['center', 'top', 'cover'], true)) {
            $position = (string) $record->wallpaper_position;
        }

        $stored = $images->storeWallpaper($request->file('image'), $position);
        $previousPath = $record->wallpaper_path;
        $previousSource = $record->wallpaper_source_path;

        try {
            $record->wallpaper_path = $stored['path'];
            $record->wallpaper_source_path = $stored['source_path'];
            $record->wallpaper_position = $position;
            $record->save();
        } catch (Throwable $exception) {
            $images->delete($stored['path']);
            $images->delete($stored['source_path']);

            throw $exception;
        }

        if ($previousPath !== $stored['path']) {
            $images->delete($previousPath);
        }

        if ($previousSource !== $stored['source_path']) {
            $images->delete($previousSource);
        }

        return [
            'message' => 'Wallpaper uploaded.',
            'urlKey' => 'wallpaperUrl',
            'width' => $stored['width'],
            'height' => $stored['height'],
        ];
    }

    /**
     * @return array{message: string, urlKey: string, width: int, height: int}
     */
    private function storeFavicon(GlobalSetting $record, BrandingUploadRequest $request, BrandingImageProcessor $images): array
    {
        $previous = $record->favicon_path;
        $stored = $images->storeFavicon($request->file('image'));

        try {
            $record->favicon_path = $stored['path'];
            $record->save();
        } catch (Throwable $exception) {
            if ($previous !== $stored['path']) {
                $images->delete($stored['path']);
            }

            throw $exception;
        }

        return [
            'message' => 'Favicon uploaded.',
            'urlKey' => 'faviconUrl',
            'width' => $stored['width'],
            'height' => $stored['height'],
        ];
    }
}
