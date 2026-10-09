<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\CoordinatedRouter;
use App\Models\CustomPage;
use App\Models\Faq;
use App\Models\HardwareCategory;
use App\Models\HardwareItem;
use App\Models\Suggestion;
use App\Models\User;
use App\Models\WebhookDestination;
use App\Policies\CoordinatedRouterPolicy;
use App\Policies\CustomPagePolicy;
use App\Policies\FaqPolicy;
use App\Policies\HardwareCategoryPolicy;
use App\Policies\HardwareItemPolicy;
use App\Policies\SuggestionPolicy;
use App\Policies\UserPolicy;
use App\Policies\WebhookDestinationPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleRequests\RequireLivewireHeaders;

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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Faq::class, FaqPolicy::class);
        Gate::policy(Suggestion::class, SuggestionPolicy::class);
        Gate::policy(CoordinatedRouter::class, CoordinatedRouterPolicy::class);
        Gate::policy(CustomPage::class, CustomPagePolicy::class);
        Gate::policy(HardwareCategory::class, HardwareCategoryPolicy::class);
        Gate::policy(HardwareItem::class, HardwareItemPolicy::class);
        Gate::policy(WebhookDestination::class, WebhookDestinationPolicy::class);

        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
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

        // Asegurar que las peticiones AJAX de Livewire (utilizadas en Filament /admin) dispongan
        // de middleware de sesión, cookies y CSRF, dado que el grupo 'web' público tiene las cookies deshabilitadas.
        Livewire::setUpdateRoute(function ($handle, $path) {
            return Route::post($path, $handle)
                ->middleware([
                    EncryptCookies::class,
                    AddQueuedCookiesToResponse::class,
                    StartSession::class,
                    ShareErrorsFromSession::class,
                    ValidateCsrfToken::class,
                    RequireLivewireHeaders::class,
                ])
                ->name('default-livewire.update');
        });
    }
}
