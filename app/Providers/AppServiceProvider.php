<?php

namespace App\Providers;

use App\Auth\ConfigAdminUserProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        Auth::provider('config-admins', function ($app, array $config): ConfigAdminUserProvider {
            return new ConfigAdminUserProvider(config('admin.admins', []));
        });
    }
}
