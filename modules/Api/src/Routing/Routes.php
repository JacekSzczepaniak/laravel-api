<?php

namespace Modules\Api\Routing;

use Illuminate\Support\Facades\Route;
use Modules\Api\Http\Controllers\HealthController;
use Modules\Api\Http\Controllers\V1\AuthController;
use Modules\Api\Http\Controllers\V1\PeopleContactsController;
use Modules\Api\Http\Controllers\V1\PeopleController;

final class Routes
{
    public static function register(): void
    {
        Route::prefix('api')
            ->middleware(['api', 'throttle:api'])
            ->group(function () {
                Route::get('/health', HealthController::class);

                Route::prefix('v1')->group(function () {
                    Route::prefix('auth')->group(function () {
                        Route::post('login',    [AuthController::class, 'login'])->middleware('throttle:10,1');
                        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');

                        Route::middleware('auth:sanctum')->group(function () {
                            Route::get('me',      [AuthController::class, 'me']);
                            Route::post('logout', [AuthController::class, 'logout']);
                        });
                    });
                    Route::apiResource('people', PeopleController::class)
                        ->only(['index','store','show','update','destroy']);

                    Route::apiResource('people.contacts', PeopleContactsController::class)
                        ->only(['index','store','update','destroy']);
                    Route::middleware('auth:sanctum')->group(function () {
                        Route::post('people/{person}/emails/send-welcome', [PeopleContactsController::class, 'sendWelcome']);
                    });
                });
            });
    }
}
