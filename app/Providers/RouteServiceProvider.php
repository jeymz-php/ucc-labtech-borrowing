<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/administration.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        /*
        |--------------------------------------------------------------------------
        | Public Guest Borrowing Rate Limits
        |--------------------------------------------------------------------------
        |
        | Keep ordinary page requests, live polling requests, and form submissions
        | in separate buckets. This prevents the automatic inventory/status polling
        | from consuming the allowance used to open the Guest Borrower page.
        |
        */
        RateLimiter::for('guest-pages', function (Request $request) {
            return Limit::perMinute(120)
                ->by('guest-pages|'.$request->ip());
        });

        RateLimiter::for('guest-live', function (Request $request) {
            return Limit::perMinute(180)
                ->by('guest-live|'.$request->ip());
        });

        RateLimiter::for('guest-submit', function (Request $request) {
            return Limit::perMinute(10)
                ->by('guest-submit|'.$request->ip());
        });

        RateLimiter::for('staff-registration', function (Request $request) {
            return Limit::perMinute(10)
                ->by('staff-registration|'.$request->ip());
        });
    }
}
