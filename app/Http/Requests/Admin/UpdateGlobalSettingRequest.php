<?php

namespace App\Http\Requests\Admin;

use App\Support\GlobalSettingDefaults;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGlobalSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActiveAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $links = $this->input('footer_links', []);

        if (is_string($links)) {
            $decoded = json_decode($links, true);
            $links = is_array($decoded) ? $decoded : [];
        }

        $this->merge([
            'currency' => strtoupper(trim((string) $this->input('currency'))),
            'footer_links' => array_values(is_array($links) ? $links : []),
            'footer_html' => (string) $this->input('footer_html', ''),
            'footer_description' => (string) $this->input('footer_description', ''),
            'wallpaper_position' => (string) $this->input('wallpaper_position', 'center'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'website_name' => ['required', 'string', 'max:255'],
            'website_url' => ['required', 'url', 'max:255'],
            'company_email' => ['required', 'email', 'max:255'],
            'support_email' => ['required', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:40'],
            'company_address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:120'],
            'currency' => ['required', 'string', Rule::in(GlobalSettingDefaults::currencies())],
            'timezone' => ['required', 'timezone'],
            'date_format' => ['required', 'string', Rule::in(GlobalSettingDefaults::dateFormats())],
            'default_language' => ['required', 'string', Rule::in(GlobalSettingDefaults::languages())],
            'copyright_text' => ['required', 'string', 'max:500'],
            'wallpaper_position' => ['required', Rule::in(GlobalSettingDefaults::wallpaperPositions())],
            'footer_copyright' => ['required', 'string', 'max:500'],
            'footer_description' => ['nullable', 'string', 'max:1000'],
            'footer_html' => ['nullable', 'string', 'max:20000'],
            'footer_links' => ['array', 'max:12'],
            'footer_links.*.label' => ['required', 'string', 'max:80'],
            'footer_links.*.url' => ['required', 'string', 'max:255', $this->publicUrl()],
            'footer_links.*.target' => ['required', Rule::in(['_self', '_blank'])],
            'primary_colour' => $this->colour(),
            'secondary_colour' => $this->colour(),
            'accent_colour' => $this->colour(),
            'background_colour' => $this->colour(),
            'text_colour' => $this->colour(),
            'button_colour' => $this->colour(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'primary_colour.regex' => 'Enter a colour as a hex value such as #172B4D.',
            'secondary_colour.regex' => 'Enter a colour as a hex value such as #F97316.',
            'accent_colour.regex' => 'Enter a colour as a hex value such as #F97316.',
            'background_colour.regex' => 'Enter a colour as a hex value such as #FFFFFF.',
            'text_colour.regex' => 'Enter a colour as a hex value such as #111827.',
            'button_colour.regex' => 'Enter a colour as a hex value such as #F97316.',
        ];
    }

    /**
     * @return list<string>
     */
    private function colour(): array
    {
        return ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'];
    }

    private function publicUrl(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value) || $this->allowsUrl($value) === false) {
                $fail('Enter a site path starting with / or an http(s) URL.');
            }
        };
    }

    private function allowsUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            return false;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_starts_with($url, '/\\')) {
            return true;
        }

        $parts = parse_url($url);

        return is_array($parts) && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true);
    }
}
