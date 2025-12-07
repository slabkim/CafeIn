<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
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
        // Force generated URLs to use HTTPS in production (behind proxies)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Ensure storage symlink exists for serving uploaded files
        if (! app()->runningInConsole()) {
            $publicStorage = public_path('storage');
            if (! is_link($publicStorage) && ! is_dir($publicStorage)) {
                try {
                    Artisan::call('storage:link');
                } catch (\Throwable $e) {
                    // swallow; will be handled by manual setup instructions
                }
            }
        }
    }
}
