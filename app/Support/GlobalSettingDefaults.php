<?php

namespace App\Support;

class GlobalSettingDefaults
{
    /**
     * @return list<string>
     */
    public static function currencies(): array
    {
        return ['USD', 'EUR', 'GBP', 'LKR', 'INR', 'AUD', 'CAD', 'SGD'];
    }

    /**
     * @return list<string>
     */
    public static function dateFormats(): array
    {
        return ['YYYY-MM-DD', 'DD/MM/YYYY', 'MM/DD/YYYY', 'DD-MM-YYYY', 'D MMM YYYY'];
    }

    /**
     * @return list<string>
     */
    public static function languages(): array
    {
        return ['English', 'Sinhala', 'Tamil', 'Spanish', 'French', 'German', 'Hindi'];
    }

    /**
     * @return list<string>
     */
    public static function wallpaperPositions(): array
    {
        return ['center', 'top', 'cover'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function attributes(): array
    {
        return [
            'company_name' => 'Linkora Digital Solutions (Pvt) Ltd',
            'website_name' => 'Linkora',
            'website_url' => 'https://linkora.example',
            'company_email' => 'info@linkora.example',
            'support_email' => 'support@linkora.example',
            'phone_number' => '+94 11 234 5678',
            'company_address' => '45, Kandy Road, Colombo 07, Sri Lanka',
            'city' => 'Colombo',
            'state' => 'Western Province',
            'postal_code' => '00700',
            'country' => 'Sri Lanka',
            'currency' => 'USD',
            'timezone' => 'Asia/Colombo',
            'date_format' => 'YYYY-MM-DD',
            'default_language' => 'English',
            'copyright_text' => '© 2026 Linkora Digital Solutions. All rights reserved.',
            'logo_path' => null,
            'wallpaper_path' => null,
            'wallpaper_source_path' => null,
            'wallpaper_position' => 'center',
            'favicon_path' => null,
            'footer_copyright' => '© 2026 Linkora Digital Solutions. All rights reserved.',
            'footer_description' => 'Backlinks on real publisher websites, placed and verified by our SEO team.',
            'footer_html' => '',
            'footer_links' => [
                ['label' => 'Terms and Conditions', 'url' => '/terms-and-conditions', 'target' => '_self'],
                ['label' => 'Privacy Policy', 'url' => '/privacy-policy', 'target' => '_self'],
                ['label' => 'Acceptable Use Policy', 'url' => '/acceptable-use-policy', 'target' => '_self'],
                ['label' => 'Contact Us', 'url' => '/contact', 'target' => '_self'],
            ],
            'primary_colour' => '#152029',
            'secondary_colour' => '#D63A10',
            'accent_colour' => '#FF4917',
            'background_colour' => '#FFFFFF',
            'text_colour' => '#152029',
            'button_colour' => '#FF4917',
        ];
    }
}
