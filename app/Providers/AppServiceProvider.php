<?php

namespace App\Providers;

use App\Services\GmailTransport;
use Illuminate\Support\Facades\Mail;
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
        Mail::extend('gmail', static fn (array $config): GmailTransport => new GmailTransport(
            $config['client_id'],
            $config['client_secret'],
            $config['refresh_token'],
        ));
    }
}
