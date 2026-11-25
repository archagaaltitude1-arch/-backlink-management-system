<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_name',
    'website_name',
    'website_url',
    'company_email',
    'support_email',
    'phone_number',
    'company_address',
    'city',
    'state',
    'postal_code',
    'country',
    'currency',
    'timezone',
    'date_format',
    'default_language',
    'copyright_text',
    'logo_path',
    'wallpaper_path',
    'wallpaper_source_path',
    'wallpaper_position',
    'favicon_path',
    'footer_copyright',
    'footer_description',
    'footer_html',
    'footer_links',
    'primary_colour',
    'secondary_colour',
    'accent_colour',
    'background_colour',
    'text_colour',
    'button_colour',
])]
class GlobalSetting extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'footer_links' => 'array',
        ];
    }
}
