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
        if (config('app.env') === 'production' && ! app()->runningInConsole()) {
            $localHosts = ['127.0.0.1', 'localhost'];
            $host = request()->getHost();
            // Allow HTTP when accessing from local hosts to avoid SSL handshake errors
            if (! in_array($host, $localHosts, true)) {
                URL::forceScheme('https');
            }
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
