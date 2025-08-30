<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Modules\Api\Http\Middleware\ForceJson;
use Modules\Api\Support\Exceptions\ApiException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', [
            ForceJson::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ],
                ], 422);
            }
        });
        // API: brak autoryzacji
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => ['message' => 'Unauthenticated'],
                ], 401);
            }
        });

        // API: własne wyjątki (opcjonalnie)
        $exceptions->render(function (ApiException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => $e->getMessage(),
                        'errors' => $e->errors ?? [],
                    ],
                ], $e->status ?? 400);
            }
        });
    })->create();
