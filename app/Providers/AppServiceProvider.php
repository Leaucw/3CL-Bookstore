<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

use App\Models\Review;
use App\Observers\ReviewObserver;

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
        // Register the Review observer
        Review::observe(ReviewObserver::class);

        // Named limiter used by 'throttle:api'
        RateLimiter::for('api', function (Request $request) {
            // 60 requests / minute per user id (or IP if guest)
            return [
                Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()),
            ];
        });

        // Load API routes
        \Illuminate\Support\Facades\Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));

        // Load web routes
        \Illuminate\Support\Facades\Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}