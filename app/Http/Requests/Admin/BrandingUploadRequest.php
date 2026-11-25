<?php

namespace App\Http\Requests\Admin;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class BrandingUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActiveAdmin() === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $asset = (string) $this->route('asset');

        return match ($asset) {
            'logo' => [
                'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            ],
            'wallpaper' => [
                'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
                'wallpaper_position' => ['nullable', 'in:center,top,cover'],
            ],
            'favicon' => [
                'image' => ['required', 'file', 'max:2048', $this->faviconFile()],
            ],
            default => [
                'image' => ['required', 'file'],
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image.required' => 'Choose an image to upload.',
            'image.image' => 'Upload a PNG, JPG, JPEG, or WebP image.',
            'image.mimes' => 'Upload a PNG, JPG, JPEG, or WebP image.',
            'image.max' => 'The image is larger than the allowed size.',
        ];
    }

    private function faviconFile(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! $value instanceof UploadedFile) {
                $fail('Upload a PNG, JPG, JPEG, or ICO favicon.');

                return;
            }

            $mime = (string) $value->getMimeType();
            $allowed = ['image/png', 'image/jpeg', 'image/x-icon', 'image/vnd.microsoft.icon'];

            if (in_array($mime, $allowed, true)) {
                return;
            }

            $header = file_get_contents($value->getRealPath(), false, null, 0, 4);

            if ($header !== "\x00\x00\x01\x00") {
                $fail('Upload a PNG, JPG, JPEG, or ICO favicon.');
            }
        };
    }
}
