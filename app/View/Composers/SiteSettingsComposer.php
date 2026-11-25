<?php

namespace App\View\Composers;

use App\Support\SiteSettings;
use Illuminate\View\View;

class SiteSettingsComposer
{
    public function __construct(private SiteSettings $settings) {}

    public function compose(View $view): void
    {
        $view->with('siteSettings', $this->settings->publicPayload());
    }
}
