<?php

namespace Modules\Api;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Modules\Api\Console\DispatchOutbox;
use Modules\Api\Routing\Routes;
use Modules\Api\Support\ApiResponse;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/api.php', 'modules.api');

        $this->commands([DispatchOutbox::class]);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        ApiResponse::boot();
        Routes::register();
        $this->publishes([
            __DIR__.'/../config/api.php' => config_path('modules/api.php'),
        ], 'modules-api-config');

        RateLimiter::for('api', function (Request $request) {
            $perMinute = app()->environment('testing') ? 600 : 120;

            return [
                Limit::perMinute($perMinute)->by($request->user()?->id ?: $request->ip()),
            ];
        });
    }
}
