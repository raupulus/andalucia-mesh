<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

    public function boot(): void
    {
        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Rate limiting para la API pública (60 req/min por IP)
        RateLimiter::for('api-publica', function (Request $request) {
            $exemptIps = array_filter(array_map('trim', explode(',', (string) env('API_RATE_LIMIT_EXEMPT', '127.0.0.1,172.30.0.1'))));
            if (in_array($request->ip(), $exemptIps, true)) {
                return Limit::none();
            }
            $limitPerMin = (int) env('API_RATE_LIMIT_PER_MINUTE', 60);
            return Limit::perMinute($limitPerMin)->by(sha1($request->ip() ?? 'unknown'));
        });
    }
}
