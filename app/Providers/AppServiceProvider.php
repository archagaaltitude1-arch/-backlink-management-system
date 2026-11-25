<?php

namespace App\Providers;

use App\View\Composers\SiteSettingsComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer([
            'layouts.public',
            'layouts.admin',
            'layouts.customer',
            'layouts.seo',
            'components.public-footer',
            'components.public-navbar',
            'components.site-branding-head',
        ], SiteSettingsComposer::class);
    }
}
