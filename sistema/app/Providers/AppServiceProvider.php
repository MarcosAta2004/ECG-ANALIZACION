<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Forzar HTTPS cuando se accede a través de un proxy (ngrok, etc.)
        if (
            str_starts_with(config('app.url'), 'https://') ||
            request()->header('X-Forwarded-Proto') === 'https'
        ) {
            URL::forceScheme('https');
        }
    }
}
