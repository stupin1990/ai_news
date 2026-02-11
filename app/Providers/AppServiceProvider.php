<?php

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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('registration', function (Request $request) {
            return Limit::perMinutes(5, 1)->by($request->ip())->response(function () {
                return back()->withErrors([
                    'email' => 'Too many registration attempts. Please try again in 5 minutes.',
                ]);
            });
        });
    }
}
